import './bootstrap.js';
import './styles/app.css';
import { session } from '@hotwired/turbo';

/*
 * Turbo Drive queda desactivado mientras convivan el diseño nuevo (Tailwind)
 * y las pantallas viejas (KaiAdmin + jQuery): asi cada navegacion entre
 * secciones es una carga normal y los plugins viejos no se re-inicializan.
 * Los <turbo-frame> siguen funcionando (listado de inventario).
 */
session.drive = false;
