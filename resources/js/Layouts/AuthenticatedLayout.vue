<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 flex">
            <!-- Sidebar -->
            <aside class="w-64 bg-white border-r border-gray-200">
                <div class="h-16 flex items-center px-4 border-b border-gray-100">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                    </Link>
                </div>

                <nav class="p-4 space-y-1">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</NavLink>
                    <NavLink :href="route('admin.pages.index')" :active="route().current('admin.pages.*')">Pages</NavLink>
                    <NavLink :href="route('admin.posts.index')" :active="route().current('admin.posts.*')">Posts</NavLink>
                    <NavLink :href="route('admin.news.index')" :active="route().current('admin.news.*')">News</NavLink>
                    <NavLink href="#">Products</NavLink>
                    <NavLink href="#">Media</NavLink>
                    <NavLink href="#">Menus</NavLink>
                    <NavLink href="#">Users</NavLink>
                    <Link :href="route('profile.edit')" class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">Profile</Link>
                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">Log Out</Link>
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
