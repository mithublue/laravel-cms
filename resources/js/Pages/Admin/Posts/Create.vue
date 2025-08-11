<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import FeaturedImageUploader from '@/Components/FeaturedImageUploader.vue';

const form = useForm({
  title: '',
  slug: '',
  status: 'draft',
  visibility: 'public',
  published_at: '',
  is_pinned: false,
  allow_comments: true,
  excerpt: '',
  content: '',
  featured_image: null,
});

function submit() {
  form.post(route('admin.posts.store'), { forceFormData: true });
}
</script>

<template>
  <Head title="Create Post" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Post</h2>
        <Link :href="route('admin.posts.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to list</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Main -->
          <div class="lg:col-span-2 space-y-4">
            <div class="bg-white p-6 shadow sm:rounded-lg">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Title</label>
                  <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                  <textarea v-model="form.excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                  <div v-if="form.errors.excerpt" class="mt-1 text-sm text-red-600">{{ form.errors.excerpt }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Content</label>
                  <RichTextEditor v-model="form.content" />
                  <div v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Slug (optional)</label>
                <input v-model="form.slug" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</div>
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

              <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" v-model="form.is_pinned" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  Pinned
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" v-model="form.allow_comments" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                  Allow comments
                </label>
              </div>
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
              <FeaturedImageUploader v-model="form.featured_image" />
              <div v-if="form.errors.featured_image" class="mt-1 text-sm text-red-600">{{ form.errors.featured_image }}</div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg flex items-center gap-3">
              <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Save</button>
              <span v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</span>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
