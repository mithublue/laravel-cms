<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import MediaLibraryModal from '@/Components/MediaLibraryModal.vue';

const props = defineProps({
  modelValue: File,
  existingUrl: { type: String, default: '' },
  label: { type: String, default: 'Featured image' },
  enableLibrary: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'file-selected', 'select-existing']);
const fileInput = ref(null);
const previewUrl = ref(props.existingUrl || '');
let objectUrl = null;
const libraryOpen = ref(false);

function onFileChange(e) {
  const file = e.target.files?.[0] || null;
  emit('update:modelValue', file);
  // when a file is selected, notify parent to clear any media id
  emit('file-selected');
  if (objectUrl) URL.revokeObjectURL(objectUrl);
  if (file) {
    objectUrl = URL.createObjectURL(file);
    previewUrl.value = objectUrl;
  } else {
    previewUrl.value = props.existingUrl || '';
  }
}

function onSelectExisting(item) {
  // clear any file selection
  if (fileInput.value) fileInput.value.value = '';
  emit('update:modelValue', null);
  emit('select-existing', item);
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl);
    objectUrl = null;
  }
  previewUrl.value = item?.url || '';
}

watch(() => props.existingUrl, (val) => {
  if (!props.modelValue) previewUrl.value = val || '';
});

onBeforeUnmount(() => {
  if (objectUrl) URL.revokeObjectURL(objectUrl);
});
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">{{ label }}</label>
    <div class="space-y-3">
      <div v-if="previewUrl" class="aspect-video w-full overflow-hidden rounded border">
        <img :src="previewUrl" alt="Featured" class="h-full w-full object-cover" />
      </div>
      <div class="flex items-center gap-3">
        <input ref="fileInput" type="file" accept="image/*" @change="onFileChange" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
        <button v-if="props.enableLibrary" type="button" class="rounded-md bg-gray-100 px-3 py-2 text-xs hover:bg-gray-200" @click="libraryOpen = true">Choose from Library</button>
      </div>
      <MediaLibraryModal v-if="props.enableLibrary" v-model="libraryOpen" mime="image" @select="onSelectExisting" />
    </div>
  </div>
</template>
