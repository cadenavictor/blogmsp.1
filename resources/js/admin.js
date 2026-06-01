import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import Placeholder from '@tiptap/extension-placeholder'

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''

async function uploadImage(file) {
    const body = new FormData()
    body.append('file', file)

    const response = await fetch('/admin/media', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body,
    })

    if (!response.ok) {
        throw new Error('Falha no upload da imagem.')
    }

    const data = await response.json()
    return data.url
}

/* ------------------------------------------------------------------ Editor */

const TOOLBAR = [
    { name: 'bold', label: 'B', title: 'Negrito', run: (e) => e.chain().focus().toggleBold().run(), active: 'bold' },
    { name: 'italic', label: 'I', title: 'Italico', run: (e) => e.chain().focus().toggleItalic().run(), active: 'italic' },
    { name: 'strike', label: 'S', title: 'Tachado', run: (e) => e.chain().focus().toggleStrike().run(), active: 'strike' },
    { sep: true },
    { name: 'h2', label: 'H2', title: 'Titulo 2', run: (e) => e.chain().focus().toggleHeading({ level: 2 }).run(), active: ['heading', { level: 2 }] },
    { name: 'h3', label: 'H3', title: 'Titulo 3', run: (e) => e.chain().focus().toggleHeading({ level: 3 }).run(), active: ['heading', { level: 3 }] },
    { sep: true },
    { name: 'ul', label: '• Lista', title: 'Lista', run: (e) => e.chain().focus().toggleBulletList().run(), active: 'bulletList' },
    { name: 'ol', label: '1. Lista', title: 'Lista numerada', run: (e) => e.chain().focus().toggleOrderedList().run(), active: 'orderedList' },
    { name: 'quote', label: '❝', title: 'Citacao', run: (e) => e.chain().focus().toggleBlockquote().run(), active: 'blockquote' },
    { name: 'code', label: '</>', title: 'Bloco de codigo', run: (e) => e.chain().focus().toggleCodeBlock().run(), active: 'codeBlock' },
    { sep: true },
    { name: 'link', label: '🔗', title: 'Link', run: setLink },
    { name: 'image', label: '🖼', title: 'Imagem', run: insertImage },
    { sep: true },
    { name: 'undo', label: '↶', title: 'Desfazer', run: (e) => e.chain().focus().undo().run() },
    { name: 'redo', label: '↷', title: 'Refazer', run: (e) => e.chain().focus().redo().run() },
]

function setLink(editor) {
    const previous = editor.getAttributes('link').href
    const url = window.prompt('URL do link (vazio para remover):', previous ?? '')

    if (url === null) return

    if (url === '') {
        editor.chain().focus().extendMarkRange('link').unsetLink().run()
        return
    }

    editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

function insertImage(editor) {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/*'
    input.addEventListener('change', async () => {
        const file = input.files?.[0]
        if (!file) return
        try {
            const url = await uploadImage(file)
            editor.chain().focus().setImage({ src: url }).run()
        } catch (error) {
            window.alert(error.message)
        }
    })
    input.click()
}

function buildToolbar(editor) {
    const bar = document.createElement('div')
    bar.className = 'editor-toolbar'
    const buttons = []

    TOOLBAR.forEach((item) => {
        if (item.sep) {
            const sep = document.createElement('span')
            sep.className = 'sep'
            bar.appendChild(sep)
            return
        }

        const button = document.createElement('button')
        button.type = 'button'
        button.textContent = item.label
        button.title = item.title
        button.addEventListener('click', () => item.run(editor))
        bar.appendChild(button)

        if (item.active) {
            buttons.push({ button, active: item.active })
        }
    })

    const refresh = () => {
        buttons.forEach(({ button, active }) => {
            const on = Array.isArray(active) ? editor.isActive(...active) : editor.isActive(active)
            button.classList.toggle('is-active', on)
        })
    }

    editor.on('selectionUpdate', refresh)
    editor.on('transaction', refresh)

    return bar
}

function initEditor(wrap) {
    const source = wrap.querySelector('[data-editor-source]')
    if (!source) return

    source.hidden = true
    // A hidden `required` field is not focusable and blocks submit; the server
    // still validates `content`, so drop client-side required once enhanced.
    source.required = false

    const surface = document.createElement('div')
    surface.className = 'editor'

    const mount = document.createElement('div')

    const editor = new Editor({
        element: mount,
        extensions: [
            StarterKit,
            Link.configure({ openOnClick: false, autolink: true }),
            Image,
            Placeholder.configure({ placeholder: source.getAttribute('data-placeholder') || 'Escreva o conteudo...' }),
        ],
        content: source.value || '',
        onUpdate: ({ editor }) => {
            source.value = editor.isEmpty ? '' : editor.getHTML()
        },
    })

    surface.appendChild(buildToolbar(editor))
    surface.appendChild(mount)
    wrap.appendChild(surface)

    source.form?.addEventListener('submit', () => {
        source.value = editor.isEmpty ? '' : editor.getHTML()
    })
}

/* ------------------------------------------------------------- Cover upload */

function initUploader(uploader) {
    const input = uploader.querySelector('input[type="file"]')
    const hidden = uploader.querySelector('[data-uploader-value]')
    const preview = uploader.querySelector('[data-uploader-preview]')
    const clear = uploader.querySelector('[data-uploader-clear]')

    const render = (url) => {
        if (!preview) return
        preview.innerHTML = url
            ? `<img src="${url}" alt="Pre-visualizacao da capa">`
            : '<span class="placeholder">Nenhuma imagem selecionada</span>'
    }

    input?.addEventListener('change', async () => {
        const file = input.files?.[0]
        if (!file) return
        try {
            const url = await uploadImage(file)
            if (hidden) hidden.value = url
            render(url)
        } catch (error) {
            window.alert(error.message)
        }
    })

    clear?.addEventListener('click', () => {
        if (hidden) hidden.value = ''
        if (input) input.value = ''
        render('')
    })

    // Reflect a manually typed/pasted URL in the preview.
    hidden?.addEventListener('input', () => render(hidden.value.trim()))
}

/* ------------------------------------------------------------------- Slug */

function slugify(value) {
    return value
        .toString()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
}

function initSlug() {
    const source = document.querySelector('[data-slug-source]')
    const target = document.querySelector('[data-slug-target]')
    if (!source || !target) return

    let locked = target.value.trim() !== ''
    target.addEventListener('input', () => { locked = true })
    source.addEventListener('input', () => {
        if (!locked) target.value = slugify(source.value)
    })
}

/* --------------------------------------------------------------- Behaviors */

function initConfirms() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault()
            }
        })
    })
}

function initCopy() {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.querySelector(button.getAttribute('data-copy'))
            if (!target) return
            try {
                await navigator.clipboard.writeText(target.innerText)
                const original = button.textContent
                button.textContent = 'Copiado!'
                setTimeout(() => { button.textContent = original }, 1500)
            } catch (error) {
                window.alert('Nao foi possivel copiar.')
            }
        })
    })
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-editor]').forEach(initEditor)
    document.querySelectorAll('[data-uploader]').forEach(initUploader)
    initSlug()
    initConfirms()
    initCopy()
})
