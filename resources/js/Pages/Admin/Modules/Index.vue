<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  modules: Array,
});

function toggle(module) {
  router.put(route('admin.modules.update', module.id), { enabled: !module.enabled }, { preserveScroll: true });
}
</script>

<template>
  <Head title="Modules" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Modules</h2>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="m in props.modules" :key="m.id" class="border rounded-lg p-4 flex flex-col gap-3">
              <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold capitalize">{{ m.name }}</h3>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="m.enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                  {{ m.enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </div>
              <button @click="toggle(m)" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
                :class="m.enabled ? 'bg-red-600 text-white hover:bg-red-500' : 'bg-indigo-600 text-white hover:bg-indigo-500'">
                {{ m.enabled ? 'Disable' : 'Enable' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
