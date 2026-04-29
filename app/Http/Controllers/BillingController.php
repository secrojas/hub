<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreBillingRequest;
use App\Http\Requests\UpdateBillingRequest;
use App\Mail\BillingDetailMail;
use App\Mail\FacturaAfipMail;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
            'clients'            => Client::orderBy('nombre')->get(['id', 'nombre', 'valor_hora']),
            'tareas_finalizadas' => $this->tareasFinalizadas(),
        ]);
    }

    public function store(StoreBillingRequest $request)
    {
        $data  = $request->safe()->except('items');
        $items = $request->validated('items');
        $monto = collect($items)->sum('monto');

        $billing = Billing::create(array_merge($data, ['monto' => $monto]));

        $billing->items()->createMany($items);

        return redirect()->route('billing.index');
    }

    public function show(Billing $billing)
    {
        $billing->load(['client', 'items.task']);

        return Inertia::render('Admin/Billing/Show', [
            'billing' => [
                'id'               => $billing->id,
                'concepto'         => $billing->concepto,
                'monto'            => (float) $billing->monto,
                'fecha_emision'    => $billing->fecha_emision?->toDateString(),
                'fecha_pago'       => $billing->fecha_pago?->toDateString(),
                'estado'           => $billing->estado->value,
                'has_afip_pdf'     => (bool) $billing->afip_pdf_path,
                'afip_uploaded_at' => $billing->afip_uploaded_at?->toDateTimeString(),
                'client'           => $billing->client ? [
                    'nombre' => $billing->client->nombre,
                    'email'  => $billing->client->email,
                ] : null,
                'items'            => $billing->items->map(fn ($item) => [
                    'id'       => $item->id,
                    'concepto' => $item->concepto,
                    'monto'    => (float) $item->monto,
                    'task_id'  => $item->task_id,
                    'task'     => $item->task ? [
                        'id'    => $item->task->id,
                        'titulo'=> $item->task->titulo,
                        'horas' => $item->task->horas,
                    ] : null,
                ]),
            ],
        ]);
    }

    public function sendEmail(Billing $billing)
    {
        $billing->load(['client', 'items.task']);

        Mail::to($billing->client->email)
            ->bcc(env('ADMIN_EMAIL', 'sec.rojas@gmail.com'))
            ->send(new BillingDetailMail($billing));

        return back()->with('message', 'Email enviado a ' . $billing->client->email . ' correctamente.');
    }

    public function edit(Billing $billing)
    {
        $billing->load('items');

        return Inertia::render('Admin/Billing/Edit', [
            'billing'            => array_merge(
                $billing->toArray(),
                ['has_afip_pdf' => (bool) $billing->afip_pdf_path],
            ),
            'clients'            => Client::orderBy('nombre')->get(['id', 'nombre', 'valor_hora']),
            'tareas_finalizadas' => $this->tareasFinalizadas(),
        ]);
    }

    public function update(UpdateBillingRequest $request, Billing $billing)
    {
        $data  = $request->safe()->except('items');
        $items = $request->validated('items');
        $monto = collect($items)->sum('monto');

        $billing->update(array_merge($data, ['monto' => $monto]));

        $billing->items()->delete();
        $billing->items()->createMany($items);

        return redirect()->route('billing.index');
    }

    public function destroy(Billing $billing)
    {
        $billing->delete();

        return redirect()->route('billing.index');
    }

    public function uploadAfipPdf(Request $request, Billing $billing)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($billing->afip_pdf_path) {
            Storage::disk('local')->delete($billing->afip_pdf_path);
        }

        $path = $request->file('pdf')->store('afip', 'local');

        $billing->update([
            'afip_pdf_path'    => $path,
            'afip_uploaded_at' => now(),
        ]);

        $billing->load('client');
        Mail::to($billing->client->email)->send(new FacturaAfipMail($billing));

        return back()->with('success', 'Factura AFIP subida y enviada al cliente.');
    }

    public function downloadAfipPdf(Billing $billing)
    {
        abort_unless($billing->afip_pdf_path && Storage::disk('local')->exists($billing->afip_pdf_path), 404);

        return Storage::disk('local')->download(
            $billing->afip_pdf_path,
            'factura-afip-' . str_pad($billing->id, 5, '0', STR_PAD_LEFT) . '.pdf'
        );
    }

    private function tareasFinalizadas(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('estado', TaskStatus::Finalizado)
            ->whereNotNull('horas')
            ->orderBy('titulo')
            ->get(['id', 'client_id', 'titulo', 'horas']);
    }
}
