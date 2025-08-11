<script setup>
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import Highlight from '@tiptap/extension-highlight';
import Youtube from '@tiptap/extension-youtube';
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Write your content…' },
});
const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const fileInput = ref(null);
const uploading = ref(false);

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue || '',
    extensions: [
      StarterKit,
      Placeholder.configure({ placeholder: props.placeholder }),
      Link.configure({ openOnClick: false, autolink: true, linkOnPaste: true }),
      Image.configure({ inline: false, allowBase64: true }),
      Underline,
      Highlight,
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
      Youtube.configure({ controls: true, nocookie: false }),
    ],
    editorProps: {
      attributes: {
        class: 'prose prose-sm sm:prose max-w-none focus:outline-none',
      },
      handlePaste(view, event) {
        const items = event.clipboardData?.items || [];
        for (const item of items) {
          if (item.type?.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) {
              uploadImage(file);
              return true;
            }
          }
        }
        return false;
      },
      handleDrop(view, event) {
        const files = event.dataTransfer?.files || [];
        if (files.length) {
          for (const file of files) {
            if (file.type?.startsWith('image/')) {
              uploadImage(file);
            }
          }
          return true;
        }
        return false;
      },
    },
    onUpdate: ({ editor }) => {
      emit('update:modelValue', editor.getHTML());
    },
  });
});

watch(() => props.modelValue, (val) => {
  if (editor.value && editor.value.getHTML() !== val) {
    editor.value.commands.setContent(val || '', false);
  }
});

onBeforeUnmount(() => {
  editor.value?.destroy();
});

function toggle(cmd) {
  switch (cmd) {
    case 'bold':
      editor.value?.chain().focus().toggleBold().run();
      break;
    case 'italic':
      editor.value?.chain().focus().toggleItalic().run();
      break;
    case 'strike':
      editor.value?.chain().focus().toggleStrike().run();
      break;
    case 'code':
      editor.value?.chain().focus().toggleCode().run();
      break;
    case 'h2':
      editor.value?.chain().focus().toggleHeading({ level: 2 }).run();
      break;
    case 'h3':
      editor.value?.chain().focus().toggleHeading({ level: 3 }).run();
      break;
    case 'bullet':
      editor.value?.chain().focus().toggleBulletList().run();
      break;
    case 'ordered':
      editor.value?.chain().focus().toggleOrderedList().run();
      break;
    case 'blockquote':
      editor.value?.chain().focus().toggleBlockquote().run();
      break;
    case 'codeblock':
      editor.value?.chain().focus().toggleCodeBlock().run();
      break;
    case 'link': {
      const url = prompt('Enter URL');
      if (url) editor.value?.chain().focus().setLink({ href: url }).run();
      break;
    }
    case 'unlink':
      editor.value?.chain().focus().unsetLink().run();
      break;
    case 'image':
      fileInput.value?.click();
      break;
    case 'underline':
      editor.value?.chain().focus().toggleUnderline().run();
      break;
    case 'highlight':
      editor.value?.chain().focus().toggleHighlight().run();
      break;
    case 'hr':
      editor.value?.chain().focus().setHorizontalRule().run();
      break;
    case 'left':
      editor.value?.chain().focus().setTextAlign('left').run();
      break;
    case 'center':
      editor.value?.chain().focus().setTextAlign('center').run();
      break;
    case 'right':
      editor.value?.chain().focus().setTextAlign('right').run();
      break;
    case 'youtube': {
      const url = prompt('Enter YouTube URL');
      if (url) editor.value?.chain().focus().setYoutubeVideo({ src: url }).run();
      break;
    }
    default:
      break;
  }
}
async function uploadImage(file) {
  try {
    uploading.value = true;
    const form = new FormData();
    form.append('file', file);

    // Prefer axios if available for CSRF headers
    let resp;
    if (window.axios) {
      resp = await window.axios.post(window.route ? window.route('admin.media.upload') : '/admin/media/upload', form);
      resp = resp.data;
    } else {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch(window.route ? window.route('admin.media.upload') : '/admin/media/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: form,
      });
      resp = await res.json();
    }

    if (resp?.url) {
      editor.value?.chain().focus().setImage({ src: resp.url, alt: resp.name || '' }).run();
    }
  } catch (e) {
    console.error('Image upload failed', e);
  } finally {
    uploading.value = false;
  }
}

function onFilePicked(e) {
  const file = e.target.files?.[0];
  if (file) uploadImage(file);
  e.target.value = '';
}
</script>

<template>
  <div class="border rounded-md">
    <div class="flex flex-wrap items-center gap-1 border-b bg-gray-50 p-2">
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('bold')">Bold</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('italic')">Italic</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('strike')">Strike</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('underline')">Underline</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('highlight')">Highlight</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('h2')">H2</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('h3')">H3</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('bullet')">• List</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('ordered')">1. List</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('left')">Left</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('center')">Center</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('right')">Right</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('blockquote')">Quote</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('codeblock')">Code</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('hr')">Rule</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('link')">Link</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('unlink')">Unlink</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" :disabled="uploading" @click="toggle('image')">{{ uploading ? 'Uploading…' : 'Image' }}</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('youtube')">YouTube</button>
      <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFilePicked" />
    </div>
    <div class="p-3 min-h-[240px]">
      <EditorContent :editor="editor" />
    </div>
  </div>
</template>

<style>
/* TipTap base styles tweaks */
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #9ca3af;
  pointer-events: none;
  height: 0;
}
</style>
