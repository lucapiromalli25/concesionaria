import { Controller } from '@hotwired/stimulus';

/**
 * Formulario de filtros de los listados.
 *
 * Busca cuando el usuario lo pide (boton Buscar o Enter), no mientras tipea:
 * cada busqueda reemplaza el contenido del turbo-frame y eso se llevaba puesto
 * el foco del input apenas escribias la primera letra.
 *
 * Arma la URL a mano, sin los campos vacios. Ademas de quedar limpia, evita un
 * 400: InputBag::getInt() de Symfony rechaza el string vacio, y una respuesta de
 * error no trae el turbo-frame, asi que el listado mostraba "Content missing".
 */
export default class extends Controller {
    connect() {
        this.onSubmit = this.#onSubmit.bind(this);
        this.element.addEventListener('submit', this.onSubmit);
    }

    disconnect() {
        this.element.removeEventListener('submit', this.onSubmit);
    }

    /** Para botones que quieran buscar sin pasar por el boton principal. */
    submitNow() {
        this.element.requestSubmit();
    }

    #onSubmit(event) {
        event.preventDefault();

        const params = new URLSearchParams(new FormData(this.element));
        params.delete('page'); // cualquier filtro nuevo vuelve a la primera pagina
        [...params.entries()].forEach(([clave, valor]) => {
            if (valor === '') params.delete(clave);
        });

        const query = params.toString();
        const url = query ? `${this.element.action}?${query}` : this.element.action;

        // Turbo Drive esta desactivado, asi que sincronizamos la URL a mano para
        // que el filtro sobreviva a un refresh o se pueda compartir.
        history.replaceState({}, '', url);

        const frame = this.element.closest('turbo-frame');
        if (!frame) {
            window.location.assign(url);
            return;
        }

        // Cambiar src ya dispara la carga; si la busqueda es la misma hay que
        // pedir la recarga a mano, o no pasaria nada.
        if (frame.src === url) {
            frame.reload();
        } else {
            frame.src = url;
        }
    }
}
