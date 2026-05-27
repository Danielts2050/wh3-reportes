const toast = Swal.mixin({

    toast:true,

    position:'top-end',

    showConfirmButton:false,

    timer:2800,

    timerProgressBar:true,

    background:'#ffffff',

    color:'#1f2937',

    customClass:{

        popup:'corporate-toast'

    },

    didOpen:(toastElement)=>{

        toastElement.onmouseenter=
        Swal.stopTimer;

        toastElement.onmouseleave=
        Swal.resumeTimer;

    }

});



window.notifySuccess=(message)=>{

    toast.fire({

        icon:'success',

        title:message

    });

};



window.notifyInfo=(message)=>{

    toast.fire({

        icon:'info',

        title:message

    });

};



window.notifyError=(message)=>{

    toast.fire({

        icon:'error',

        title:message

    });

};



window.notifyLoading=(message)=>{

    Swal.fire({

        title:message,

        html:'Procesando operación...',

        allowOutsideClick:false,

        background:'#fff',

        customClass:{

            popup:'corporate-loading'

        },

        didOpen:()=>{

            Swal.showLoading();

        }

    });

};



window.closeLoading=()=>{

    Swal.close();

}