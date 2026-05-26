@extends('layouts.app')

@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-warning fw-bold">
        Resumen de Paletas por Material
    </div>

    <div class="card-body">

        <form method="POST" action="/resumen-paletas/procesar">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Referencia</label>
                <input 
                    type="text" 
                    name="referencia" 
                    class="form-control"
                    value="{{ old('referencia', $referencia) }}"
                    placeholder="Ejemplo: REF-001"
                >
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">
                    Pega los datos desde Excel: Material y Cantidad
                </label>

                <textarea 
                    name="datos" 
                    rows="10" 
                    class="form-control font-monospace"
                    placeholder="Material	Cantidad"
                >{{ old('datos', $datos) }}</textarea>

                @error('datos')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-success">
                Procesar
            </button>
        </form>

    </div>
</div>

@if($resultado)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-dark text-white fw-bold">
            Resultado
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-warning text-center">
                    <tr>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Paletas</th>
                        <th>Referencia</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($resultado as $fila)
                        <tr class="{{ $fila['Material'] === 'TOTAL' ? 'table-warning fw-bold' : '' }}">
                            <td>{{ $fila['Material'] }}</td>
                            <td class="text-end">{{ number_format($fila['Cantidad'], 0) }}</td>
                            <td class="text-center">{{ $fila['Paletas'] }}</td>
                            <td>{{ $fila['Referencia'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($resultado)

<form
method="POST"
action="/resumen-paletas/exportar"
class="mt-3">

@csrf

<input
type="hidden"
name="resultado"
value='@json($resultado)'>

<button
class="btn btn-primary">

Exportar Excel

</button>

</form>

@endif
    
@endif

@endsection