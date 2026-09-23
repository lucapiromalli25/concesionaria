import { Controller } from '@hotwired/stimulus';

/** Marca o desmarca de una toda la columna de un rol en la matriz de permisos. */
export default class extends Controller {
    alternarColumna(event) {
        const rol = event.currentTarget.dataset.rol;
        const casillas = this.element.querySelectorAll(`input[type=checkbox][data-rol="${rol}"]:not([disabled])`);
        const marcarTodas = ![...casillas].every(casilla => casilla.checked);

        casillas.forEach(casilla => { casilla.checked = marcarTodas; });
    }
}
