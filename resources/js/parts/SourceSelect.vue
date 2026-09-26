<template>
    <div class="rss-picker" ref="root">
        <button
            ref="trigger"
            type="button"
            class="rss-picker-trigger"
            :disabled="disabled || !categories.length"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :aria-label="__('All sources')"
            @click="toggle"
            @keydown.down.prevent="openPanel(0)"
            @keydown.up.prevent="openPanel(-1)"
        >
            <span class="rss-picker-value">{{ currentTitle }}</span>
            <svg
                class="rss-picker-caret"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <!-- Teleported: the card clips its own overflow, a panel inside it
             would be cut off at the first item. -->
        <Teleport to="body">
            <div
                v-if="open"
                ref="panel"
                class="nova-tw rss-picker-panel"
                :style="panelStyle"
                role="listbox"
                :aria-activedescendant="activeId"
                @keydown.esc.prevent="close"
                @keydown.down.prevent="move(1)"
                @keydown.up.prevent="move(-1)"
                @keydown.home.prevent="moveTo(0)"
                @keydown.end.prevent="moveTo(flat.length - 1)"
                @keydown.enter.prevent="choose(flat[active])"
                @keydown.tab="close"
            >
                <div class="rss-picker-filter">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <input
                        ref="search"
                        v-model="term"
                        type="search"
                        :placeholder="__('Search news…')"
                        :aria-label="__('Search news…')"
                        @keydown.down.prevent="move(1)"
                        @keydown.up.prevent="move(-1)"
                        @keydown.enter.prevent="choose(flat[active])"
                        @keydown.esc.prevent="close"
                    />
                    <span class="rss-picker-count">{{ flat.length }}</span>
                </div>

                <div class="rss-picker-scroll" ref="scroll">
                    <template v-for="group in groups" :key="group.key">
                        <p class="rss-picker-group">{{ group.label }}</p>
                        <button
                            v-for="source in group.sources"
                            :id="`rss-opt-${source.name}`"
                            :key="source.name"
                            type="button"
                            class="rss-picker-option"
                            :class="{
                                'is-active': flat[active]?.name === source.name,
                                'is-current': source.name === modelValue,
                            }"
                            role="option"
                            :aria-selected="source.name === modelValue"
                            @click="choose(source)"
                            @mousemove="active = flat.findIndex((s) => s.name === source.name)"
                        >
                            <span class="rss-picker-tick" aria-hidden="true">
                                <svg
                                    v-if="source.name === modelValue"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0l-3.5-3.5a1 1 0 111.4-1.4l2.8 2.79 6.8-6.79a1 1 0 011.4 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </span>
                            <span class="rss-picker-name">{{ source.title }}</span>
                        </button>
                    </template>

                    <p v-if="!flat.length" class="rss-picker-empty">
                        {{ __("No results") }}
                    </p>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from "vue";
import { __ } from "../composables/useTranslations";

const props = defineProps({
    modelValue: { type: String, default: "" },
    categories: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
    fallbackLabel: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue"]);

const root = ref(null);
const trigger = ref(null);
const panel = ref(null);
const scroll = ref(null);
const search = ref(null);

const open = ref(false);
const term = ref("");
const active = ref(0);
const panelStyle = ref({});

const matches = (source, group) =>
    `${source.title} ${group.label}`
        .toLowerCase()
        .includes(term.value.trim().toLowerCase());

const groups = computed(() =>
    props.categories
        .map((group) => ({
            ...group,
            sources: group.sources.filter((source) => matches(source, group)),
        }))
        .filter((group) => group.sources.length),
);

const flat = computed(() => groups.value.flatMap((group) => group.sources));

const currentTitle = computed(() => {
    const found = props.categories
        .flatMap((group) => group.sources)
        .find((source) => source.name === props.modelValue);

    return found?.title ?? props.fallbackLabel;
});

const activeId = computed(() =>
    flat.value[active.value] ? `rss-opt-${flat.value[active.value].name}` : null,
);

/** Anchor the panel to the trigger, flipping above it when space runs out. */
function place() {
    const box = trigger.value?.getBoundingClientRect();
    if (!box) return;

    const width = Math.max(box.width, 248);
    const below = innerHeight - box.bottom;
    const height = Math.min(360, Math.max(below, box.top) - 16);
    const flip = below < 240 && box.top > below;

    panelStyle.value = {
        position: "fixed",
        left: `${Math.min(box.left, innerWidth - width - 12)}px`,
        width: `${width}px`,
        maxHeight: `${height}px`,
        ...(flip
            ? { bottom: `${innerHeight - box.top + 6}px` }
            : { top: `${box.bottom + 6}px` }),
    };
}

function openPanel(index = 0) {
    if (props.disabled || !props.categories.length) return;

    open.value = true;
    term.value = "";
    place();

    nextTick(() => {
        const at = flat.value.findIndex((s) => s.name === props.modelValue);
        active.value = index === -1 ? flat.value.length - 1 : at > -1 ? at : 0;
        search.value?.focus();
        scrollToActive();
    });
}

function close() {
    open.value = false;
    term.value = "";
    trigger.value?.focus();
}

const toggle = () => (open.value ? close() : openPanel());

function move(delta) {
    if (!flat.value.length) return;
    active.value =
        (active.value + delta + flat.value.length) % flat.value.length;
    scrollToActive();
}

function moveTo(index) {
    active.value = Math.max(0, Math.min(index, flat.value.length - 1));
    scrollToActive();
}

function scrollToActive() {
    nextTick(() => {
        scroll.value
            ?.querySelector(".rss-picker-option.is-active")
            ?.scrollIntoView({ block: "nearest" });
    });
}

function choose(source) {
    if (!source) return;
    emit("update:modelValue", source.name);
    close();
}

function onDocumentPointer(event) {
    if (
        !root.value?.contains(event.target) &&
        !panel.value?.contains(event.target)
    ) {
        open.value = false;
        term.value = "";
    }
}

watch(open, (isOpen) => {
    const method = isOpen ? "addEventListener" : "removeEventListener";
    document[method]("pointerdown", onDocumentPointer, true);
    window[method]("resize", place, true);
    window[method]("scroll", place, true);
});

watch(term, () => {
    active.value = 0;
    place();
});

onUnmounted(() => {
    document.removeEventListener("pointerdown", onDocumentPointer, true);
    window.removeEventListener("resize", place, true);
    window.removeEventListener("scroll", place, true);
});
</script>
