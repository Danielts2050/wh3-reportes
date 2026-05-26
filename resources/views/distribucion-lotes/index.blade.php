@extends('layouts.app')

@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-warning fw-bold">
        Distribución por Lotes
    </div>

    <div class="card-body">

        <form method="POST" action="/distribucion-lotes/procesar">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">
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

<div class="card shadow-sm mt-4">
    <div class="card-header bg-dark text-white fw-bold">
        Resultado
    </div>

    <div class="card-body">

        <ul class="nav nav-tabs" role="tablist">
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

        <div class="tab-content border border-top-0 p-3">

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