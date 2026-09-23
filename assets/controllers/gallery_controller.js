import { Controller } from '@hotwired/stimulus';

/** Galeria simple: la miniatura clickeada pasa a ser la foto principal. */
export default class extends Controller {
    static targets = ['main'];

    select(event) {
        this.mainTarget.src = event.currentTarget.dataset.src;
    }
}
