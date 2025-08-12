<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  roles: Object,
});

function destroyRole(id) {
  if (!confirm('Delete this role?')) return;
  router.delete(route('admin.roles.destroy', id));
}
</script>

<template>
  <Head title="Roles" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Roles</h2>
        <Link :href="route('admin.roles.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add New Role</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto w-full sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Permissions</th>
                  <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="role in props.roles.data" :key="role.id">
                  <td class="px-3 py-2 text-sm text-gray-700">{{ role.id }}</td>
                  <td class="px-3 py-2 text-sm text-gray-900">{{ role.name }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="perm in role.permissions" :key="perm" class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 border border-indigo-200">{{ perm }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2 text-sm text-right">
                    <div class="flex justify-end gap-2">
                      <Link :href="route('admin.roles.edit', role.id)" class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Edit</Link>
                      <button @click="destroyRole(role.id)" class="inline-flex items-center rounded-md bg-red-600 px-2 py-1 text-xs font-semibold text-white hover:bg-red-500">Delete</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <Link v-for="link in roles.links" :key="link.url + link.label" :href="link.url || '#'" preserve-scroll :class="[
              'px-3 py-1 rounded border text-sm',
              link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]" v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
