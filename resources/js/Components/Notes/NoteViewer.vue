<script setup>
import { onMounted, onUpdated, nextTick } from 'vue'
import hljs from 'highlight.js'

defineProps({
    contenido: String,
})

function highlight() {
    nextTick(() => {
        document.querySelectorAll('.note-content pre code').forEach((block) => {
            if (!block.dataset.highlighted) {
                hljs.highlightElement(block)
            }
        })
    })
}

onMounted(highlight)
onUpdated(highlight)
</script>

<template>
    <div
        class="note-content prose prose-invert prose-sm max-w-none
               prose-headings:text-slate-100 prose-headings:font-semibold
               prose-p:text-slate-300 prose-p:leading-relaxed
               prose-a:text-violet-400 prose-a:no-underline hover:prose-a:underline
               prose-strong:text-slate-100
               prose-code:text-violet-300 prose-code:bg-surface-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-xs prose-code:font-mono prose-code:before:content-none prose-code:after:content-none
               prose-pre:bg-surface-950 prose-pre:border prose-pre:border-slate-700/60 prose-pre:rounded-lg prose-pre:p-0
               prose-blockquote:border-l-violet-500/50 prose-blockquote:text-slate-400 prose-blockquote:not-italic
               prose-ul:text-slate-300 prose-ol:text-slate-300
               prose-hr:border-slate-700"
        v-html="contenido"
    />
</template>

<style>
/* highlight.js theme override for dark surface */
.note-content pre code.hljs {
    background: transparent;
    padding: 1rem 1.25rem;
    display: block;
    overflow-x: auto;
    font-size: 0.8rem;
    line-height: 1.6;
}
</style>
