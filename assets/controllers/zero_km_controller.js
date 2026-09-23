import { Controller } from '@hotwired/stimulus';

/** Marca 0 km: deshabilita la patente porque el vehiculo todavia no la tiene. */
export default class extends Controller {
    static targets = ['checkbox', 'plate'];

    connect() {
        this.checkboxTarget.checked = this.plateTarget.value.trim() === '';
        this.toggle();
    }

    toggle() {
        const is0km = this.checkboxTarget.checked;
        if (is0km) this.plateTarget.value = '';
        this.plateTarget.disabled = is0km;
        this.plateTarget.classList.toggle('opacity-60', is0km);
    }
}
