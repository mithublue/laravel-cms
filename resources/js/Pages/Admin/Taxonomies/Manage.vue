<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  scope: { type: String, default: null }, // 'post' | 'news' | 'product' | null
  taxonomies: { type: Array, default: () => [] },
});

const scopes = [
  { value: 'post', label: 'Posts' },
  { value: 'news', label: 'News' },
  { value: 'product', label: 'Products' },
];

const form = useForm({
  name: '',
  slug: '',
  scope: props.scope || 'post',
  hierarchical: false,
  multiple: true,
});

const scopeLabel = computed(() => scopes.find(s => s.value === (props.scope || form.scope))?.label || 'All');

function submit() {
  form.post(route('admin.taxonomies.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Refresh the page to see the new taxonomy in list
      const q = props.scope ? `?scope=${encodeURIComponent(props.scope)}` : `?scope=${encodeURIComponent(form.scope)}`;
      window.location.href = route('admin.taxonomies.manage') + q;
    },
  });
}
</script>

<template>
  <Head :title="`Taxonomies - ${scopeLabel}`" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Taxonomies <span v-if="props.scope" class="text-gray-500 font-normal">— {{ scopeLabel }}</span></h2>
    </template>

    <div class="max-w-6xl mx-auto space-y-6">
      <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-base font-medium text-gray-900 mb-4">Create Taxonomy</h3>
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Slug (optional)</label>
            <input v-model="form.slug" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</div>
          </div>

          <div v-if="!props.scope">
            <label class="block text-sm font-medium text-gray-700">Scope</label>
            <select v-model="form.scope" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option v-for="s in scopes" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <div v-if="form.errors.scope" class="mt-1 text-sm text-red-600">{{ form.errors.scope }}</div>
          </div>
          <div v-else>
            <label class="block text-sm font-medium text-gray-700">Scope</label>
            <input type="text" :value="scopeLabel" class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-gray-600" disabled />
          </div>

          <div class="flex items-center gap-2">
            <input id="hierarchical" v-model="form.hierarchical" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            <label for="hierarchical" class="text-sm text-gray-700">Hierarchical (e.g., categories)</label>
          </div>
          <div class="flex items-center gap-2">
            <input id="multiple" v-model="form.multiple" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            <label for="multiple" class="text-sm text-gray-700">Allow multiple selection</label>
          </div>

          <div class="md:col-span-2">
            <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
              {{ form.processing ? 'Saving…' : 'Create Taxonomy' }}
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-base font-medium text-gray-900 mb-4">Existing Taxonomies <span v-if="props.scope" class="text-gray-500 font-normal">— {{ scopeLabel }}</span></h3>
        <div v-if="!taxonomies.length" class="text-sm text-gray-500">No taxonomies yet.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Name</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Slug</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Scope</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Hierarchical</th>
                <th class="px-4 py-2 text-left font-medium text-gray-600">Multiple</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="t in taxonomies" :key="t.id">
                <td class="px-4 py-2">{{ t.name }}</td>
                <td class="px-4 py-2">{{ t.slug }}</td>
                <td class="px-4 py-2 uppercase">{{ t.scope }}</td>
                <td class="px-4 py-2">{{ t.hierarchical ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-2">{{ t.multiple ? 'Yes' : 'No' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
