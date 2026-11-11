import './bootstrap';

/**
 * Filtro del desplegable de Codigo Unidad.
 * El input de busqueda filtra las opciones del <select> sin alterar el valor
 * enviado al servidor (que sigue siendo el del <select>).
 */
function initCodigoUnidadFilter() {
    const buscador = document.getElementById('codigo_unidad_buscar');
    const select = document.getElementById('codigo_unidad');

    if (!buscador || !select) {
        return;
    }

    const opciones = Array.from(select.options).map((option) => ({
        value: option.value,
        text: option.textContent,
        selected: option.selected,
    }));

    const normalizar = (texto) => (texto || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    const render = (filtro) => {
        const termino = normalizar(filtro).trim();
        const seleccionado = select.value;

        select.innerHTML = '';

        opciones
            .filter((opcion) => termino === '' || normalizar(opcion.text).includes(termino))
            .forEach((opcion) => {
                const el = document.createElement('option');
                el.value = opcion.value;
                el.textContent = opcion.text;
                el.selected = opcion.value === seleccionado;
                select.appendChild(el);
            });

        if (select.options.length === 0) {
            const vacio = document.createElement('option');
            vacio.value = '';
            vacio.textContent = 'Sin coincidencias';
            select.appendChild(vacio);
        }
    };

    buscador.addEventListener('input', () => render(buscador.value));

    // Al elegir en el select, se refleja el codigo en el buscador.
    select.addEventListener('change', () => {
        const elegido = select.options[select.selectedIndex];
        if (elegido && elegido.value !== '') {
            buscador.value = elegido.textContent;
        }
    });

    // Estado inicial: mostrar la opcion guardada.
    const inicial = opciones.find((opcion) => opcion.selected);
    if (inicial && inicial.value !== '') {
        buscador.value = inicial.text;
    }
}

/**
 * Autocompletado por Codigo OSINERGMIN.
 * ENTER consulta el backend sin enviar el formulario.
 */
function initAgenteAutocomplete() {
    const input = document.getElementById('codigo_osinergmin');

    if (!input) {
        return;
    }

    const url = input.dataset.agenteUrl;
    const estado = document.getElementById('codigo_osinergmin_estado');
    const form = input.closest('form');

    const actividad = document.getElementById('codigo_actividad');
    const razon = document.getElementById('razon_social_agente');
    const codigoUnidad = document.getElementById('codigo_unidad');
    const codigoUnidadBuscar = document.getElementById('codigo_unidad_buscar');

    if (!url) {
        return;
    }

    let ultimoCodigoConsultado = null;
    let abortController = null;

    const setEstado = (texto, clase) => {
        if (!estado) {
            return;
        }
        estado.textContent = texto;
        estado.className = 'mt-1 text-xs ' + (clase || 'text-slate-500');
    };

    const limpiarDatosAsociados = () => {
        if (actividad) {
            actividad.value = '';
        }
        if (razon) {
            razon.value = '';
        }
    };

    // Si el codigo cambia despues de una busqueda, se descartan los datos
    // que pertenecian al codigo anterior.
    input.addEventListener('input', () => {
        if (ultimoCodigoConsultado !== null && input.value.trim() !== ultimoCodigoConsultado) {
            ultimoCodigoConsultado = null;
            limpiarDatosAsociados();
            setEstado('Presiona ENTER para autocompletar.', 'text-slate-500');
        }
    });

    input.addEventListener('keydown', async (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        // Evita que ENTER envie el formulario.
        event.preventDefault();
        event.stopPropagation();

        const codigo = input.value.trim();

        if (codigo === '') {
            setEstado('Ingresa un Código OSINERGMIN.', 'text-amber-700');
            return;
        }

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        setEstado('Consultando...', 'text-slate-500');

        try {
            const respuesta = await fetch(
                url + '?codigo=' + encodeURIComponent(codigo),
                {
                    headers: { Accept: 'application/json' },
                    signal: abortController.signal,
                }
            );

            if (!respuesta.ok) {
                throw new Error('HTTP ' + respuesta.status);
            }

            const data = await respuesta.json();

            if (!data.encontrado) {
                ultimoCodigoConsultado = null;
                limpiarDatosAsociados();
                setEstado(data.mensaje || 'No se encontró el Código OSINERGMIN', 'text-rose-700');
                return;
            }

            ultimoCodigoConsultado = codigo;

            if (actividad) {
                actividad.value = data.codigo_actividad || '';
            }
            if (razon) {
                razon.value = data.razon_social_agente || '';
            }

            // Si el Codigo Actividad coincide con el prefijo de una opcion de
            // Codigo Unidad, se selecciona automaticamente.
            if (codigoUnidad && data.codigo_actividad) {
                const prefijo = String(data.codigo_actividad).trim();
                const coincidencias = Array.from(codigoUnidad.options).filter(
                    (opcion) => opcion.value !== '' && opcion.value.startsWith(prefijo + ' ')
                );

                if (coincidencias.length === 1) {
                    codigoUnidad.value = coincidencias[0].value;
                    if (codigoUnidadBuscar) {
                        codigoUnidadBuscar.value = coincidencias[0].textContent;
                    }
                }
            }

            setEstado('Datos completados correctamente.', 'text-emerald-700');
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            setEstado('No se pudo consultar el código. Inténtalo nuevamente.', 'text-rose-700');
        }
    });

    // Evita el envio implicito del formulario con ENTER en este campo.
    if (form) {
        form.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && event.target === input) {
                event.preventDefault();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCodigoUnidadFilter();
    initAgenteAutocomplete();
});
