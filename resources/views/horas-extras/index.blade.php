@extends('layouts.app')

@section('page-title')
Horas Extras
@endsection

@section('page-description')
Registro y seguimiento de horas extras del personal.
@endsection

@section('content')

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            notifySuccess("{{ session('success') }}");
        });
    </script>
@endif

<div class="card corporate-card mb-4">

    <div class="card-header bg-primary text-white">
        Nueva Hora Extra
    </div>

    <div class="card-body">

        <form
            method="POST"
            action="/horas-extras"
            class="row g-3 align-items-end"
            id="overtimeForm"
        >
            @csrf

            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="col-md-4">
                <label class="form-label fw-bold">
                    Empleado
                </label>

                <input
                    type="text"
                    name="employee_name"
                    id="employee_name"
                    class="form-control"
                    placeholder="Ej. Juan Pérez"
                    required
                >
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    Código
                </label>

                <input
                    type="text"
                    name="employee_code"
                    id="employee_code"
                    class="form-control"
                    placeholder="Ej. 1001"
                    required
                >
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    Horas
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="hours"
                    id="hours"
                    class="form-control"
                    placeholder="Ej. 4"
                    required
                >
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    Fecha
                </label>

                <input
                    type="date"
                    name="work_date"
                    id="work_date"
                    class="form-control"
                    value="{{ date('Y-m-d') }}"
                    required
                >
            </div>

            <div class="col-md-2">
                <button class="btn btn-success w-100" id="btnSubmit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar
                </button>
            </div>

        </form>

    </div>

</div>


<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="card corporate-card">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="metric-icon bg-primary text-white">
                    <i class="fa-solid fa-list-check"></i>
                </div>

                <div>
                    <span class="text-muted">
                        Total registros
                    </span>

                    <h3 class="mb-0">
                        {{ $totalRegistros }}
                    </h3>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card corporate-card">
            <div class="card-body d-flex align-items-center gap-3">

                <div class="metric-icon bg-success text-white">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>
                    <span class="text-muted">
                        Total horas
                    </span>

                    <h3 class="mb-0">
                        {{ number_format($totalHoras, 2) }}
                    </h3>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-4">
    <div class="card corporate-card">
        <div class="card-body d-flex align-items-center gap-3">

            <div class="metric-icon bg-warning text-dark">
                <i class="fa-solid fa-user-clock"></i>
            </div>

            <div>
                <span class="text-muted">
                    Empleado con más horas
                </span>

                <h5 class="mb-0">
                    {{ $topEmployee['name'] ?? 'N/A' }}
                </h5>

                <small class="text-muted">
                    {{ isset($topEmployee) ? number_format($topEmployee['hours'], 2) . ' horas' : 'Sin datos' }}
                </small>
            </div>

        </div>
    </div>
</div>

</div>


<div class="card corporate-card">

    <div class="card-header">
        Mis Registros
    </div>

    <div class="card corporate-card mb-4">

    <div class="card-header">
        Filtros de búsqueda
    </div>

    <div class="card-body">

        <form
            method="GET"
            action="/horas-extras"
            class="row g-3 align-items-end"
        >

            <div class="col-md-3">
                <label class="form-label fw-bold">
                    Empleado
                </label>

                <input
                    type="text"
                    name="employee_name"
                    class="form-control"
                    placeholder="Buscar empleado"
                    value="{{ $filters['employee_name'] ?? '' }}"
                >
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    Código
                </label>

                <input
                    type="text"
                    name="employee_code"
                    class="form-control"
                    placeholder="Código"
                    value="{{ $filters['employee_code'] ?? '' }}"
                >
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    Desde
                </label>

                <input
                    type="date"
                    name="date_from"
                    class="form-control"
                    value="{{ $filters['date_from'] ?? '' }}"
                >
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">
                    Hasta
                </label>

                <input
                    type="date"
                    name="date_to"
                    class="form-control"
                    value="{{ $filters['date_to'] ?? '' }}"
                >
            </div>

            <div class="col-md-3 d-flex gap-2">

                <button class="btn btn-primary w-100">
                    <i class="fa-solid fa-filter"></i>
                    Filtrar
                </button>

                <a href="/horas-extras" class="btn btn-dark w-100">
                    <i class="fa-solid fa-rotate-left"></i>
                    Limpiar
                </a>

            </div>

        </form>

    </div>

</div>

    <div class="card-body table-responsive">

        <table class="table table-bordered align-middle">

            <thead class="table-light">
                <tr>
                    <th>Empleado</th>
                    <th>Código</th>
                    <th>Horas</th>
                    <th>Fecha</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>

                @forelse($entries as $entry)

                    <tr>
                        <td>{{ $entry->employee_name }}</td>
                        <td>{{ $entry->employee_code }}</td>
                        <td>{{ number_format($entry->hours, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($entry->work_date)->format('d/m/Y') }}</td>

                        <td class="text-center">

                            <div class="d-flex justify-content-center gap-2">

                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    onclick="editarRegistro(
                                        {{ $entry->id }},
                                        '{{ addslashes($entry->employee_name) }}',
                                        '{{ addslashes($entry->employee_code) }}',
                                        '{{ $entry->hours }}',
                                        '{{ $entry->work_date }}'
                                    )"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="/horas-extras/{{ $entry->id }}"
                                    class="delete-form"
                                    data-empleado="{{ $entry->employee_name }}"
                                    data-codigo="{{ $entry->employee_code }}"
                                    data-horas="{{ number_format($entry->hours, 2) }}"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                    >
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay registros todavía.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection


@section('scripts')

<script>
function editarRegistro(id, employeeName, employeeCode, hours, workDate) {
    const form = document.getElementById('overtimeForm');
    const method = document.getElementById('formMethod');
    const btn = document.getElementById('btnSubmit');

    document.getElementById('employee_name').value = employeeName;
    document.getElementById('employee_code').value = employeeCode;
    document.getElementById('hours').value = hours;
    document.getElementById('work_date').value = workDate;

    form.action = `/horas-extras/${id}`;
    method.value = 'PUT';

    btn.innerHTML = `
        <i class="fa-solid fa-pen-to-square"></i>
        Actualizar
    `;

    btn.classList.remove('btn-success');
    btn.classList.add('btn-primary');

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

    notifyInfo('Modo edición activado');
}


document.querySelectorAll('.delete-form').forEach(form => {

    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const empleado = form.dataset.empleado;
        const codigo = form.dataset.codigo;
        const horas = form.dataset.horas;

        Swal.fire({
            title: 'Eliminar hora extra',
            html: `
                <div style="text-align:left">
                    <p class="mb-2">
                        Esta acción no se puede deshacer.
                    </p>

                    <hr>

                    <p class="mb-1">
                        <strong>Empleado:</strong> ${empleado}
                    </p>

                    <p class="mb-1">
                        <strong>Código:</strong> ${codigo}
                    </p>

                    <p class="mb-0">
                        <strong>Horas:</strong> ${horas}
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });

});
</script>

@endsection