import { Controller } from '@hotwired/stimulus';

/** Modo claro / oscuro, recordado en localStorage. */
export default class extends Controller {
    static targets = ['icon'];

    connect() {
        this.render();
    }

    toggle() {
        const siguiente = (localStorage.getItem('tema') || 'light') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('tema', siguiente);
        aplicarTema(siguiente);
        this.render();
    }

    render() {
        const tema = localStorage.getItem('tema') || 'light';
        this.iconTargets.forEach(icon => {
            icon.classList.toggle('hidden', icon.dataset.tema !== tema);
        });
        this.element.title = tema === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
    }
}

export function aplicarTema(tema) {
    document.documentElement.classList.toggle('dark', tema === 'dark');
}
