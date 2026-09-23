import { Controller } from '@hotwired/stimulus';

/**
 * Editor del plan de cuotas de una venta. Muestra la seccion solo cuando el
 * pago es financiado, genera el plan contra el servidor, deja editar cada fila
 * y mantiene el total a la vista. Las filas viajan como JSON en un hidden.
 */
export default class extends Controller {
    static targets = ['section', 'editor', 'count', 'hidden', 'suma', 'diferencia', 'metodo', 'precio', 'fecha', 'moneda'];
    static values = { previewUrl: String, financiado: { type: String, default: 'Financiado' } };

    connect() {
        this.numero = 0;
        this.toggle();
    }

    toggle() {
        const esFinanciado = this.metodoTarget.value === this.financiadoValue;
        this.sectionTarget.classList.toggle('hidden', !esFinanciado);
        if (!esFinanciado) {
            this.editorTarget.innerHTML = '';
            this.hiddenTarget.value = '';
            this.numero = 0;
        }
        this.recalcular();
    }

    async generar() {
        const cantidad = parseInt(this.countTarget.value) || 0;
        const precio = parseFloat(this.precioTarget.value) || 0;
        const fecha = this.fechaTarget.value || new Date().toISOString().slice(0, 10);

        if (cantidad <= 0 || precio <= 0) return;

        const url = `${this.previewUrlValue}?count=${cantidad}&price=${precio}&date=${fecha}`;
        const cuotas = await (await fetch(url)).json();

        this.editorTarget.innerHTML = '';
        this.numero = 0;
        cuotas.forEach(cuota => this.#agregarFila(cuota.amount, cuota.dueDate));
        this.recalcular();
    }

    agregar() {
        this.#agregarFila('', '');
        this.recalcular();
    }

    quitar(event) {
        event.currentTarget.closest('[data-fila]').remove();
        this.#renumerar();
        this.recalcular();
    }

    recalcular() {
        const filas = [...this.editorTarget.querySelectorAll('[data-fila]')];
        const datos = filas.map(fila => ({
            amount: fila.querySelector('[data-monto]').value,
            dueDate: fila.querySelector('[data-vencimiento]').value,
        }));

        this.hiddenTarget.value = datos.length ? JSON.stringify(datos) : '';

        const suma = datos.reduce((total, cuota) => total + (parseFloat(cuota.amount) || 0), 0);
        const precio = parseFloat(this.precioTarget.value) || 0;
        const diferencia = precio - suma;
        const moneda = this.monedaTarget.value === 'USD' ? 'USD' : '$';

        this.sumaTarget.textContent = `${moneda} ${formatear(suma)}`;

        if (datos.length === 0) {
            this.diferenciaTarget.textContent = '';
            return;
        }
        if (Math.abs(diferencia) < 0.01) {
            this.diferenciaTarget.textContent = 'Coincide con el precio de venta';
            this.diferenciaTarget.className = 'text-xs font-medium text-emerald-600 dark:text-emerald-400';
        } else {
            const signo = diferencia > 0 ? 'Faltan' : 'Sobran';
            this.diferenciaTarget.textContent = `${signo} ${moneda} ${formatear(Math.abs(diferencia))} respecto del precio`;
            this.diferenciaTarget.className = 'text-xs font-medium text-amber-600 dark:text-amber-400';
        }
    }

    #agregarFila(monto, vencimiento) {
        this.numero++;
        const fila = document.createElement('div');
        fila.dataset.fila = '';
        fila.className = 'flex items-center gap-2';
        fila.innerHTML = `
            <span data-numero class="w-8 shrink-0 text-center text-xs font-medium text-muted">${this.numero}</span>
            <input type="number" step="0.01" data-monto value="${monto}" placeholder="Monto"
                   data-action="input->installments#recalcular" class="input flex-1" inputmode="decimal">
            <input type="date" data-vencimiento value="${vencimiento}"
                   data-action="input->installments#recalcular" class="input w-44">
            <button type="button" data-action="installments#quitar" class="btn-ghost btn-icon text-red-600 dark:text-red-400">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path d="M6 18 18 6M6 6l12 12"/></svg>
            </button>`;
        this.editorTarget.append(fila);
    }

    #renumerar() {
        this.editorTarget.querySelectorAll('[data-numero]').forEach((span, i) => {
            span.textContent = i + 1;
        });
        this.numero = this.editorTarget.querySelectorAll('[data-fila]').length;
    }
}

function formatear(valor) {
    return valor.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
