@extends('layouts.app')
@section('page-title', 'Distribución de Lotes')
@section('page-description', 'Clasifica materiales por lotes y genera una vista organizada para exportar.')

@section('content')

<div class="card corporate-card">
    <div class="card-header" style="background:linear-gradient(135deg,var(--purple-700),var(--purple-500));color:#fff;">
        Distribución por Lotes
    </div>

    <div class="card-body">

        <form method="POST" action="/distribucion-lotes/procesar">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Pega los datos desde Excel:
                    Material, Lotes, localidad, Cantidad, N.Lotes
                </label>

                <textarea 
                    name="datos" 
                    rows="10" 
                    class="form-control font-monospace"
                    placeholder="Material	Lotes	localidad	Cantidad	N.Lotes"
                >{{ old('datos', $datos) }}</textarea>

                @error('datos')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-success">
                Procesar Distribución
            </button>
        </form>

    </div>
</div>

@if(count($lotes) > 0 || count($general) > 0)

<div class="card corporate-card mt-4">
    <div class="card-header" style="background:var(--purple-700);color:#fff;">
        Resultado
    </div>

    <div class="card-body">

        <ul class="nav nav-tabs" role="tablist" style="border-bottom-color:var(--purple-200);">
            @foreach($lotes as $numero => $filas)
                <li class="nav-item">
                    <button 
                        class="nav-link {{ $loop->first ? 'active' : '' }}" 
                        data-bs-toggle="tab"
                        data-bs-target="#lote-{{ $numero }}"
                        type="button">
                        Lote {{ $numero }}
                    </button>
                </li>
            @endforeach

            <li class="nav-item">
                <button 
                    class="nav-link {{ count($lotes) === 0 ? 'active' : '' }}" 
                    data-bs-toggle="tab"
                    data-bs-target="#general"
                    type="button">
                    General
                </button>
            </li>
        </ul>

        <div class="tab-content p-3" style="border:1px solid var(--purple-200);border-top:0;border-radius:0 0 var(--radius-md) var(--radius-md);">

            @foreach($lotes as $numero => $filas)
                <div 
                    class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                    id="lote-{{ $numero }}">

                    @include('distribucion-lotes.tabla', ['filas' => $filas])

                </div>
            @endforeach

            <div 
                class="tab-pane fade {{ count($lotes) === 0 ? 'show active' : '' }}" 
                id="general">

                @include('distribucion-lotes.tabla', ['filas' => $general])

            </div>

        </div>

    </div>
</div>

<form method="POST" action="/distribucion-lotes/exportar" class="mt-3">
    @csrf

    <input type="hidden" name="lotes" value='@json($lotes)'>
    <input type="hidden" name="general" value='@json($general)'>

    <button class="btn btn-primary">
        Exportar Excel
    </button>
</form>

@endif

@endsection