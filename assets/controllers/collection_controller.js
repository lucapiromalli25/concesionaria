import { Controller } from '@hotwired/stimulus';

/** Colecciones de Symfony (allow_add / allow_delete) sin jQuery. */
export default class extends Controller {
    static targets = ['container'];
    static values = { prototype: String, index: Number };

    add() {
        const html = this.prototypeValue.replace(/__name__/g, this.indexValue);
        const item = document.createElement('li');
        item.className = 'flex items-center gap-2';
        item.innerHTML = `<div class="flex-1">${html}</div>
            <button type="button" data-action="collection#remove" class="btn-ghost btn-sm text-red-600">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path d="m14.74 9-.346 9m-4.788 0L9.26 9M18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397M4.772 5.79a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.2v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </button>`;
        this.containerTarget.append(item);
        this.indexValue++;

        item.querySelectorAll('input[type="file"]').forEach(input => {
            input.className = 'block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200';
        });
    }

    remove(event) {
        event.target.closest('li').remove();
    }
}
