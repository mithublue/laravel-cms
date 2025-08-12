<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  role: Object, // { id, name, permissions: [] }
  permissions: Array,
});

const form = useForm({
  name: props.role.name,
  permissions: props.role.permissions || [],
  _method: 'put',
});

const groups = computed(() => {
  const map = {};
  for (const p of props.permissions) {
    let key = 'General';
    if (p.startsWith('manage ')) {
      key = p.replace('manage ', '');
    } else if (p.startsWith('view ')) {
      key = p.replace('view ', '');
    }
    key = key.charAt(0).toUpperCase() + key.slice(1);
    (map[key] ||= []).push(p);
  }
  Object.values(map).forEach(arr => arr.sort());
  return Object.entries(map)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([name, perms]) => ({ name, perms }))
});

function toggleGroup(name, checked) {
  const group = groups.value.find(g => g.name === name);
  if (!group) return;
  if (checked) {
    const merged = new Set([...form.permissions, ...group.perms]);
    form.permissions = Array.from(merged);
  } else {
    form.permissions = form.permissions.filter(p => !group.perms.includes(p));
  }
}

function isGroupChecked(name) {
  const group = groups.value.find(g => g.name === name);
  if (!group) return false;
  return group.perms.every(p => form.permissions.includes(p));
}

function submit() {
  form.post(route('admin.roles.update', props.role.id));
}

function destroyRole() {
  if (!confirm('Delete this role?')) return;
  router.delete(route('admin.roles.destroy', props.role.id));
}
</script>

<template>
  <Head :title="`Edit Role: ${props.role.name}`" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Role</h2>
        <div class="flex items-center gap-3">
          <button @click="destroyRole" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Delete</button>
          <Link :href="route('admin.roles.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to Roles</Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto w-full sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Role Name</label>
              <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div>
              <div class="mb-2 flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700">Permissions</label>
                <div class="flex items-center gap-2 text-xs">
                  <button type="button" class="text-indigo-600 hover:underline" @click="form.permissions = [...props.permissions]">Select All</button>
                  <button type="button" class="text-gray-600 hover:underline" @click="form.permissions = []">Clear</button>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="group in groups" :key="group.name" class="rounded border border-gray-200">
                  <div class="flex items-center justify-between border-b border-gray-200 px-3 py-2 bg-gray-50">
                    <div class="font-semibold text-sm text-gray-800">{{ group.name }}</div>
                    <label class="inline-flex items-center gap-2 text-xs text-gray-700">
                      <input type="checkbox" :checked="isGroupChecked(group.name)" @change="toggleGroup(group.name, $event.target.checked)" />
                      All
                    </label>
                  </div>
                  <div class="p-3 space-y-2">
                    <label v-for="perm in group.perms" :key="perm" class="flex items-center gap-2 text-sm text-gray-700">
                      <input type="checkbox" :value="perm" v-model="form.permissions" />
                      <span>{{ perm }}</span>
                    </label>
                  </div>
                </div>
              </div>
              <div v-if="form.errors.permissions" class="mt-2 text-sm text-red-600">{{ form.errors.permissions }}</div>
            </div>

            <div class="pt-2">
              <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                Update Role
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
