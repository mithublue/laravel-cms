<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import FeaturedImageUploader from '@/Components/FeaturedImageUploader.vue';
import TaxonomyManager from '@/Components/TaxonomyManager.vue';

const form = useForm({
  name: '',
  slug: '',
  short_description: '',
  description: '',
  sku: '',
  type: 'simple',
  price: '0',
  sale_price: '',
  currency: 'USD',
  stock_qty: 0,
  manage_stock: true,
  stock_status: 'in_stock',
  backorder: false,
  status: 'draft',
  visibility: 'public',
  published_at: '',
  featured_image: null,
  terms: [],
});

function submit() {
  form.post(route('admin.products.store'), {
    forceFormData: true,
  });
}

// Right sidebar collapse state
const rightCollapsed = ref(false);
</script>

<template>
  <Head title="Create Product" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Product</h2>
        <Link :href="route('admin.products.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to list</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <form
          @submit.prevent="submit"
          :class="['grid grid-cols-1 gap-6', rightCollapsed ? 'lg:grid-cols-1' : 'lg:grid-cols-3']"
        >
          <!-- Main -->
          <div :class="[rightCollapsed ? 'lg:col-span-1' : 'lg:col-span-2', 'space-y-4']">
            <div class="bg-white p-6 shadow sm:rounded-lg">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Name</label>
                  <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Short Description</label>
                  <textarea v-model="form.short_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                  <div v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <RichTextEditor v-model="form.description" />
                  <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6" v-show="!rightCollapsed">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Options</h3>
                <button type="button" @click="rightCollapsed = true" class="text-xs text-gray-500 hover:text-gray-700">Hide »</button>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Slug</label>
                <input v-model="form.slug" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">SKU</label>
                <input v-model="form.sku" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</div>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Price</label>
                  <input v-model="form.price" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Sale Price</label>
                  <input v-model="form.sale_price" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.sale_price" class="mt-1 text-sm text-red-600">{{ form.errors.sale_price }}</div>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Currency</label>
                  <input v-model="form.currency" type="text" maxlength="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm uppercase focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Stock Qty</label>
                  <input v-model="form.stock_qty" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.stock_qty" class="mt-1 text-sm text-red-600">{{ form.errors.stock_qty }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Stock Status</label>
                  <select v-model="form.stock_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="in_stock">In stock</option>
                    <option value="out_of_stock">Out of stock</option>
                    <option value="backorder">Backorder</option>
                  </select>
                  <div v-if="form.errors.stock_status" class="mt-1 text-sm text-red-600">{{ form.errors.stock_status }}</div>
                </div>
              </div>

              <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" v-model="form.manage_stock" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  Manage stock
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" v-model="form.backorder" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  Allow backorder
                </label>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Status</label>
                  <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                  </select>
                  <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Visibility</label>
                  <select v-model="form.visibility" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                  </select>
                  <div v-if="form.errors.visibility" class="mt-1 text-sm text-red-600">{{ form.errors.visibility }}</div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Publish at</label>
                <input v-model="form.published_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.published_at" class="mt-1 text-sm text-red-600">{{ form.errors.published_at }}</div>
              </div>
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
              <FeaturedImageUploader v-model="form.featured_image" />
              <div v-if="form.errors.featured_image" class="mt-1 text-sm text-red-600">{{ form.errors.featured_image }}</div>
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
              <TaxonomyManager v-model="form.terms" scope="product" />
              <div v-if="form.errors.terms" class="mt-1 text-sm text-red-600">{{ form.errors.terms }}</div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg flex items-center gap-3">
              <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Create</button>
              <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</span>
            </div>
          </div>
        </form>

        <!-- Floating button to show sidebar when collapsed -->
        <button
          v-if="rightCollapsed"
          type="button"
          @click="rightCollapsed = false"
          class="fixed bottom-6 right-6 rounded-full bg-white shadow px-3 py-2 text-sm text-gray-700 border hover:bg-gray-50"
          title="Show sidebar"
        >« Show Sidebar</button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
