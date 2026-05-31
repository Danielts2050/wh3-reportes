function getSwal() {
    if (typeof window.Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado.');
        return null;
    }

    return window.Swal;
}

function createToast() {
    const SwalInstance = getSwal();

    if (!SwalInstance) {
        return null;
    }

    return SwalInstance.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2800,
        timerProgressBar: true,
        background: '#ffffff',
        color: '#1f2937',
        customClass: {
            popup: 'corporate-toast'
        },
        didOpen: (toastElement) => {
            toastElement.onmouseenter = SwalInstance.stopTimer;
            toastElement.onmouseleave = SwalInstance.resumeTimer;
        }
    });
}

window.notifySuccess = (message) => {
    const toast = createToast();

    if (!toast) {
        alert(message);
        return;
    }

    toast.fire({
        icon: 'success',
        title: message
    });
};

window.notifyInfo = (message) => {
    const toast = createToast();

    if (!toast) {
        alert(message);
        return;
    }

    toast.fire({
        icon: 'info',
        title: message
    });
};

window.notifyError = (message) => {
    const toast = createToast();

    if (!toast) {
        alert(message);
        return;
    }

    toast.fire({
        icon: 'error',
        title: message
    });
};

window.notifyLoading = (message) => {
    const SwalInstance = getSwal();

    if (!SwalInstance) {
        alert(message);
        return;
    }

    SwalInstance.fire({
        title: message,
        html: 'Procesando operación...',
        allowOutsideClick: false,
        background: '#fff',
        customClass: {
            popup: 'corporate-loading'
        },
        didOpen: () => {
            SwalInstance.showLoading();
        }
    });
};

window.closeLoading = () => {
    const SwalInstance = getSwal();

    if (SwalInstance) {
        SwalInstance.close();
    }
};