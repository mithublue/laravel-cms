<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
  posts: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const selected = ref([]);
const allSelected = computed(() => props.posts?.data?.length && selected.value.length === props.posts.data.length);

watch(search, (value) => {
  router.get(route('admin.posts.trash'), { search: value }, { preserveState: true, replace: true });
});

function restoreItem(id) {
  if (!confirm('Restore this post?')) return;
  router.post(route('admin.posts.restore', id), {}, { preserveScroll: true });
}

function forceDeleteItem(id) {
  if (!confirm('Permanently delete this post? This cannot be undone.')) return;
  router.delete(route('admin.posts.force-delete', id), { preserveScroll: true });
}

function toggleSelectAll(e) {
  if (e.target.checked) {
    selected.value = props.posts.data.map((p) => p.id);
  } else {
    selected.value = [];
  }
}

function bulkRestore() {
  if (!selected.value.length) return;
  if (!confirm(`Restore ${selected.value.length} post(s)?`)) return;
  router.post(route('admin.posts.bulk-restore'), { ids: selected.value }, { preserveScroll: true, preserveState: true });
}

function bulkForceDelete() {
  if (!selected.value.length) return;
  if (!confirm(`Permanently delete ${selected.value.length} post(s)? This cannot be undone.`)) return;
  router.delete(route('admin.posts.bulk-force-delete'), { data: { ids: selected.value }, preserveScroll: true, preserveState: true });
}
</script>

<template>
  <Head title="Posts Trash" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Posts Trash</h2>
        <div class="flex items-center gap-3">
          <Link :href="route('admin.posts.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to Posts</Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg">
          <div class="mb-4">
            <input v-model="search" type="text" placeholder="Search trashed posts..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
          </div>

          <div class="mb-3 flex items-center gap-2">
            <button @click="bulkRestore" :disabled="!selected.length" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
              :class="selected.length ? 'bg-green-600 text-white hover:bg-green-500' : 'bg-gray-200 text-gray-500 cursor-not-allowed'">
              Restore Selected
            </button>
            <button @click="bulkForceDelete" :disabled="!selected.length" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
              :class="selected.length ? 'bg-red-600 text-white hover:bg-red-500' : 'bg-gray-200 text-gray-500 cursor-not-allowed'">
              Delete Selected Permanently
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
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Deleted At</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="post in posts.data" :key="post.id">
                  <td class="w-10 px-3 py-2">
                    <input type="checkbox" :value="post.id" v-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" aria-label="Select row" />
                  </td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ post.id }}</td>
                  <td class="px-3 py-2 text-sm text-gray-900">{{ post.title || '(no title)' }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ post.slug }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ post.deleted_at }}</td>
                  <td class="px-3 py-2 text-right text-sm space-x-2">
                    <button @click="restoreItem(post.id)" class="inline-flex items-center rounded-md bg-green-600 px-2.5 py-1.5 text-white text-xs font-semibold shadow-sm hover:bg-green-500">Restore</button>
                    <button @click="forceDeleteItem(post.id)" class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-white text-xs font-semibold shadow-sm hover:bg-red-500">Delete Permanently</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <Link v-for="link in posts.links" :key="link.url + link.label" :href="link.url || '#'" preserve-scroll :class="[
              'px-3 py-1 rounded border text-sm',
              link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]" v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
