<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: File,
  existingUrl: { type: String, default: '' },
  label: { type: String, default: 'Featured image' },
});

const emit = defineEmits(['update:modelValue']);
const fileInput = ref(null);
const previewUrl = ref(props.existingUrl || '');
let objectUrl = null;

function onFileChange(e) {
  const file = e.target.files?.[0] || null;
  emit('update:modelValue', file);
  if (objectUrl) URL.revokeObjectURL(objectUrl);
  if (file) {
    objectUrl = URL.createObjectURL(file);
    previewUrl.value = objectUrl;
  } else {
    previewUrl.value = props.existingUrl || '';
  }
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
      <input ref="fileInput" type="file" accept="image/*" @change="onFileChange" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
    </div>
  </div>
</template>
