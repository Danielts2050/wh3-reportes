<?php

namespace App\Http\Controllers;

use App\Models\OvertimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorasExtrasController extends Controller
{
  public function index(Request $request)
{
    $user = Auth::user();

    $query = OvertimeEntry::with('user');

    if ($user->role->name !== 'gerente') {
        $query->where('user_id', $user->id);
    }

    if (
        $user->role->name === 'gerente' &&
        $request->filled('supervisor_id')
    ) {
        $query->where('user_id', $request->supervisor_id);
    }

    if ($request->filled('employee_name')) {
        $query->where(
            'employee_name',
            'like',
            '%' . $request->employee_name . '%'
        );
    }

    if ($request->filled('employee_code')) {
        $query->where(
            'employee_code',
            'like',
            '%' . $request->employee_code . '%'
        );
    }

    if ($request->filled('date_from')) {
        $query->whereDate(
            'work_date',
            '>=',
            $request->date_from
        );
    }

    if ($request->filled('date_to')) {
        $query->whereDate(
            'work_date',
            '<=',
            $request->date_to
        );
    }

    $entries = $query
        ->orderByDesc('work_date')
        ->get();

    $totalRegistros = $entries->count();

    $totalHoras = $entries->sum('hours');

    $totalEmpleados = $entries
        ->pluck('employee_code')
        ->unique()
        ->count();

    $totalSupervisores = $entries
        ->pluck('user_id')
        ->unique()
        ->count();

    $promedioHoras = $totalRegistros > 0
        ? round($totalHoras / $totalRegistros, 2)
        : 0;

    $topEmployee = $entries
        ->groupBy('employee_code')
        ->map(function ($items) {
            return [
                'name' => $items->first()->employee_name,
                'code' => $items->first()->employee_code,
                'hours' => $items->sum('hours'),
            ];
        })
        ->sortByDesc('hours')
        ->first();

    $supervisores = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'supervisor');
        })
        ->orderBy('name')
        ->get();

    $view = $user->role->name === 'gerente'
        ? 'horas-extras.gerencia'
        : 'horas-extras.index';

    return view($view, [
        'entries' => $entries,

        'totalRegistros' => $totalRegistros,
        'totalHoras' => $totalHoras,
        'totalEmpleados' => $totalEmpleados,
        'totalSupervisores' => $totalSupervisores,
        'promedioHoras' => $promedioHoras,

        'topEmployee' => $topEmployee,

        'supervisores' => $supervisores,

        'isGerente' => $user->role->name === 'gerente',

        'filters' => $request->only([
            'supervisor_id',
            'employee_name',
            'employee_code',
            'date_from',
            'date_to',
        ]),
    ]);
}
    public function store(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_code' => 'required|string|max:50',
            'hours' => 'required|numeric|min:0.01',
            'work_date' => 'required|date',
        ]);

        OvertimeEntry::create([
            'user_id' => Auth::id(),
            'employee_name' => $request->employee_name,
            'employee_code' => $request->employee_code,
            'hours' => $request->hours,
            'work_date' => $request->work_date,
        ]);

        return redirect('/horas-extras')
            ->with('success', 'Registro de horas extras guardado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_code' => 'required|string|max:50',
            'hours' => 'required|numeric|min:0.01',
            'work_date' => 'required|date',
        ]);

        $user = Auth::user();

        $entryQuery = OvertimeEntry::where('id', $id);

        if ($user->role->name !== 'gerente') {
            $entryQuery->where('user_id', $user->id);
        }

        $entry = $entryQuery->firstOrFail();

        $entry->update([
            'employee_name' => $request->employee_name,
            'employee_code' => $request->employee_code,
            'hours' => $request->hours,
            'work_date' => $request->work_date,
        ]);

        return redirect('/horas-extras')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $entryQuery = OvertimeEntry::where('id', $id);

        if ($user->role->name !== 'gerente') {
            $entryQuery->where('user_id', $user->id);
        }

        $entry = $entryQuery->firstOrFail();

        $entry->delete();

        return redirect('/horas-extras')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function exportar(Request $request)
{
    $user = Auth::user();

    $query = OvertimeEntry::with('user');

    if ($user->role->name !== 'gerente') {
        $query->where('user_id', $user->id);
    }

    if ($user->role->name === 'gerente' && $request->filled('supervisor_id')) {
        $query->where('user_id', $request->supervisor_id);
    }

    if ($request->filled('employee_name')) {
        $query->where('employee_name', 'like', '%' . $request->employee_name . '%');
    }

    if ($request->filled('employee_code')) {
        $query->where('employee_code', 'like', '%' . $request->employee_code . '%');
    }

    if ($request->filled('date_from')) {
        $query->whereDate('work_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('work_date', '<=', $request->date_to);
    }

    $entries = $query->orderByDesc('work_date')->get();

    $isGerente = $user->role->name === 'gerente';

    $borde = 'border:1px solid #000; padding:8px 14px;';
    $amarillo = 'background-color:#FFF200;';
    $centro = 'text-align:center; vertical-align:middle;';
    $negrita = 'font-weight:bold;';

    $html = '<html><head><meta charset="UTF-8"></head><body>';
    $html .= '<table cellspacing="0" cellpadding="10" style="border-collapse:collapse;font-family:Arial;font-size:11pt;">';

    $html .= '<tr>';

    if ($isGerente) {
        $html .= '<th style="'.$borde.$amarillo.$centro.$negrita.'">Supervisor</th>';
    }

    $html .= '<th style="'.$borde.$amarillo.$centro.$negrita.'">Empleado</th>';
    $html .= '<th style="'.$borde.$amarillo.$centro.$negrita.'">Código</th>';
    $html .= '<th style="'.$borde.$amarillo.$centro.$negrita.'">Horas</th>';
    $html .= '<th style="'.$borde.$amarillo.$centro.$negrita.'">Fecha</th>';
    $html .= '</tr>';

    foreach ($entries as $entry) {
        $html .= '<tr>';

        if ($isGerente) {
            $html .= '<td style="'.$borde.$centro.'">'.($entry->user->name ?? 'Sin supervisor').'</td>';
        }

        $html .= '<td style="'.$borde.$centro.'">'.$entry->employee_name.'</td>';
        $html .= '<td style="'.$borde.$centro.'">'.$entry->employee_code.'</td>';
        $html .= '<td style="'.$borde.$centro.'">'.number_format($entry->hours, 2).'</td>';
        $html .= '<td style="'.$borde.$centro.'">'.\Carbon\Carbon::parse($entry->work_date)->format('d/m/Y').'</td>';

        $html .= '</tr>';
    }

    $html .= '</table></body></html>';

    return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="horas_extras.xls"');
    }
}