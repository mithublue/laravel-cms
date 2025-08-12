<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import TomSelect from 'tom-select';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, default: () => [] }, // strings or { value, label }
  placeholder: { type: String, default: 'Select options' },
  disabled: { type: Boolean, default: false },
  id: { type: String, default: '' },
  name: { type: String, default: '' },
  class: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const selectEl = ref(null);
let ts = null;

const normalizedOptions = () => props.options.map(o => (
  typeof o === 'string' ? { value: o, label: o } : o
));

onMounted(() => {
  if (!selectEl.value) return;
  ts = new TomSelect(selectEl.value, {
    options: normalizedOptions(),
    items: props.modelValue,
    placeholder: props.placeholder,
    plugins: ['remove_button'],
    valueField: 'value',
    labelField: 'label',
    searchField: ['label', 'value'],
    maxOptions: 1000,
    persist: false,
    create: false,
    onChange(values) {
      emit('update:modelValue', Array.isArray(values) ? values : [values]);
      emit('change', values);
    },
  });
  ts.setTextboxValue('');
  if (props.disabled) ts.disable();
});

onBeforeUnmount(() => {
  if (ts) {
    ts.destroy();
    ts = null;
  }
});

watch(() => props.modelValue, (val) => {
  if (ts) {
    const next = Array.isArray(val) ? val : [];
    const curr = ts.getValue();
    const same = Array.isArray(curr) && curr.length === next.length && curr.every((v, i) => v === next[i]);
    if (!same) ts.setValue(next, true);
  }
});

watch(() => props.disabled, (dis) => {
  if (!ts) return;
  dis ? ts.disable() : ts.enable();
});
</script>

<template>
  <select
    ref="selectEl"
    multiple
    :id="id"
    :name="name || undefined"
    :class="class"
  >
    <option v-for="opt in normalizedOptions()" :key="opt.value" :value="opt.value">
      {{ opt.label }}
    </option>
  </select>
</template>
