import { Controller } from '@hotwired/stimulus';

/**
 * Envia el formulario de filtros solo (debounce en texto, inmediato en selects)
 * y resetea la paginacion en cada cambio.
 */
export default class extends Controller {
    static values = { delay: { type: Number, default: 350 } };

    connect() {
        this.timeout = null;
    }

    disconnect() {
        clearTimeout(this.timeout);
    }

    submit() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.#send(), this.delayValue);
    }

    submitNow() {
        clearTimeout(this.timeout);
        this.#send();
    }

    #send() {
        const page = this.element.querySelector('input[name="page"]');
        if (page) page.value = 1;

        // Turbo Drive esta desactivado, asi que sincronizamos la URL a mano
        // para que el filtro sobreviva a un refresh o se pueda compartir.
        const params = new URLSearchParams(new FormData(this.element));
        [...params.entries()].forEach(([k, v]) => { if (v === '') params.delete(k); });
        history.replaceState({}, '', `${this.element.action}?${params}`);

        this.element.requestSubmit();
    }
}
