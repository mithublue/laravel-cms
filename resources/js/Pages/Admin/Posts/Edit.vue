<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import FeaturedImageUploader from '@/Components/FeaturedImageUploader.vue';

const props = defineProps({
  post: Object,
});

// Right sidebar collapse state
const rightCollapsed = ref(false);

const form = useForm({
  title: props.post?.title || '',
  slug: props.post?.slug || '',
  status: props.post?.status || 'draft',
  visibility: props.post?.visibility || 'public',
  published_at: props.post?.published_at || '',
  is_pinned: props.post?.is_pinned || false,
  allow_comments: props.post?.allow_comments ?? true,
  excerpt: props.post?.excerpt || '',
  content: props.post?.content || '',
  featured_image: null,
});

function submit() {
  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('admin.posts.update', props.post.id), {
      forceFormData: true,
      headers: { 'X-HTTP-Method-Override': 'PUT' },
      onFinish: () => form.transform((data) => data),
    });
}

function destroy() {
  if (confirm('Delete this post?')) {
    router.delete(route('admin.posts.destroy', props.post.id));
  }
}
</script>

<template>
  <Head title="Edit Post" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Post</h2>
        <div class="flex items-center gap-3">
          <button @click="destroy" class="text-sm text-red-600 hover:text-red-700">Delete</button>
          <Link :href="route('admin.posts.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to list</Link>
        </div>
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
              <FeaturedImageUploader v-model="form.featured_image" :existing-url="props.post?.featured_image_url || ''" />
              <div v-if="form.errors.featured_image" class="mt-1 text-sm text-red-600">{{ form.errors.featured_image }}</div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg flex items-center gap-3">
              <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Update</button>
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
