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
          tax.terms = (res?.data?.data || []).map(item => ({ value: item.id, label: item.name || `#${item.id}` }));
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
    const res = await window.axios.post(route('admin.terms.store'), {
      scope: props.scope,
      taxonomy: tax.slug,
      name,
    });
    const item = res?.data?.data;
    if (item?.id) {
      const opt = { value: item.id, label: item.name };
      tax.terms.push(opt);
      tax.selected = Array.from(new Set([...(tax.selected || []), item.id]));
      newTerm.value[tax.slug] = '';
      emitCombined();
    }
  } catch (e) {
    // Surface a simple error; detailed errors can be added later
    alert('Failed to create term.');
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
      </div>
    </div>
  </div>
</template>
