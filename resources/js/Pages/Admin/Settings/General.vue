<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import FeaturedImageUploader from '@/Components/FeaturedImageUploader.vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true,
    default: () => ({
      site_name: '',
      tagline: '',
      admin_email: '',
      locale: 'en',
      datetime_format: 'Y-m-d H:i',
      logo_url: '',
      favicon_url: '',
    }),
  },
});

const form = useForm({
  site_name: props.settings.site_name || '',
  tagline: props.settings.tagline || '',
  admin_email: props.settings.admin_email || '',
  locale: props.settings.locale || 'en',
  datetime_format: props.settings.datetime_format || 'Y-m-d H:i',
  logo: null,
  favicon: null,
});

function submit() {
  form.post(route('admin.settings.general.update'), {
    forceFormData: true,
  });
}

// Sidebar collapse (consistent UX with other pages)
const rightCollapsed = ref(false);
</script>

<template>
  <Head title="Settings - General" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Settings · General</h2>
        <Link :href="route('dashboard')" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Main -->
          <div class="lg:col-span-2 space-y-4">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Site name</label>
                <input v-model="form.site_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.site_name" class="mt-1 text-sm text-red-600">{{ form.errors.site_name }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Tagline</label>
                <input v-model="form.tagline" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.tagline" class="mt-1 text-sm text-red-600">{{ form.errors.tagline }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Admin email</label>
                <input v-model="form.admin_email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</div>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Locale</label>
                  <input v-model="form.locale" type="text" placeholder="e.g. en, en_US, fr" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.locale" class="mt-1 text-sm text-red-600">{{ form.errors.locale }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Datetime format</label>
                  <input v-model="form.datetime_format" type="text" placeholder="e.g. Y-m-d H:i" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.datetime_format" class="mt-1 text-sm text-red-600">{{ form.errors.datetime_format }}</div>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
              <FeaturedImageUploader v-model="form.logo" :existing-url="props.settings.logo_url || ''" label="Logo" />
              <div v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</div>
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
              <FeaturedImageUploader v-model="form.favicon" :existing-url="props.settings.favicon_url || ''" label="Favicon" />
              <div v-if="form.errors.favicon" class="mt-1 text-sm text-red-600">{{ form.errors.favicon }}</div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg flex items-center gap-3">
              <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Save</button>
              <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</span>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <div class="bg-white p-6 shadow sm:rounded-lg">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-700">Options</h3>
              </div>
              <p class="mt-2 text-xs text-gray-500">Update general site information and branding assets.</p>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
