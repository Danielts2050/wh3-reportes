const form = document.getElementById('form-procesar');

if (form) {
    form.addEventListener('submit', () => {
        const boton = document.getElementById('btnProcesar');
        const card = form.closest('.card-body');

        if (boton) {
            boton.disabled = true;
            boton.classList.add('btn-loading');

            boton.innerHTML = `
                <i class="fa-solid fa-circle-notch me-2"></i>
                Procesando
            `;
        }

        if (card) {
            card.classList.add('processing-overlay');
        }

        notifyLoading(
            'Procesando resumen'
        );

        setTimeout(()=>{

            closeLoading();

        },800);
    });
}

window.copiarTablaConEstilos = async () => {
    const tabla = document.getElementById('tabla-copiable');

    if (!tabla) {
        notifyError('No existe información para copiar');
        return;
    }

    try {
        await navigator.clipboard.write([
            new ClipboardItem({
                'text/html': new Blob([tabla.innerHTML], { type: 'text/html' }),
                'text/plain': new Blob([tabla.innerText], { type: 'text/plain' })
            })
        ]);

        notifySuccess('Tabla copiada con formato profesional');
    } catch (e) {
        notifyError('No se pudo copiar la tabla');
    }
};