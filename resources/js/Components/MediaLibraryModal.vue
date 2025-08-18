<script setup>
import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  mime: { type: String, default: 'image' },
});
const emit = defineEmits(['update:modelValue', 'select']);

const open = ref(props.modelValue);
const items = ref([]);
const loading = ref(false);
const error = ref('');
const q = ref('');
const next = ref(null);
const prev = ref(null);
const uploading = ref(false);

watch(() => props.modelValue, (v) => { open.value = v; if (v) fetchPage(); });
watch(open, (v) => emit('update:modelValue', v));

async function fetchPage(url) {
  loading.value = true;
  error.value = '';
  try {
    const endpoint = url || route('admin.media.index', { mime: props.mime, q: q.value, per_page: 24 });
    const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('Failed to load media');
    const json = await res.json();
    items.value = json.data || [];
    next.value = json.links?.next || null;
    prev.value = json.links?.prev || null;
  } catch (e) {
    error.value = e?.message || String(e);
  } finally {
    loading.value = false;
  }
}

function close() { open.value = false; }

function selectItem(item) {
  emit('select', item);
  close();
}

async function handleUpload(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  uploading.value = true;
  try {
    const fd = new FormData();
    fd.append('file', file);
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const res = await fetch(route('admin.media.upload'), {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: fd,
    });
    if (!res.ok) throw new Error('Upload failed');
    const json = await res.json();
    // refresh list and preselect uploaded item
    await fetchPage();
    emit('select', { id: json.id, url: json.url, original_name: json.name, mime_type: json.mime, size: json.size, width: json.width, height: json.height });
    close();
  } catch (e) {
    alert(e?.message || 'Upload error');
  } finally {
    uploading.value = false;
    e.target.value = '';
  }
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/30" @click="close" />
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-5xl rounded-lg bg-white shadow-lg">
        <div class="flex items-center justify-between border-b px-4 py-3">
          <h3 class="text-sm font-medium">Media Library</h3>
          <button type="button" class="text-gray-500 hover:text-gray-700" @click="close">✕</button>
        </div>
        <div class="p-4 space-y-3">
          <div class="flex items-center gap-3">
            <input v-model="q" @keyup.enter="fetchPage()" type="text" placeholder="Search media..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
            <button @click="fetchPage()" class="rounded-md bg-gray-100 px-3 py-2 text-sm">Search</button>
            <label class="ml-auto inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 cursor-pointer">
              <input type="file" accept="image/*" class="hidden" :disabled="uploading" @change="handleUpload" />
              <span v-if="!uploading">Upload</span>
              <span v-else>Uploading...</span>
            </label>
          </div>

          <div v-if="error" class="text-sm text-red-600">{{ error }}</div>
          <div v-else-if="loading" class="text-sm text-gray-600">Loading…</div>
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <button v-for="item in items" :key="item.id" type="button" class="group border rounded overflow-hidden hover:ring-2 hover:ring-indigo-500" @click="selectItem(item)">
              <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                <img v-if="item.url" :src="item.url" class="h-full w-full object-cover" :alt="item.original_name || ''" />
                <div v-else class="text-xs text-gray-500 p-2">{{ item.original_name || 'Media' }}</div>
              </div>
              <div class="px-2 py-1 text-[11px] truncate text-gray-700">{{ item.original_name || item.filename }}</div>
            </button>
          </div>

          <div class="flex items-center justify-between pt-2 border-t mt-2">
            <button type="button" class="rounded-md px-3 py-1.5 text-sm bg-gray-100 disabled:opacity-50" :disabled="!prev" @click="fetchPage(prev)">Previous</button>
            <button type="button" class="rounded-md px-3 py-1.5 text-sm bg-gray-100 disabled:opacity-50" :disabled="!next" @click="fetchPage(next)">Next</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
