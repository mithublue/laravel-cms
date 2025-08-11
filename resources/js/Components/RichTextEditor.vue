<script setup>
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Write your content…' },
});
const emit = defineEmits(['update:modelValue']);

const editor = ref(null);

onMounted(() => {
  editor.value = new Editor({
    content: props.modelValue || '',
    extensions: [
      StarterKit,
    ],
    editorProps: {
      attributes: {
        class: 'prose prose-sm sm:prose max-w-none focus:outline-none',
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
    default:
      break;
  }
}
</script>

<template>
  <div class="border rounded-md">
    <div class="flex flex-wrap items-center gap-1 border-b bg-gray-50 p-2">
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('bold')">Bold</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('italic')">Italic</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('strike')">Strike</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('h2')">H2</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('h3')">H3</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('bullet')">• List</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('ordered')">1. List</button>
      <span class="mx-2 h-5 w-px bg-gray-300"></span>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('blockquote')">Quote</button>
      <button type="button" class="px-2 py-1 text-sm rounded hover:bg-gray-100" @click="toggle('codeblock')">Code</button>
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
