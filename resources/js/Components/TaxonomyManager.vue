<script setup>
import { ref, onMounted } from 'vue';
import TomMultiSelect from '@/Components/TomMultiSelect.vue';
import { route } from 'ziggy-js';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  scope: { type: String, required: true }, // 'post' | 'news' | 'product'
});

const emit = defineEmits(['update:modelValue']);

const loading = ref(true);
const error = ref('');
const taxonomies = ref([]); // [{ id, name, slug, scope, hierarchical, multiple, terms: [{value,label}], selected: [] }]
const newTerm = ref({}); // map of taxonomy slug -> new term name
const newParent = ref({}); // map of taxonomy slug -> parent_id
const termError = ref({}); // map of taxonomy slug -> last error string

async function fetchTaxonomies() {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await window.axios.get(route('admin.taxonomies.index'), { params: { scope: props.scope } });
    taxonomies.value = (data?.data || []).map(t => ({ ...t, terms: [], selected: [] }));

    // Fetch terms for each taxonomy in parallel
    await Promise.all(
      taxonomies.value.map(async (tax) => {
        try {
          const res = await window.axios.get(route('admin.terms.index'), {
            params: { scope: props.scope, taxonomy: tax.slug },
          });
          tax.terms = (res?.data?.data || []).map(item => ({ value: item.id, label: item.name || `#${item.id}`, parent_id: item.parent_id }));
        } catch (e) {
          // isolate errors per taxonomy
        }
      })
    );

    // Initialize selected per taxonomy from incoming modelValue
    const selectedSet = new Set((props.modelValue || []).map(Number));
    for (const tax of taxonomies.value) {
      tax.selected = tax.terms.filter(opt => selectedSet.has(Number(opt.value))).map(opt => opt.value);
    }

    emitCombined();
  } catch (e) {
    error.value = 'Failed to load taxonomies/terms.';
  } finally {
    loading.value = false;
  }
}

function emitCombined() {
  // Flatten unique selection across all taxonomies
  const ids = [...new Set(taxonomies.value.flatMap(t => t.selected))].map(Number);
  emit('update:modelValue', ids);
}

function onSelectChange() {
  emitCombined();
}

async function createTerm(tax) {
  const name = (newTerm.value?.[tax.slug] || '').trim();
  if (!name) return;
  try {
    termError.value[tax.slug] = '';
    const payload = {
      scope: props.scope,
      taxonomy: tax.slug,
      name,
    };
    // If hierarchical and parent selected, add parent_id
    if (tax.hierarchical && newParent.value[tax.slug]) {
      payload.parent_id = newParent.value[tax.slug];
    }
    const res = await window.axios.post(route('admin.terms.store'), payload);
    const item = res?.data?.data;
    if (item?.id) {
      const opt = { value: item.id, label: item.name, parent_id: item.parent_id };
      tax.terms.push(opt);
      tax.selected = Array.from(new Set([...(tax.selected || []), item.id]));
      newTerm.value[tax.slug] = '';
      newParent.value[tax.slug] = '';
      termError.value[tax.slug] = '';
      emitCombined();
    }
  } catch (e) {
    let message = e?.response?.data?.message;
    const errors = e?.response?.data?.errors;
    if (!message && errors && typeof errors === 'object') {
      const firstKey = Object.keys(errors)[0];
      if (firstKey && errors[firstKey]?.length) {
        message = errors[firstKey][0];
      }
    }
    termError.value[tax.slug] = message || 'Failed to create term.';
    alert(termError.value[tax.slug]);
  }
}

onMounted(fetchTaxonomies);
</script>

<template>
  <div class="space-y-5">
    <div v-if="loading" class="text-sm text-gray-500">Loading taxonomies…</div>
    <div v-else-if="error" class="text-sm text-red-600">{{ error }}</div>

    <div v-else>
      <div v-if="!taxonomies.length" class="text-sm text-gray-500">No taxonomies available.</div>

      <div v-for="tax in taxonomies" :key="tax.id" class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-sm font-medium text-gray-700">{{ tax.name }}</label>
        </div>
        <template v-if="tax.hierarchical">
          <!-- Render terms as a tree -->
          <div class="ml-2">
            <template v-for="parent in tax.terms.filter(t => !t.parent_id)" :key="parent.value">
              <div>
                <label>
                  <input type="checkbox" :value="parent.value" v-model="tax.selected" @change="onSelectChange" />
                  {{ parent.label }}
                </label>
                <div class="ml-5" v-if="tax.terms.some(t => t.parent_id === parent.value)">
                  <div v-for="child in tax.terms.filter(t => t.parent_id === parent.value)" :key="child.value">
                    <label>
                      <input type="checkbox" :value="child.value" v-model="tax.selected" @change="onSelectChange" />
                      {{ child.label }}
                    </label>
                  </div>
                </div>
              </div>
            </template>
          </div>
          <!-- Add new term UI for hierarchical taxonomy -->
          <div class="mt-2 space-y-2">
            <input
              v-model="newTerm[tax.slug]"
              type="text"
              :placeholder="`Add new ${tax.name}…`"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <div class="flex items-center gap-2">
              <select v-model="newParent[tax.slug]" class="rounded-md border-gray-300">
                <option value="">— Parent Category —</option>
                <option v-for="opt in tax.terms" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
              <button type="button" @click="createTerm(tax)" class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 border">
                Add
              </button>
            </div>
          </div>
          <div v-if="termError[tax.slug]" class="text-sm text-red-600">{{ termError[tax.slug] }}</div>
        </template>
        <template v-else>
          <!-- Non-hierarchical: keep multi-select UI -->
          <TomMultiSelect
            v-model="tax.selected"
            :options="tax.terms"
            :placeholder="`Select ${tax.name}`"
            class="mt-1 block w-full"
            @change="() => onSelectChange()"
          />
          <div class="flex items-center gap-2">
            <input
              v-model="newTerm[tax.slug]"
              type="text"
              :placeholder="`Add new ${tax.name}…`"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <button type="button" @click="createTerm(tax)" class="mt-1 inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 border">
              Add
            </button>
          </div>
          <div v-if="termError[tax.slug]" class="text-sm text-red-600">{{ termError[tax.slug] }}</div>
        </template>
      </div>
    </div>
  </div>
</template>
