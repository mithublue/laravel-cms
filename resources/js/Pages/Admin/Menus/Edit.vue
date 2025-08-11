<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MenuItemNode from './Partials/MenuItemNode.vue';

const props = defineProps({
  menu: Object,
  sources: Object,
});

// Deep clone initial tree from props to avoid mutating readonly/Proxy props and avoid DataCloneError
const tree = ref(JSON.parse(JSON.stringify(props.menu.tree || [])));

// Custom link form
const custom = ref({ title: '', url: '' });

function addCustomLink() {
  if (!custom.value.title) return;
  tree.value.push({
    id: null,
    title: custom.value.title,
    url: custom.value.url || '#',
    target: null,
    classes: null,
    rel: null,
    linkable_type: null,
    linkable_id: null,
    meta: {},
    children: [],
  });
  custom.value = { title: '', url: '' };
}

function addFromSource(item) {
  tree.value.push({
    id: null,
    title: item.title,
    url: item.url,
    target: null,
    classes: null,
    rel: null,
    linkable_type: null,
    linkable_id: item.id,
    meta: {},
    children: [],
  });
}

function removeAt(path) {
  // path is array of indexes
  if (!path.length) return;
  const last = path[path.length - 1];
  const parent = getParent(path);
  parent.splice(last, 1);
}

function getParent(path) {
  let arr = tree.value;
  for (let i = 0; i < path.length - 1; i++) {
    arr = arr[path[i]].children;
  }
  return arr;
}

function moveUp(path) {
  const parent = getParent(path);
  const idx = path[path.length - 1];
  if (idx <= 0) return;
  [parent[idx - 1], parent[idx]] = [parent[idx], parent[idx - 1]];
}

function moveDown(path) {
  const parent = getParent(path);
  const idx = path[path.length - 1];
  if (idx >= parent.length - 1) return;
  [parent[idx + 1], parent[idx]] = [parent[idx], parent[idx + 1]];
}

function indent(path) {
  const parent = getParent(path);
  const idx = path[path.length - 1];
  if (idx <= 0) return;
  const item = parent.splice(idx, 1)[0];
  const prev = parent[idx - 1];
  prev.children = prev.children || [];
  prev.children.push(item);
}

function outdent(path) {
  if (path.length === 1) return; // top level can't outdent
  const idx = path[path.length - 1];
  const parentPath = path.slice(0, -1);
  const grandParentPath = path.slice(0, -2);
  const parentArr = getArrayByPath(parentPath);
  const item = parentArr.splice(idx, 1)[0];
  const gpArr = getArrayByPath(grandParentPath);
  const parentIdx = parentPath[parentPath.length - 1];
  gpArr.splice(parentIdx + 1, 0, item);
}

function getArrayByPath(path) {
  let arr = tree.value;
  for (let i = 0; i < path.length; i++) {
    arr = arr[path[i]].children;
  }
  return arr;
}

function flatten(items) {
  return items.map((it) => ({
    id: it.id,
    title: it.title,
    url: it.url,
    target: it.target,
    classes: it.classes,
    rel: it.rel,
    linkable_type: it.linkable_type,
    linkable_id: it.linkable_id,
    meta: it.meta || {},
    children: flatten(it.children || []),
  }));
}

const details = ref(null);

function selectItem(item) {
  // Bind a shallow proxy for editing inputs
  details.value = item;
}

const saving = ref(false);
function saveMenu() {
  saving.value = true;
  const payload = { items: flatten(tree.value) };
  const form = useForm(payload);
  form.post(route('admin.menus.sync-items', props.menu.id), {
    preserveScroll: true,
    onFinish: () => (saving.value = false),
  });
}
</script>

<template>
  <Head :title="`Edit Menu: ${menu.name}`" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Menu: {{ menu.name }}</h2>
        <div class="flex items-center gap-3">
          <Link :href="route('admin.menus.index')" class="text-sm text-gray-600 hover:text-gray-900">Back to list</Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Left: Sources (Custom Links + Entities) -->
          <div class="space-y-6 lg:col-span-1">
            <div class="bg-white p-4 shadow sm:rounded-lg space-y-3">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Custom Links</h3>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">URL</label>
                <input v-model="custom.url" type="text" placeholder="https://example.com or /about" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Link Text</label>
                <input v-model="custom.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
              <div>
                <button type="button" @click="addCustomLink" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add to Menu</button>
              </div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg space-y-2" v-if="sources?.pages?.length">
              <h3 class="text-sm font-semibold text-gray-700">Pages</h3>
              <ul class="max-h-48 overflow-auto divide-y divide-gray-100">
                <li v-for="p in sources.pages" :key="'page-'+p.id" class="flex items-center justify-between py-1 text-sm">
                  <span class="truncate pr-2">{{ p.title }}</span>
                  <button type="button" @click="addFromSource(p)" class="rounded bg-gray-100 px-2 py-1 text-xs hover:bg-gray-200">Add</button>
                </li>
              </ul>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg space-y-2" v-if="sources?.posts?.length">
              <h3 class="text-sm font-semibold text-gray-700">Posts</h3>
              <ul class="max-h-48 overflow-auto divide-y divide-gray-100">
                <li v-for="p in sources.posts" :key="'post-'+p.id" class="flex items-center justify-between py-1 text-sm">
                  <span class="truncate pr-2">{{ p.title }}</span>
                  <button type="button" @click="addFromSource(p)" class="rounded bg-gray-100 px-2 py-1 text-xs hover:bg-gray-200">Add</button>
                </li>
              </ul>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg space-y-2" v-if="sources?.news?.length">
              <h3 class="text-sm font-semibold text-gray-700">News</h3>
              <ul class="max-h-48 overflow-auto divide-y divide-gray-100">
                <li v-for="n in sources.news" :key="'news-'+n.id" class="flex items-center justify-between py-1 text-sm">
                  <span class="truncate pr-2">{{ n.title }}</span>
                  <button type="button" @click="addFromSource(n)" class="rounded bg-gray-100 px-2 py-1 text-xs hover:bg-gray-200">Add</button>
                </li>
              </ul>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg space-y-2" v-if="sources?.products?.length">
              <h3 class="text-sm font-semibold text-gray-700">Products</h3>
              <ul class="max-h-48 overflow-auto divide-y divide-gray-100">
                <li v-for="p in sources.products" :key="'product-'+p.id" class="flex items-center justify-between py-1 text-sm">
                  <span class="truncate pr-2">{{ p.title }}</span>
                  <button type="button" @click="addFromSource(p)" class="rounded bg-gray-100 px-2 py-1 text-xs hover:bg-gray-200">Add</button>
                </li>
              </ul>
            </div>
          </div>

          <!-- Right: Menu Structure -->
          <div class="lg:col-span-2 space-y-4">
            <div class="bg-white p-4 shadow sm:rounded-lg">
              <h3 class="mb-3 text-sm font-semibold text-gray-700">Menu Structure</h3>
              <div class="space-y-2">
                <MenuItemNode
                  v-for="(item, i) in tree"
                  :key="i + '-' + (item.id ?? 'new')"
                  :item="item"
                  :path="[i]"
                  @remove="removeAt"
                  @up="moveUp"
                  @down="moveDown"
                  @indent="indent"
                  @outdent="outdent"
                  @select="selectItem"
                />
                <div v-if="!tree.length" class="text-sm text-gray-500">No items. Add links from the left panel.</div>
              </div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg flex items-center gap-3">
              <button type="button" @click="saveMenu" :disabled="saving" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">Save Menu</button>
              <span v-if="saving" class="text-sm text-gray-600">Saving...</span>
            </div>

            <!-- Details panel moved here to appear right after the menu builder -->
            <div v-if="details" class="bg-white p-4 shadow sm:rounded-lg space-y-3">
              <h3 class="text-sm font-semibold text-gray-700">Menu Item Details</h3>
              <div>
                <label class="block text-sm font-medium text-gray-700">Navigation Label</label>
                <input v-model="details.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">URL</label>
                <input v-model="details.url" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Target</label>
                  <select v-model="details.target" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option :value="null">Default</option>
                    <option value="_self">Same tab</option>
                    <option value="_blank">New tab</option>
                  </select>
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">CSS Classes</label>
                  <input v-model="details.classes" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Rel</label>
                <input v-model="details.rel" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
export default { name: 'AdminMenusEdit' };
</script>
