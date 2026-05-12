<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\VariableExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VariableExpenseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'monto'       => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string', 'max:255'],
            'categoria'   => ['required', 'string'],
        ]);

        VariableExpense::create($data);

        return back()->with('success', 'Gasto variable registrado.');
    }

    public function update(Request $request, VariableExpense $variableExpense): RedirectResponse
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'monto'       => ['required', 'numeric', 'min:0'],
            'descripcion' => ['required', 'string', 'max:255'],
            'categoria'   => ['required', 'string'],
        ]);

        $variableExpense->update($data);

        return back()->with('success', 'Gasto variable actualizado.');
    }

    public function destroy(VariableExpense $variableExpense): RedirectResponse
    {
        if ($variableExpense->comprobante_path) {
            Storage::disk('local')->delete($variableExpense->comprobante_path);
        }

        $variableExpense->delete();

        return back()->with('success', 'Gasto eliminado.');
    }
}
