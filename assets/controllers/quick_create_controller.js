import { Controller } from '@hotwired/stimulus';

/**
 * Alta rapida en un unico dialogo (sin encadenar modales).
 * Escucha el evento combobox:create, abre el <dialog> que corresponda,
 * postea el alta y agrega la opcion nueva al combobox que la pidio.
 */
export default class extends Controller {
    static targets = ['dialog'];

    open(event) {
        const kind = event.target.dataset.kind;
        this.combobox = event.target;
        this.dialog = this.dialogTargets.find(d => d.dataset.kind === kind);
        if (!this.dialog) return;

        const first = this.dialog.querySelector('input[name]');
        if (first && event.detail.query) first.value = event.detail.query;

        this.#error('');
        this.dialog.showModal();
        first?.focus();
    }

    close() {
        this.dialog?.close();
    }

    async submit(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const button = form.querySelector('button[type="submit"]');
        const payload = Object.fromEntries(new FormData(form).entries());

        button.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await response.json();

            if (!response.ok) {
                this.#error(data.error || 'No se pudo guardar.');
                return;
            }

            this.application
                .getControllerForElementAndIdentifier(this.combobox, 'combobox')
                .seleccionar(data.id, data.text);

            form.reset();
            this.close();
        } catch {
            this.#error('Error de conexion.');
        } finally {
            button.disabled = false;
        }
    }

    #error(message) {
        const box = this.dialog?.querySelector('[data-error]');
        if (!box) return;
        box.textContent = message;
        box.classList.toggle('hidden', !message);
    }
}
