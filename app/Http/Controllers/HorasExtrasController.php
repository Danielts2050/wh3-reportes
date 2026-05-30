<?php

namespace App\Http\Controllers;

use App\Models\OvertimeEntry;
use Illuminate\Http\Request;

class HorasExtrasController extends Controller
{
   public function index(Request $request)
{
    $userId = 1;

    $query = OvertimeEntry::where('user_id', $userId);

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

    $entries = $query
        ->orderByDesc('work_date')
        ->get();

    $totalRegistros = $entries->count();
    $totalHoras = $entries->sum('hours');

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

    return view('horas-extras.index', [
        'entries' => $entries,
        'totalRegistros' => $totalRegistros,
        'totalHoras' => $totalHoras,
        'topEmployee' => $topEmployee,
        'filters' => $request->only([
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

    $userId = 1;

    OvertimeEntry::create([
        'user_id' => $userId,
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

    $userId = 1;

    $entry = OvertimeEntry::where('user_id', $userId)
        ->where('id', $id)
        ->firstOrFail();

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
        $userId =  1;

        $entry = OvertimeEntry::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $entry->delete();

        return redirect('/horas-extras')
            ->with('success', 'Registro eliminado correctamente.');
    }
}