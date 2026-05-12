<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Finance\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Models\Finance\FixedExpense;
use App\Models\Finance\VariableExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FixedExpenseController extends Controller
{
    public function index(): Response
    {
        $fixed = FixedExpense::orderBy('categoria')->orderBy('nombre')->get();
        $variable = VariableExpense::orderByDesc('fecha')->get();

        return Inertia::render('Admin/Finance/Expenses', [
            'fixed_expenses'    => $fixed,
            'variable_expenses' => $variable,
            'categories'        => collect(ExpenseCategory::cases())->map(fn ($c) => [
                'value' => $c->value,
                'label' => $c->label(),
                'color' => $c->color(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'           => ['required', 'string', 'max:150'],
            'monto'            => ['required', 'numeric', 'min:0'],
            'dia_vencimiento'  => ['nullable', 'integer', 'min:1', 'max:31'],
            'categoria'        => ['required', 'string'],
            'descripcion'      => ['nullable', 'string'],
        ]);

        FixedExpense::create($data);

        return back()->with('success', 'Gasto fijo creado.');
    }

    public function update(Request $request, FixedExpense $fixedExpense): RedirectResponse
    {
        $data = $request->validate([
            'nombre'           => ['required', 'string', 'max:150'],
            'monto'            => ['required', 'numeric', 'min:0'],
            'dia_vencimiento'  => ['nullable', 'integer', 'min:1', 'max:31'],
            'categoria'        => ['required', 'string'],
            'activo'           => ['boolean'],
            'descripcion'      => ['nullable', 'string'],
        ]);

        $fixedExpense->update($data);

        return back()->with('success', 'Gasto fijo actualizado.');
    }

    public function destroy(FixedExpense $fixedExpense): RedirectResponse
    {
        $fixedExpense->delete();

        return back()->with('success', 'Gasto fijo eliminado.');
    }
}
