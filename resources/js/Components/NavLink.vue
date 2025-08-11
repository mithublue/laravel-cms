<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: '',
    },
});

const classes = computed(() => {
    const base = 'block w-full rounded-md text-sm font-medium';
    const state = props.active
        ? 'text-gray-900 bg-gray-100'
        : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900';
    const padding = props.collapsed ? 'px-2 py-2 text-center' : 'px-3 py-2';
    return `${base} ${padding} ${state}`;
});
</script>

<template>
    <Link :href="href" :class="classes" :title="label || undefined">
        <span :class="[collapsed ? 'opacity-0 w-0 inline-block overflow-hidden' : 'inline-block']">
            <slot />
        </span>
    </Link>
</template>
