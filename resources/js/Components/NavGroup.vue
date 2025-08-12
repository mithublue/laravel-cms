<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    collapsed: { type: Boolean, default: false },
    active: { type: Boolean, default: false },
    openByDefault: { type: Boolean, default: false },
});

const open = ref(props.active || props.openByDefault);

watch(
    () => props.collapsed,
    (isCollapsed) => {
        if (isCollapsed) {
            open.value = false; // auto-close when sidebar is collapsed
        }
    }
);

watch(
    () => props.active,
    (isActive) => {
        // auto-open when a child route is active, auto-close when not
        open.value = !!isActive;
    }
);
</script>

<template>
    <div>
        <button
            type="button"
            class="w-full flex items-center justify-between rounded-md text-sm font-medium px-3 py-2"
            :class="[
                active ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
            ]"
            @click="open = !open"
            :title="collapsed ? label : undefined"
        >
            <span :class="[collapsed ? 'opacity-0 w-0 inline-block overflow-hidden' : 'inline-block']">{{ label }}</span>
            <span v-if="!collapsed" class="text-xs text-gray-500">{{ open ? '▾' : '▸' }}</span>
        </button>

        <div v-show="open && !collapsed" class="ml-3 space-y-1">
            <slot />
        </div>
    </div>
</template>
