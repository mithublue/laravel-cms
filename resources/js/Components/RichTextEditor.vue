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
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import { Extension } from '@tiptap/core';
import Suggestion from '@tiptap/suggestion';
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Write your content…' },
  // 'simple' | 'notion'
  uiMode: { type: String, default: 'notion' },
  // Show a small toggle to switch modes inside the component
  showUiSwitcher: { type: Boolean, default: true },
});
const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const fileInput = ref(null);
const uploading = ref(false);
const mode = ref(props.uiMode);

watch(() => props.uiMode, (v) => { mode.value = v; });

// Simple Notion-like slash command extension using Suggestion
const SlashCommand = Extension.create({
  name: 'slash-command',
  addOptions() {
    return {
      suggestion: {
        char: '/',
        startOfLine: true,
        items: ({ query }) => {
          const all = [
            { title: 'Text', subtitle: 'Start writing with plain text', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).setParagraph().run() },
            { title: 'Heading 1', subtitle: 'Big section heading', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).setHeading({ level: 1 }).run() },
            { title: 'Heading 2', subtitle: 'Medium section heading', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).setHeading({ level: 2 }).run() },
            { title: 'Heading 3', subtitle: 'Small section heading', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).setHeading({ level: 3 }).run() },
            { title: 'Bulleted list', subtitle: 'Create a simple bulleted list', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).toggleBulletList().run() },
            { title: 'Numbered list', subtitle: 'Create a list with numbering', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).toggleOrderedList().run() },
            { title: 'To-do list', subtitle: 'Track tasks with a to-do list', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).toggleTaskList().run() },
            { title: 'Quote', subtitle: 'Capture a quote', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).toggleBlockquote().run() },
            { title: 'Code block', subtitle: 'Capture a code snippet', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).toggleCodeBlock().run() },
            { title: 'Divider', subtitle: 'Visually divide blocks', command: ({ editor, range }) => editor.chain().focus().deleteRange(range).setHorizontalRule().run() },
            { title: 'Image', subtitle: 'Upload or paste an image', command: ({ editor, range }) => { editor.commands.deleteRange(range); fileInput.value?.click(); } },
            { title: 'YouTube', subtitle: 'Embed a YouTube video', command: ({ editor, range }) => { editor.commands.deleteRange(range); const url = prompt('YouTube URL'); if (url) editor.commands.setYoutubeVideo({ src: url }); } },
          ];
          return all.filter(item => item.title.toLowerCase().includes(query.toLowerCase()));
        },
        render: () => {
          let component;
          let popup;
          const renderItems = (root, { items, command }) => {
            root.innerHTML = '';
            items.forEach((item, idx) => {
              const el = document.createElement('button');
              el.type = 'button';
              el.className = 'w-full text-left px-3 py-2 hover:bg-gray-100 flex flex-col';
              el.setAttribute('data-index', String(idx));
              el.innerHTML = `<span class="font-medium">${item.title}</span><span class="text-gray-500">${item.subtitle || ''}</span>`;
              el.addEventListener('click', () => command(item));
              root.appendChild(el);
            });
            const first = root.querySelector('[data-index="0"]');
            first?.classList.add('active');
          };
          const highlight = (items, index) => {
            items.forEach(i => i.classList.remove('active'));
            items[index]?.classList.add('active');
          };
          const updatePosition = (el, rect) => {
            if (!rect) return;
            el.style.position = 'absolute';
            el.style.left = rect.left + window.scrollX + 'px';
            el.style.top = rect.bottom + window.scrollY + 6 + 'px';
            el.style.zIndex = 50;
          };
          const destroy = () => {
            if (component) {
              component.remove();
              component = null;
              popup = null;
            }
          };
          return {
            onStart: props => {
              component = document.createElement('div');
              component.className = 'rt-slash-menu shadow-lg border rounded-md bg-white text-sm min-w-[260px]';
              popup = document.createElement('div');
              component.appendChild(popup);
              document.body.appendChild(component);
              renderItems(popup, props);
              updatePosition(component, props.clientRect);
            },
            onUpdate(props) {
              renderItems(popup, props);
              updatePosition(component, props.clientRect);
            },
            onKeyDown(props) {
              if (props.event.key === 'Escape') { destroy(); return true; }
              const items = Array.from(popup.querySelectorAll('[data-index]'));
              const current = popup.querySelector('[data-index].active');
              let index = current ? Number(current.getAttribute('data-index')) : -1;
              if (props.event.key === 'ArrowDown') { index = Math.min(index + 1, items.length - 1); highlight(items, index); return true; }
              if (props.event.key === 'ArrowUp') { index = Math.max(index - 1, 0); highlight(items, index); return true; }
              if (props.event.key === 'Enter') { current?.dispatchEvent(new Event('click')); return true; }
              return false;
            },
            onExit() { destroy(); },
            destroy,
          };
        }
      }
    };
  },
  addProseMirrorPlugins() {
// Pass the editor instance to Suggestion to avoid undefined editor errors
    return [Suggestion({ editor: this.editor, ...this.options.suggestion })];
  },
});

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue || '',
    extensions: [
      StarterKit,
      TaskList,
      TaskItem.configure({ nested: true }),
      Placeholder.configure({ placeholder: () => mode.value === 'notion' ? "Type '/' for commands" : props.placeholder }),
      Link.configure({ openOnClick: false, autolink: true, linkOnPaste: true }),
      Image.configure({ inline: false, allowBase64: true }),
      Underline,
      Highlight,
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
      Youtube.configure({ controls: true, nocookie: false }),
      SlashCommand,
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
    <div class="flex items-center justify-between border-b bg-gray-50 p-2" v-if="mode === 'simple' || showUiSwitcher">
      <div class="flex flex-wrap items-center gap-1" v-if="mode === 'simple'">
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
      <div v-if="showUiSwitcher" class="ml-auto">
        <label class="inline-flex items-center gap-2 text-xs text-gray-600">
          <span>Simple</span>
          <button type="button" class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200" @click="mode = (mode === 'simple' ? 'notion' : 'simple')">
            <span class="sr-only">Toggle editor UI</span>
            <span :class="['inline-block h-4 w-4 transform rounded-full bg-white transition', mode === 'notion' ? 'translate-x-6' : 'translate-x-1']"></span>
          </button>
          <span>Notion</span>
        </label>
      </div>
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

/* Slash command menu */
.rt-slash-menu { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); }
.rt-slash-menu .active { background-color: #f3f4f6; }
</style>
