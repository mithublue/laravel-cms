<script setup>
import { defineEmits, defineProps } from 'vue';

const emit = defineEmits(['remove', 'up', 'down', 'indent', 'outdent', 'select']);
const props = defineProps({
  item: { type: Object, required: true },
  path: { type: Array, required: true },
});

function onRemove() { emit('remove', props.path); }
function onUp() { emit('up', props.path); }
function onDown() { emit('down', props.path); }
function onIndent() { emit('indent', props.path); }
function onOutdent() { emit('outdent', props.path); }
function onSelect() { emit('select', props.item); }
</script>

<script>
// Ensure component can recursively reference itself
export default { name: 'MenuItemNode' };
</script>

<template>
  <div class="rounded border border-gray-200">
    <div class="flex items-center justify-between gap-2 bg-gray-50 px-2 py-1">
      <button type="button" @click="onSelect" class="flex-1 text-left text-sm text-gray-800 truncate">{{ item.title || '(no title)' }}</button>
      <div class="flex items-center gap-1">
        <button type="button" @click="onUp" title="Move up" class="rounded px-2 py-1 text-xs bg-white border hover:bg-gray-50">↑</button>
        <button type="button" @click="onDown" title="Move down" class="rounded px-2 py-1 text-xs bg-white border hover:bg-gray-50">↓</button>
        <button type="button" @click="onIndent" title="Indent" class="rounded px-2 py-1 text-xs bg-white border hover:bg-gray-50">→</button>
        <button type="button" @click="onOutdent" title="Outdent" class="rounded px-2 py-1 text-xs bg-white border hover:bg-gray-50">←</button>
        <button type="button" @click="onRemove" title="Remove" class="rounded px-2 py-1 text-xs bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">✕</button>
      </div>
    </div>
    <div class="space-y-2 p-2 pl-4">
      <MenuItemNode
        v-for="(child, idx) in item.children"
        :key="(child.id ?? 'new') + '-' + idx"
        :item="child"
        :path="[...path, idx]"
        @remove="$emit('remove', $event)"
        @up="$emit('up', $event)"
        @down="$emit('down', $event)"
        @indent="$emit('indent', $event)"
        @outdent="$emit('outdent', $event)"
        @select="$emit('select', $event)"
      />
    </div>
  </div>
</template>
