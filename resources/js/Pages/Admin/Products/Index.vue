<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
  products: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const selected = ref([]);
const allSelected = computed(() => props.products?.data?.length && selected.value.length === props.products.data.length);

function toggleSelectAll(e) {
  if (e.target.checked) {
    selected.value = props.products.data.map((p) => p.id);
  } else {
    selected.value = [];
  }
}

function bulkTrash() {
  if (!selected.value.length) return;
  if (!confirm(`Move ${selected.value.length} selected product(s) to Trash?`)) return;
  router.post(route('admin.products.bulk-destroy'), { ids: selected.value }, { preserveScroll: true, preserveState: true });
}

watch(search, (value) => {
  router.get(route('admin.products.index'), { search: value }, { preserveState: true, replace: true });
});
</script>

<template>
  <Head title="Products" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Products</h2>
        <Link :href="route('admin.products.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Product</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg">
          <div class="mb-4">
            <input v-model="search" type="text" placeholder="Search products..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
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
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Slug</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">SKU</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Price</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Stock</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="p in products.data" :key="p.id">
                  <td class="w-10 px-3 py-2">
                    <input type="checkbox" :value="p.id" v-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" aria-label="Select row" />
                  </td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ p.id }}</td>
                  <td class="px-3 py-2 text-sm text-gray-900">{{ p.name || '(no name)' }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ p.slug }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ p.sku }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ p.price }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ p.stock_qty }}</td>
                  <td class="px-3 py-2 text-sm text-gray-700">{{ p.status }}</td>
                  <td class="px-3 py-2 text-right text-sm">
                    <Link :href="route('admin.products.edit', p.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            <Link v-for="link in products.links" :key="link.url + link.label" :href="link.url || '#'" preserve-scroll :class="[
              'px-3 py-1 rounded border text-sm',
              link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]" v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
