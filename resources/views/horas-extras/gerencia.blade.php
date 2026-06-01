@extends('layouts.app')

@section('page-title')
Horas Extras - Gerencia
@endsection

@section('page-description')
Vista consolidada de horas extras registradas por todos los supervisores.
@endsection

@section('content')

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card corporate-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="metric-icon bg-primary text-white">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>
                    <span class="text-muted">Total horas</span>
                    <h3 class="mb-0">{{ number_format($totalHoras, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card corporate-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="metric-icon bg-success text-white">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div>
                    <span class="text-muted">Empleados</span>
                    <h3 class="mb-0">{{ $totalEmpleados }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card corporate-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="metric-icon bg-warning text-dark">
                    <i class="fa-solid fa-user-tie"></i>
                </div>

                <div>
                    <span class="text-muted">Supervisores</span>
                    <h3 class="mb-0">{{ $totalSupervisores }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card corporate-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="metric-icon bg-dark text-white">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

                <div>
                    <span class="text-muted">Promedio</span>
                    <h3 class="mb-0">{{ number_format($promedioHoras, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-md-6">
        <div class="card corporate-card h-100">
            <div class="card-header">
                Top Supervisores
            </div>

            <div class="card-body">
                @forelse($horasPorSupervisor->take(5) as $item)

                    <div class="dashboard-bar-item">

                        <div class="d-flex justify-content-between mb-1">
                            <strong>{{ $item['supervisor'] }}</strong>
                            <span>{{ number_format($item['horas'], 2) }} h</span>
                        </div>

                        <div class="progress">
                            <div
                                class="progress-bar bg-primary"
                                style="width: {{ $totalHoras > 0 ? ($item['horas'] / $totalHoras) * 100 : 0 }}%"
                            ></div>
                        </div>

                    </div>

                @empty

                    <p class="text-muted mb-0">
                        No hay datos disponibles.
                    </p>

                @endforelse
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="card corporate-card h-100">
            <div class="card-header">
                Top Empleados
            </div>

            <div class="card-body">
                @forelse($topEmpleados as $item)

                    <div class="top-employee-item">

                        <div>
                            <strong>{{ $item['empleado'] }}</strong>
                            <small>Código: {{ $item['codigo'] }}</small>
                        </div>

                        <span>{{ number_format($item['horas'], 2) }} h</span>

                    </div>

                @empty

                    <p class="text-muted mb-0">
                        No hay empleados para mostrar.
                    </p>

                @endforelse
            </div>
        </div>
    </div>

</div>


<div class="row g-4 mb-4">

    <div class="col-md-6">
        <div class="card corporate-card h-100">
            <div class="card-header">
                Gráfico Mensual
            </div>

            <div class="card-body">
                @forelse($horasPorMes as $item)

                    <div class="dashboard-bar-item">

                        <div class="d-flex justify-content-between mb-1">
                            <strong>{{ $item['mes'] }}</strong>
                            <span>{{ number_format($item['horas'], 2) }} h</span>
                        </div>

                        <div class="progress">
                            <div
                                class="progress-bar bg-success"
                                style="width: {{ $totalHoras > 0 ? ($item['horas'] / $totalHoras) * 100 : 0 }}%"
                            ></div>
                        </div>

                    </div>

                @empty

                    <p class="text-muted mb-0">
                        No hay datos mensuales.
                    </p>

                @endforelse
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="card corporate-card h-100">
            <div class="card-header">
                Horas por Empleado
            </div>

            <div class="card-body">
                @forelse($horasPorEmpleado->take(10) as $item)

                    <div class="top-employee-item">

                        <div>
                            <strong>{{ $item['empleado'] }}</strong>
                            <small>Código: {{ $item['codigo'] }}</small>
                        </div>

                        <span>{{ number_format($item['horas'], 2) }} h</span>

                    </div>

                @empty

                    <p class="text-muted mb-0">
                        No hay datos por empleado.
                    </p>

                @endforelse
            </div>
        </div>
    </div>

</div>

<div class="card corporate-card mb-4">

    <div class="card-header">
        Filtros Gerenciales
    </div>

    <div class="card-body">

        <form method="GET" action="/horas-extras" class="row g-3 align-items-end">

            <div class="col-md-3">

    <label class="form-label fw-bold">
        Supervisor
    </label>

    <select
        name="supervisor_id"
        class="form-select"
    >

        <option value="">
            Todos los supervisores
        </option>

        @foreach($supervisores as $supervisor)

            <option
                value="{{ $supervisor->id }}"
                {{ ($filters['supervisor_id'] ?? '') == $supervisor->id ? 'selected' : '' }}
            >
                {{ $supervisor->name }}
            </option>

        @endforeach

    </select>

</div>

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


<div class="card corporate-card">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

        <span>
            Consolidado General
        </span>

        <span class="badge bg-primary">
            {{ $totalRegistros }} registros
        </span>

    </div>

    <div class="card-body table-responsive">

        <div class="d-flex gap-2 mb-3 flex-wrap">

   

    <a
        href="/horas-extras/exportar?{{ http_build_query(request()->query()) }}"
        class="btn btn-primary"
    >
        <i class="fa-solid fa-file-excel"></i>
        Exportar Excel
    </a>

</div>

        <table
            id="tabla-horas-extras"
            class="table table-bordered align-middle"
        >

            <thead class="table-light">
                <tr>
                    <th>Supervisor</th>
                    <th>Empleado</th>
                    <th>Código</th>
                    <th>Horas</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody>

                @forelse($entries as $entry)

                    <tr>
                        <td>{{ $entry->user->name ?? 'Sin supervisor' }}</td>
                        <td>{{ $entry->employee_name }}</td>
                        <td>{{ $entry->employee_code }}</td>
                        <td>{{ number_format($entry->hours, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($entry->work_date)->format('d/m/Y') }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No existen registros para mostrar.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection