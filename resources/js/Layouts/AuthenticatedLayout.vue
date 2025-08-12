<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import NavGroup from '@/Components/NavGroup.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const sidebarCollapsed = ref(false);

onMounted(() => {
    const mq = window.matchMedia('(max-width: 1024px)');
    const handler = () => {
        sidebarCollapsed.value = mq.matches;
    };
    handler();
    mq.addEventListener('change', handler);
    onUnmounted(() => mq.removeEventListener('change', handler));
});
</script>

<template>
    <div class="admin-layout">
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
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')" :collapsed="sidebarCollapsed" label="Dashboard">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </template>
                        Dashboard
                    </NavLink>
                    <a href="/" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-indigo-700 hover:bg-indigo-50" :title="sidebarCollapsed ? 'View Site' : undefined">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3c4.97 0 9 4.03 9 9s-4.03 9-9 9-9-4.03-9-9 4.03-9 9-9zm0 0c2.761 0 5 4.03 5 9s-2.239 9-5 9-5-4.03-5-9 2.239-9 5-9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12h18"/></svg>
                        <span :class="[sidebarCollapsed ? 'opacity-0 w-0 inline-block overflow-hidden' : 'inline-block']">View Site ↗</span>
                    </a>

                    <!-- Pages -->
                    <NavGroup label="Pages" :collapsed="sidebarCollapsed" :active="route().current('admin.pages.*')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h7l5 5v9a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                        </template>
                        <NavLink :href="route('admin.pages.index')" :active="route().current('admin.pages.index')" :collapsed="false" label="All Pages">All Pages</NavLink>
                        <NavLink :href="route('admin.pages.create')" :active="route().current('admin.pages.create')" :collapsed="false" label="Add New Page">Add New Page</NavLink>
                        <NavLink :href="route('admin.pages.trash')" :active="route().current('admin.pages.trash')" :collapsed="false" label="Trash">Trash</NavLink>
                    </NavGroup>

                    <!-- Posts -->
                    <NavGroup label="Posts" :collapsed="sidebarCollapsed" :active="route().current('admin.posts.*')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h10M7 11h10M7 15h7M5 5a2 2 0 012-2h10a2 2 0 012 2v14l-4-3H7a2 2 0 01-2-2V5z"/></svg>
                        </template>
                        <NavLink :href="route('admin.posts.index')" :active="route().current('admin.posts.index')" :collapsed="false" label="All Posts">All Posts</NavLink>
                        <NavLink :href="route('admin.posts.create')" :active="route().current('admin.posts.create')" :collapsed="false" label="Add New Post">Add New Post</NavLink>
                        <NavLink :href="route('admin.posts.trash')" :active="route().current('admin.posts.trash')" :collapsed="false" label="Trash">Trash</NavLink>
                    </NavGroup>

                    <!-- News -->
                    <NavGroup label="News" :collapsed="sidebarCollapsed" :active="route().current('admin.news.*')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h10M4 18h10"/></svg>
                        </template>
                        <NavLink :href="route('admin.news.index')" :active="route().current('admin.news.index')" :collapsed="false" label="All News">All News</NavLink>
                        <NavLink :href="route('admin.news.create')" :active="route().current('admin.news.create')" :collapsed="false" label="Add New News">Add New News</NavLink>
                        <NavLink :href="route('admin.news.trash')" :active="route().current('admin.news.trash')" :collapsed="false" label="Trash">Trash</NavLink>
                    </NavGroup>

                    <!-- Products -->
                    <NavGroup label="Products" :collapsed="sidebarCollapsed" :active="route().current('admin.products.*')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7H4l1.5 12A2 2 0 007.5 21h9a2 2 0 002-1.8L20 7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7l.5-2A2 2 0 0111.5 3h1a2 2 0 011.9 2L15 7"/></svg>
                        </template>
                        <NavLink :href="route('admin.products.index')" :active="route().current('admin.products.index')" :collapsed="false" label="All Products">All Products</NavLink>
                        <NavLink :href="route('admin.products.create')" :active="route().current('admin.products.create')" :collapsed="false" label="Add New Product">Add New Product</NavLink>
                        <NavLink :href="route('admin.products.trash')" :active="route().current('admin.products.trash')" :collapsed="false" label="Trash">Trash</NavLink>
                    </NavGroup>

                    <NavLink href="#" :collapsed="sidebarCollapsed" label="Media">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4 4 4 4-4 4 4"/><circle cx="6" cy="8" r="2" stroke-width="1.5"/></svg>
                        </template>
                        Media
                    </NavLink>
                    <NavLink :href="route('admin.menus.index')" :active="route().current('admin.menus.*')" :collapsed="sidebarCollapsed" label="Menus">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </template>
                        Menus
                    </NavLink>
                    <NavLink :href="route('admin.themes.index')" :active="route().current('admin.themes.*')" :collapsed="sidebarCollapsed" label="Themes">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4h16v8H4zM10 12v8M14 12v8"/></svg>
                        </template>
                        Themes
                    </NavLink>
                    <!-- Users -->
                    <NavGroup label="Users" :collapsed="sidebarCollapsed" :active="route().current('admin.users.*') || route().current('admin.roles.*')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="3" stroke-width="1.5"/></svg>
                        </template>
                        <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.index')" :collapsed="false" label="All Users">All Users</NavLink>
                        <NavLink :href="route('admin.users.create')" :active="route().current('admin.users.create')" :collapsed="false" label="Add New User">Add New User</NavLink>
                        <NavLink :href="route('admin.roles.index')" :active="route().current('admin.roles.*')" :collapsed="false" label="Roles">Roles</NavLink>
                    </NavGroup>
                    <NavLink :href="route('profile.edit')" :collapsed="sidebarCollapsed" label="Profile">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14a7 7 0 100-14 7 7 0 000 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804"/></svg>
                        </template>
                        Profile
                    </NavLink>
                    <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-3 w-full text-left px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100" :title="sidebarCollapsed ? 'Log Out' : undefined">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4h6a2 2 0 012 2v12a2 2 0 01-2 2H7"/></svg>
                        <span :class="[sidebarCollapsed ? 'opacity-0 w-0 inline-block overflow-hidden' : 'inline-block']">Log Out</span>
                    </Link>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Page Heading -->
                <header class="bg-white shadow" v-if="$slots.header">
                    <div class="px-4 py-6 sm:px-6 lg:px-8">
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
