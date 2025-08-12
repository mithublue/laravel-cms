<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
  pages: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const selected = ref([]);
const allSelected = computed(() => props.pages?.data?.length && selected.value.length === props.pages.data.length);

function toggleSelectAll(e) {
  if (e.target.checked) {
    selected.value = props.pages.data.map((p) => p.id);
  } else {
    selected.value = [];
  }
}

function bulkTrash() {
  if (!selected.value.length) return;
  if (!confirm(`Move ${selected.value.length} selected page(s) to Trash?`)) return;
  router.post(route('admin.pages.bulk-destroy'), { ids: selected.value }, { preserveScroll: true, preserveState: true });
}

watch(search, (value) => {
  router.get(route('admin.pages.index'), { search: value }, { preserveState: true, replace: true });
});
</script>

<template>
  <Head title="Pages" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Pages</h2>
        <Link :href="route('admin.pages.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Page</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg">
          <div class="mb-4">
            <input v-model="search" type="text" placeholder="Search pages..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
          </div>

          <div class="mb-3 flex items-center gap-2">
            <button @click="bulkTrash" :disabled="!selected.length" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
              :class="selected.length ? 'bg-red-600 text-white hover:bg-red-500' : 'bg-gray-200 text-gray-500 cursor-not-allowed'">
              Move to Trash
            </button>
            <span class="text-sm text-gray-500" v-if="selected.length">{{ selected.length }} selected</span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="w-10 px-3 py-2">
                    <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" aria-label="Select all" />
                  </th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Slug</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Visibility</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Published</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="page in pages.data" :key="page.id">
                  <td class="w-10 px-3 py-2">
                    <input type="checkbox" :value="page.id" v-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" aria-label="Select row" />
                  </td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ page.id }}</td>
                  <td class="px-3 py-2 text-sm text-gray-900">{{ page.title || '(no title)' }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ page.slug }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ page.status }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ page.visibility }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ page.published_at || '-' }}</td>
                  <td class="px-3 py-2 text-right text-sm">
                    <Link :href="route('admin.pages.edit', page.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <Link v-for="link in pages.links" :key="link.url + link.label" :href="link.url || '#'" preserve-scroll :class="[
              'px-3 py-1 rounded border text-sm',
              link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]" v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
