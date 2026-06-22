@extends('layouts.app')
@section('page-title', 'Resumen de Paletas')
@section('page-description', 'Agrupa materiales, calcula paletas y genera exportación operativa.')

@section('content')




<div class="card corporate-card">

    <div class="card-header" style="background:linear-gradient(135deg,var(--purple-700),var(--purple-500));color:#fff;">

        Resumen de Paletas por Material

    </div>

    <div class="card-body">

        <div class="helper-card mb-4">

            <strong>Formato esperado:</strong>

            Material | Cantidad

            <br><br>

            Puedes copiar directamente desde Excel y pegarlo aquí.

        </div>


        <form
        method="POST"
        action="/resumen-paletas/procesar"
        id="form-procesar"
        >

            @csrf

            <div class="form-grid">

                <div>

                    <label class="form-label fw-bold">

                        Referencia

                    </label>

                    <input
                    type="text"
                    name="referencia"
                    class="form-control"
                    placeholder="Ej: REF-001"
                    value="{{ old('referencia',$referencia ?? '') }}"
                    >

                </div>


                <div>

                    <label class="form-label fw-bold">

                        Datos desde Excel

                    </label>

                    <textarea
                    class="form-control font-monospace"
                    rows="10"
                    name="datos"
                    placeholder="Material    Cantidad"
                    >{{ old('datos',$datos ?? '') }}</textarea>

                </div>

            </div>


            <div class="action-row">

                <button
                class="btn btn-success"
                id="btnProcesar"
                >

                    <i class="fa-solid fa-gears"></i>

                    Procesar

                </button>

            </div>

        </form>

    </div>

</div>


@if($resultado)

<div class="card corporate-card preview-card">

    <div class="card-header preview-toolbar">

        <span class="fw-bold">

            Resultado generado

        </span>

        <div class="d-flex gap-2 flex-wrap">

            <button
            type="button"
            class="btn btn-dark"
            onclick="copiarTablaConEstilos()"
            >

                <i class="fa-solid fa-copy"></i>

                Copiar

            </button>


            <form
            method="POST"
            action="/resumen-paletas/exportar"
            >

                @csrf

                <input
                type="hidden"
                name="resultado"
                value='@json($resultado)'
                >

                <button
                class="btn btn-primary"
                id="btnExportar"
                >

                    <i class="fa-solid fa-file-excel"></i>

                    Exportar

                </button>

            </form>

        </div>

    </div>

    <div class="card-body">

        <div
        id="tabla-copiable"
        class="excel-preview-wrapper"
        >

            @include(
            'resumen-paletas.excel-preview',
            ['resultado'=>$resultado]
            )

        </div>

    </div>

</div>

@endif

@endsection


@section('scripts')

<script>

document
.getElementById('form-procesar')
?.addEventListener(
'submit',
()=>{

notifyInfo(
'Procesando información...'
);

}
);


document
.getElementById('btnExportar')
?.addEventListener(
'click',
()=>{

notifySuccess(
'Generando Excel...'
);

}
);

</script>

@endsection