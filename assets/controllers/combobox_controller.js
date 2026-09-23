import { Controller } from '@hotwired/stimulus';

/**
 * Selector con busqueda contra el servidor. Guarda el id en un input hidden y
 * muestra la etiqueta elegida. Si se define createLabel, ofrece un boton al pie
 * que emite "combobox:create" para que la pagina abra su alta rapida.
 */
export default class extends Controller {
    static targets = ['input'];
    static values = {
        url: String,
        placeholder: { type: String, default: 'Buscar...' },
        label: { type: String, default: '' },
        createLabel: { type: String, default: '' },
        minChars: { type: Number, default: 0 },
    };

    connect() {
        this.abierto = false;
        this.activo = -1;
        this.opciones = [];
        this.#build();
        this.#renderLabel();
        this.onDocumentClick = (e) => { if (!this.element.contains(e.target)) this.close(); };
        document.addEventListener('click', this.onDocumentClick);
    }

    disconnect() {
        document.removeEventListener('click', this.onDocumentClick);
        clearTimeout(this.timeout);
    }

    #build() {
        this.element.classList.add('relative');

        this.button = document.createElement('button');
        this.button.type = 'button';
        this.button.className = 'input flex items-center justify-between gap-2 text-left';
        this.button.innerHTML = `<span data-label class="truncate"></span>
            <svg class="size-4 shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>`;
        this.button.addEventListener('click', () => this.toggle());

        this.panel = document.createElement('div');
        this.panel.className = 'popover absolute z-40 mt-1 hidden w-full min-w-72';
        this.panel.innerHTML = `
            <div class="border-b border-line p-2">
                <input type="text" data-search class="input py-1.5 text-sm" placeholder="Escribi para buscar..." autocomplete="off">
            </div>
            <ul data-list class="max-h-64 overflow-y-auto py-1"></ul>`;

        this.element.append(this.button, this.panel);

        this.labelEl = this.button.querySelector('[data-label]');
        this.searchEl = this.panel.querySelector('[data-search]');
        this.listEl = this.panel.querySelector('[data-list]');

        this.searchEl.addEventListener('input', () => this.#buscarConDebounce());
        this.searchEl.addEventListener('keydown', (e) => this.#teclado(e));

        if (this.createLabelValue) {
            this.createBtn = document.createElement('button');
            this.createBtn.type = 'button';
            this.createBtn.className = 'flex w-full items-center gap-2 border-t border-line px-3 py-2 text-sm font-medium text-accent-text hover:bg-surface-2';
            this.createBtn.innerHTML = `<svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path d="M12 4.5v15m7.5-7.5h-15"/></svg><span></span>`;
            this.createBtn.querySelector('span').textContent = this.createLabelValue;
            this.createBtn.addEventListener('click', () => {
                const q = this.searchEl.value;
                this.close();
                this.dispatch('create', { detail: { query: q } });
            });
            this.panel.append(this.createBtn);
        }
    }

    #renderLabel() {
        const tiene = this.labelValue !== '';
        this.labelEl.textContent = tiene ? this.labelValue : this.placeholderValue;
        this.labelEl.classList.toggle('text-subtle', !tiene);
    }

    #buscarConDebounce() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.#buscar(), 250);
    }

    async #buscar() {
        const q = this.searchEl.value.trim();
        if (q.length < this.minCharsValue) return;

        this.listEl.innerHTML = `<li class="px-3 py-6 text-center text-xs text-subtle">Buscando...</li>`;
        try {
            const respuesta = await fetch(`${this.urlValue}?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json' },
            });
            this.opciones = await respuesta.json();
        } catch {
            this.opciones = [];
        }
        this.activo = -1;
        this.#renderOpciones();
    }

    #renderOpciones() {
        this.listEl.innerHTML = '';

        if (this.opciones.length === 0) {
            const vacio = document.createElement('li');
            vacio.className = 'px-3 py-6 text-center text-xs text-subtle';
            vacio.textContent = this.searchEl.value.trim() ? 'Sin resultados' : 'Escribi para buscar';
            this.listEl.append(vacio);
            return;
        }

        this.opciones.forEach((opcion, i) => {
            const li = document.createElement('li');
            li.className = 'popover-item flex flex-col';
            li.dataset.active = String(i === this.activo);
            li.innerHTML = `<span>${escapar(opcion.text)}</span>` +
                (opcion.hint ? `<span class="text-xs text-subtle">${escapar(opcion.hint)}</span>` : '');
            li.addEventListener('click', () => this.#elegir(opcion));
            this.listEl.append(li);
        });
    }

    #teclado(e) {
        if (e.key === 'Escape') { e.preventDefault(); this.close(); this.button.focus(); return; }
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const delta = e.key === 'ArrowDown' ? 1 : -1;
            this.activo = Math.max(0, Math.min(this.opciones.length - 1, this.activo + delta));
            this.#renderOpciones();
            this.listEl.children[this.activo]?.scrollIntoView({ block: 'nearest' });
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            const opcion = this.opciones[this.activo >= 0 ? this.activo : 0];
            if (opcion) this.#elegir(opcion);
        }
    }

    #elegir(opcion) {
        this.inputTarget.value = opcion.id;
        this.labelValue = opcion.text;
        this.#renderLabel();
        this.inputTarget.dispatchEvent(new Event('change', { bubbles: true }));
        this.close();
        this.button.focus();
    }

    /** La usa quick_create_controller despues de dar de alta algo nuevo. */
    seleccionar(id, text) {
        this.#elegir({ id, text });
    }

    toggle() { this.abierto ? this.close() : this.open(); }

    open() {
        this.abierto = true;
        this.panel.classList.remove('hidden');
        this.searchEl.focus();
        if (this.opciones.length === 0) this.#buscar();
    }

    close() {
        this.abierto = false;
        this.panel.classList.add('hidden');
    }
}

function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
