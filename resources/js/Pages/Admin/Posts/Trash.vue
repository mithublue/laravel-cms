<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  posts: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');

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

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Slug</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Deleted At</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="post in posts.data" :key="post.id">
                  <td class="px-3 py-2 text-sm text-gray-700">{{ post.id }}</td>
                  <td class="px-3 py-2 text-sm text-gray-900">{{ post.title || '(no title)' }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ post.slug }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ post.deleted_at }}</td>
                  <td class="px-3 py-2 text-right text-sm space-x-3">
                    <button @click="restoreItem(post.id)" class="text-green-600 hover:text-green-800">Restore</button>
                    <button @click="forceDeleteItem(post.id)" class="text-red-600 hover:text-red-800">Delete Permanently</button>
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
