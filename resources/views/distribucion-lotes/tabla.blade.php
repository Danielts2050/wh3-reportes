<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-warning text-center">
            <tr>
                <th>Material</th>
                <th>Lotes</th>
                <th>localidad</th>
                <th>Cantidad</th>
                <th>N.Lotes</th>
            </tr>
        </thead>

        <tbody>
            @forelse($filas as $fila)
                <tr>
                    <td>{{ $fila['Material'] }}</td>
                    <td>{{ $fila['Lotes'] }}</td>
                    <td>{{ $fila['localidad'] }}</td>
                    <td class="text-end">{{ $fila['Cantidad'] }}</td>
                    <td class="text-center">{{ $fila['N.Lotes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Sin datos
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>