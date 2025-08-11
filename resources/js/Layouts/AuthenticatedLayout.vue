<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const sidebarCollapsed = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 flex">
            <!-- Sidebar -->
            <aside :class="['bg-white border-r border-gray-200 transition-all duration-300 ease-in-out overflow-hidden', sidebarCollapsed ? 'w-16' : 'w-64']">
                <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                    </Link>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="rounded p-2 text-gray-600 hover:bg-gray-100" :title="sidebarCollapsed ? 'Expand menu' : 'Collapse menu'">
                        <span v-if="sidebarCollapsed">»</span>
                        <span v-else>«</span>
                    </button>
                </div>

                <nav class="p-4 space-y-1">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')" :collapsed="sidebarCollapsed" label="Dashboard">Dashboard</NavLink>
                    <NavLink :href="route('admin.pages.index')" :active="route().current('admin.pages.*')" :collapsed="sidebarCollapsed" label="Pages">Pages</NavLink>
                    <NavLink :href="route('admin.posts.index')" :active="route().current('admin.posts.*')" :collapsed="sidebarCollapsed" label="Posts">Posts</NavLink>
                    <NavLink :href="route('admin.news.index')" :active="route().current('admin.news.*')" :collapsed="sidebarCollapsed" label="News">News</NavLink>
                    <NavLink :href="route('admin.products.index')" :active="route().current('admin.products.*')" :collapsed="sidebarCollapsed" label="Products">Products</NavLink>
                    <NavLink href="#" :collapsed="sidebarCollapsed" label="Media">Media</NavLink>
                    <NavLink :href="route('admin.menus.index')" :active="route().current('admin.menus.*')" :collapsed="sidebarCollapsed" label="Menus">Menus</NavLink>
                    <NavLink href="#" :collapsed="sidebarCollapsed" label="Users">Users</NavLink>
                    <Link :href="route('profile.edit')" class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100" :title="sidebarCollapsed ? 'Profile' : undefined">Profile</Link>
                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100" :title="sidebarCollapsed ? 'Log Out' : undefined">Log Out</Link>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Page Heading -->
                <header class="bg-white shadow" v-if="$slots.header">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
