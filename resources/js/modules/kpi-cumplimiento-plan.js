document.addEventListener('DOMContentLoaded', function() {
    const contenedoresInput = document.getElementById('contenedores_input');
    const observacionesWrapper = document.getElementById('observaciones_wrapper');

    if (contenedoresInput && observacionesWrapper) {
        contenedoresInput.addEventListener('input', function() {
            const val = parseInt(this.value);
            if (val < 8 && this.value !== '') {
                observacionesWrapper.style.display = 'block';
            } else {
                observacionesWrapper.style.display = 'none';
            }
        });
    }

    const btnProcesar = document.getElementById('btnProcesar');
    if (btnProcesar) {
        btnProcesar.addEventListener('click', function(e) {
            if (typeof notifyInfo === 'function') {
                notifyInfo('Procesando reporte KPI...');
            }
        });
    }

    const btnExportarPdf = document.getElementById('btnExportarPdf');
    if (btnExportarPdf) {
        btnExportarPdf.addEventListener('click', function() {
            if (typeof notifyInfo === 'function') {
                notifyInfo('Generando PDF...');
            }
        });
    }
});
