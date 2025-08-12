<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TomMultiSelect from '@/Components/TomMultiSelect.vue';

const props = defineProps({
  user: Object,
  roles: Array,
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  roles: props.user.roles || [],
  _method: 'put',
});

function submit() {
  form.post(route('admin.users.update', props.user.id));
}
</script>

<template>
  <Head :title="`Edit: ${props.user.name}`" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit User</h2>
        <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to Users</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto w-full sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input v-model="form.password" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password.</p>
                <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Roles</label>
              <TomMultiSelect
                v-model="form.roles"
                :options="props.roles"
                placeholder="Select roles"
                class="mt-1 block w-full"
              />
              <div v-if="form.errors.roles" class="mt-1 text-sm text-red-600">{{ form.errors.roles }}</div>
            </div>

            <div class="pt-2 flex items-center gap-3">
              <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                Update User
              </button>
              <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900">Cancel</Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
