<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  menus: Object,
});
</script>

<template>
  <Head title="Menus" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Menus</h2>
        <Link :href="route('admin.menus.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Menu</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Location</th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="m in menus.data" :key="m.id">
                <td class="px-4 py-2 text-sm text-gray-900">{{ m.name }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ m.location || '-' }}</td>
                <td class="px-4 py-2 text-right text-sm">
                  <Link :href="route('admin.menus.edit', m.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
