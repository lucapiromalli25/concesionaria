import { Controller } from '@hotwired/stimulus';

/**
 * Trae un formulario del servidor, lo muestra en un <dialog> y lo envia por
 * fetch. Si el servidor responde HTML (formulario con errores) lo vuelve a
 * pintar dentro del dialogo; si responde JSON, cierra y recarga.
 */
export default class extends Controller {
    static targets = ['dialog', 'title', 'content'];

    async open(event) {
        const boton = event.currentTarget;
        this.titleTarget.textContent = boton.dataset.dialogTitle || '';
        this.contentTarget.innerHTML = '<p class="p-6 text-center text-sm text-muted">Cargando...</p>';
        this.dialogTarget.showModal();

        const respuesta = await fetch(boton.dataset.dialogUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        this.contentTarget.innerHTML = await respuesta.text();
        this.#escucharSubmit();
    }

    close() {
        this.dialogTarget.close();
    }

    #escucharSubmit() {
        const form = this.contentTarget.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const boton = form.querySelector('button[type="submit"]');
            boton.disabled = true;

            try {
                const respuesta = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                // Formulario clasico que termino en redirect: seguimos el redirect.
                if (respuesta.redirected) {
                    window.location.href = respuesta.url;
                    return;
                }

                const tipo = respuesta.headers.get('content-type') || '';

                if (tipo.includes('application/json')) {
                    const data = await respuesta.json();
                    if (data.status === 'success') {
                        if (data.cuota?.reciboUrl) window.open(data.cuota.reciboUrl, '_blank');
                        window.location.reload();
                        return;
                    }
                    this.#error(data.message || 'No se pudo guardar.');
                } else {
                    // El servidor devolvio el formulario con errores de validacion.
                    this.contentTarget.innerHTML = await respuesta.text();
                    this.#escucharSubmit();
                }
            } catch {
                this.#error('Error de conexion.');
            } finally {
                boton.disabled = false;
            }
        });
    }

    #error(mensaje) {
        const caja = document.createElement('p');
        caja.className = 'mx-5 mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 dark:bg-red-500/10 dark:text-red-300';
        caja.textContent = mensaje;
        this.contentTarget.prepend(caja);
    }
}
