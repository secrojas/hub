<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillingRequest;
use App\Http\Requests\UpdateBillingRequest;
use App\Models\Billing;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $billings = Billing::with('client')
            ->when($request->filled('cliente'), fn ($q) => $q->where('client_id', $request->cliente))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'cobrado_mes'     => (float) Billing::where('estado', 'pagado')
                ->whereMonth('fecha_pago', now()->month)
                ->whereYear('fecha_pago', now()->year)
                ->sum('monto'),
            'pendiente_total' => (float) Billing::where('estado', 'pendiente')->sum('monto'),
            'vencidos_count'  => Billing::where('estado', 'vencido')->count(),
        ];

        return Inertia::render('Admin/Billing/Index', [
            'billings' => $billings,
            'clients'  => Client::orderBy('nombre')->get(['id', 'nombre']),
            'filtros'  => $request->only(['cliente', 'estado']),
            'summary'  => $summary,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Billing/Create', [
            'clients' => Client::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreBillingRequest $request)
    {
        Billing::create($request->validated());

        return redirect()->route('billing.index');
    }

    public function edit(Billing $billing)
    {
        $billing->load('client');

        return Inertia::render('Admin/Billing/Edit', [
            'billing' => $billing,
            'clients' => Client::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateBillingRequest $request, Billing $billing)
    {
        $billing->update($request->validated());

        return redirect()->route('billing.index');
    }

    public function destroy(Billing $billing)
    {
        $billing->delete();

        return redirect()->route('billing.index');
    }
}
