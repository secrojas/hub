<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\BankAccount;
use App\Models\Finance\BankAccountBalance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function index(): Response
    {
        $accounts = BankAccount::with(['latestBalance', 'balances' => fn ($q) => $q->orderByDesc('fecha')])
            ->orderBy('orden')
            ->get();

        return Inertia::render('Admin/Finance/Accounts', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo'   => ['required', 'in:caja_ahorro,cuenta_corriente,billetera_digital'],
            'color'  => ['required', 'string', 'max:30'],
            'orden'  => ['nullable', 'integer'],
        ]);

        BankAccount::create($data);

        return back()->with('success', 'Cuenta creada.');
    }

    public function update(Request $request, BankAccount $account): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo'   => ['required', 'in:caja_ahorro,cuenta_corriente,billetera_digital'],
            'color'  => ['required', 'string', 'max:30'],
            'orden'  => ['nullable', 'integer'],
            'activo' => ['boolean'],
        ]);

        $account->update($data);

        return back()->with('success', 'Cuenta actualizada.');
    }

    public function destroy(BankAccount $account): RedirectResponse
    {
        $account->delete();

        return back()->with('success', 'Cuenta eliminada.');
    }

    public function addBalance(Request $request, BankAccount $account): RedirectResponse
    {
        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date'],
            'nota'  => ['nullable', 'string', 'max:500'],
        ]);

        $account->balances()->create($data);

        return back()->with('success', 'Saldo registrado.');
    }
}
