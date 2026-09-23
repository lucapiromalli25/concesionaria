import { Controller } from '@hotwired/stimulus';

/** Confirmacion para acciones destructivas que se envian por POST con token CSRF. */
export default class extends Controller {
    static targets = ['dialog', 'message', 'submit'];

    ask(event) {
        const boton = event.currentTarget;
        this.url = boton.dataset.confirmUrl;
        this.token = boton.dataset.confirmToken;
        this.messageTarget.textContent = boton.dataset.confirmMessage || 'Esta accion no se puede deshacer.';
        this.submitTarget.textContent = boton.dataset.confirmLabel || 'Confirmar';
        this.dialogTarget.showModal();
    }

    close() {
        this.dialogTarget.close();
    }

    async confirm() {
        this.submitTarget.disabled = true;
        try {
            const respuesta = await fetch(this.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ token: this.token }),
            });
            const data = await respuesta.json();

            if (data.status === 'success') {
                window.location.href = data.redirect || window.location.href;
                if (!data.redirect) window.location.reload();
                return;
            }
            this.messageTarget.textContent = data.message || 'No se pudo completar la accion.';
        } catch {
            this.messageTarget.textContent = 'Error de conexion.';
        } finally {
            this.submitTarget.disabled = false;
        }
    }
}
