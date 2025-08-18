<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';

const props = defineProps({
  scope: { type: String, default: null },
  taxonomies: { type: Array, default: () => [] },
});

const page = usePage();

const selectedTaxonomySlug = ref('');
const terms = ref([]);
const loading = ref(false);
const search = ref('');
const error = ref('');

const newTermName = ref('');
const newParentId = ref(null);

const selectedTaxonomy = computed(() => props.taxonomies.find(t => t.slug === selectedTaxonomySlug.value));

watch(selectedTaxonomySlug, () => {
  terms.value = [];
  newTermName.value = '';
  newParentId.value = null;
  if (selectedTaxonomySlug.value) {
    loadTerms();
  }
});

async function loadTerms() {
  if (!selectedTaxonomySlug.value) return;
  loading.value = true;
  error.value = '';
  try {
    const { data } = await window.axios.get(route('admin.terms.index'), {
      params: {
        scope: props.scope,
        taxonomy: selectedTaxonomySlug.value,
        search: search.value || undefined,
      },
    });
    terms.value = data.data || [];
  } catch (e) {
    error.value = e?.response?.data?.message || 'Failed to load terms';
  } finally {
    loading.value = false;
  }
}

async function createTerm() {
  if (!newTermName.value.trim() || !selectedTaxonomySlug.value) return;
  error.value = '';
  try {
    const payload = {
      scope: props.scope,
      taxonomy: selectedTaxonomySlug.value,
      name: newTermName.value.trim(),
    };
    if (selectedTaxonomy.value?.hierarchical && newParentId.value) {
      payload.parent_id = newParentId.value;
    }
    const res = await window.axios.post(route('admin.terms.store'), payload);
    const t = res?.data?.data;
    if (t) {
      terms.value.push(t);
      newTermName.value = '';
      newParentId.value = null;
    }
  } catch (e) {
    // surface backend validation errors
    const rr = e?.response;
    if (rr?.data?.errors) {
      const msgs = Object.values(rr.data.errors).flat();
      error.value = msgs.join('\n');
    } else {
      error.value = rr?.data?.message || 'Failed to create term';
    }
  }
}

async function deleteTerm(term) {
  if (!confirm(`Delete term "${term.name}"? This will unsync it from items.`)) return;
  try {
    await window.axios.delete(route('admin.terms.destroy', term.id), {
      params: { scope: props.scope },
    });
    terms.value = terms.value.filter(t => t.id !== term.id);
  } catch (e) {
    alert(e?.response?.data?.message || 'Failed to delete term');
  }
}

const pageTitle = computed(() => {
  const scopeLabel = props.scope ? props.scope.charAt(0).toUpperCase() + props.scope.slice(1) : 'Content';
  return `${scopeLabel} Terms`;
});
</script>

<template>
  <Head :title="pageTitle" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Manage Terms
        <span v-if="props.scope" class="text-gray-500 font-normal">— {{ props.scope.charAt(0).toUpperCase() + props.scope.slice(1) }}</span>
      </h2>
    </template>

    <div class="max-w-6xl mx-auto space-y-6">
      <div class="grid gap-6 md:grid-cols-3">
        <!-- Left: Taxonomy selector + Create -->
        <div class="md:col-span-1">
          <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Select Taxonomy</label>
              <select v-model="selectedTaxonomySlug" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Choose --</option>
                <option v-for="tx in taxonomies" :key="tx.id" :value="tx.slug">
                  {{ tx.name }} ({{ tx.scope }})
                </option>
              </select>
            </div>

            <div v-if="selectedTaxonomy" class="text-xs text-gray-500">
              Scope: <strong>{{ selectedTaxonomy.scope }}</strong> •
              Hierarchical: {{ selectedTaxonomy.hierarchical ? 'Yes' : 'No' }}
            </div>

            <div v-if="selectedTaxonomy" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Add Term</label>
              <input v-model="newTermName" type="text" placeholder="Term name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              <div v-if="selectedTaxonomy.hierarchical">
                <label class="block text-xs text-gray-600 mb-1">Parent (optional)</label>
                <select v-model="newParentId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option :value="null">— None —</option>
                  <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
              </div>
              <button @click="createTerm" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Create</button>
            </div>
          </div>
        </div>

        <!-- Right: Terms list -->
        <div class="md:col-span-2">
          <div class="bg-white shadow sm:rounded-lg p-6">
            <div class="flex items-center gap-2 mb-4">
              <input v-model="search" @keyup.enter="loadTerms" type="text" placeholder="Search terms..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              <button @click="loadTerms" :disabled="!selectedTaxonomySlug" class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 border border-gray-300 disabled:opacity-50">Search</button>
            </div>

            <div v-if="error" class="mb-3 p-3 border border-red-200 bg-red-50 text-red-700 text-sm rounded">{{ error }}</div>

            <div v-if="!selectedTaxonomySlug" class="text-sm text-gray-500">Choose a taxonomy to view its terms.</div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">ID</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Slug</th>
                    <th class="px-4 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr v-if="loading">
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Loading...</td>
                  </tr>
                  <tr v-for="t in terms" :key="t.id">
                    <td class="px-4 py-2 text-gray-500">#{{ t.id }}</td>
                    <td class="px-4 py-2">{{ t.name }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ t.slug }}</td>
                    <td class="px-4 py-2 text-right">
                      <button @click="deleteTerm(t)" class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 border border-red-200 hover:bg-red-100">Delete</button>
                    </td>
                  </tr>
                  <tr v-if="!loading && terms.length === 0">
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No terms</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
