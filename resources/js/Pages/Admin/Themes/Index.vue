<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  active: String,
  available: Array,
});

const form = useForm({ theme: '' });

function activate(theme) {
  form.theme = theme;
  form.post(route('admin.themes.activate'));
}
</script>

<template>
  <Head title="Themes" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Themes</h2>
    </template>

    <div class="max-w-5xl mx-auto">
      <div class="bg-white shadow rounded p-6">
        <div class="mb-4">
          <div class="text-sm text-gray-600">Active theme:</div>
          <div class="text-lg font-medium">{{ active }}</div>
        </div>
        <hr class="my-4" />
        <div>
          <h3 class="font-semibold mb-2">Available themes</h3>
          <div v-if="!available || !available.length" class="text-gray-500 text-sm">No themes found. Create folders under <code>resources/themes/*</code>.</div>
          <ul class="divide-y">
            <li v-for="t in available" :key="t" class="flex items-center justify-between py-3">
              <div>
                <div class="font-medium" :class="{ 'text-green-700': t === active }">{{ t }}</div>
                <div v-if="t === active" class="text-xs text-green-700">Active</div>
              </div>
              <button
                v-if="t !== active"
                @click="activate(t)"
                class="px-3 py-1.5 rounded bg-indigo-600 text-white text-sm hover:bg-indigo-700"
              >Activate</button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
