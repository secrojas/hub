<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { createLowlight, common } from 'lowlight'
import { watch } from 'vue'

const props = defineProps({
    modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const lowlight = createLowlight(common)

const editor = useEditor({
    content: props.modelValue ?? '',
    extensions: [
        StarterKit.configure({ codeBlock: false }),
        CodeBlockLowlight.configure({ lowlight }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-invert prose-sm max-w-none focus:outline-none min-h-[400px] px-5 py-4 text-slate-300',
        },
    },
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (val) => {
    if (editor.value && val !== editor.value.getHTML()) {
        editor.value.commands.setContent(val ?? '', false)
    }
})
</script>

<template>
    <div class="flex flex-col border border-slate-700 rounded-lg overflow-hidden bg-surface-950">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-0.5 px-3 py-2 bg-surface-800 border-b border-slate-700">
            <!-- Text style -->
            <ToolbarBtn @click="editor.chain().focus().toggleBold().run()" :active="editor?.isActive('bold')" title="Negrita">
                <strong>B</strong>
            </ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleItalic().run()" :active="editor?.isActive('italic')" title="Itálica">
                <em>I</em>
            </ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleStrike().run()" :active="editor?.isActive('strike')" title="Tachado">
                <s>S</s>
            </ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleCode().run()" :active="editor?.isActive('code')" title="Código inline">
                <span class="font-mono text-xs">&lt;/&gt;</span>
            </ToolbarBtn>

            <div class="w-px h-5 bg-slate-700 mx-1" />

            <!-- Headings -->
            <ToolbarBtn @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :active="editor?.isActive('heading', { level: 1 })" title="H1">H1</ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :active="editor?.isActive('heading', { level: 2 })" title="H2">H2</ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :active="editor?.isActive('heading', { level: 3 })" title="H3">H3</ToolbarBtn>

            <div class="w-px h-5 bg-slate-700 mx-1" />

            <!-- Lists -->
            <ToolbarBtn @click="editor.chain().focus().toggleBulletList().run()" :active="editor?.isActive('bulletList')" title="Lista">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleOrderedList().run()" :active="editor?.isActive('orderedList')" title="Lista numerada">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                </svg>
            </ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().toggleBlockquote().run()" :active="editor?.isActive('blockquote')" title="Cita">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1zm12 0c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
                </svg>
            </ToolbarBtn>

            <div class="w-px h-5 bg-slate-700 mx-1" />

            <!-- Code block -->
            <ToolbarBtn @click="editor.chain().focus().toggleCodeBlock().run()" :active="editor?.isActive('codeBlock')" title="Bloque de código">
                <span class="font-mono text-xs">{ }</span>
            </ToolbarBtn>

            <div class="w-px h-5 bg-slate-700 mx-1" />

            <!-- Undo / Redo -->
            <ToolbarBtn @click="editor.chain().focus().undo().run()" title="Deshacer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 010 16v-4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l4-4-4-4"/>
                </svg>
            </ToolbarBtn>
            <ToolbarBtn @click="editor.chain().focus().redo().run()" title="Rehacer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 000 16v-4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10l-4-4 4-4"/>
                </svg>
            </ToolbarBtn>
        </div>

        <!-- Editor area -->
        <EditorContent :editor="editor" />
    </div>
</template>

<!-- Inline component to avoid creating a separate file -->
<script>
const ToolbarBtn = {
    props: { active: Boolean },
    template: `
        <button
            type="button"
            class="px-2 py-1 rounded text-xs transition-colors"
            :class="active
                ? 'bg-violet-600/20 text-violet-400'
                : 'text-slate-400 hover:bg-slate-700 hover:text-slate-100'"
            v-bind="$attrs"
        ><slot /></button>
    `,
}
export default { components: { ToolbarBtn } }
</script>

<style>
/* tiptap code block syntax highlighting */
.ProseMirror pre {
    background: theme('colors.surface.950');
    border: 1px solid theme('colors.slate.700 / 60%');
    border-radius: 0.5rem;
    padding: 0;
    overflow: hidden;
}

.ProseMirror pre code.hljs {
    background: transparent;
    padding: 1rem 1.25rem;
    display: block;
    overflow-x: auto;
    font-size: 0.8rem;
    line-height: 1.6;
}

/* selected code block */
.ProseMirror pre.is-selected {
    outline: 2px solid theme('colors.violet.500');
}
</style>
