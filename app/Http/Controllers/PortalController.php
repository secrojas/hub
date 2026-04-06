<?php

namespace App\Http\Controllers;

use App\Enums\BillingStatus;
use App\Enums\TaskStatus;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->user()->client_id;

        abort_unless($clientId, 403);

        $tasks = Task::where('client_id', $clientId)
            ->latest()
            ->get(['id', 'titulo', 'estado', 'fecha_limite']);

        $quotes = Quote::where('client_id', $clientId)
            ->with('items')
            ->latest()
            ->get()
            ->map(fn ($q) => [
                'id'     => $q->id,
                'titulo' => $q->titulo,
                'estado' => $q->estado,
                'total'  => $q->items->sum('precio'),
            ]);

        $billings = Billing::where('client_id', $clientId)
            ->latest()
            ->get(['id', 'concepto', 'monto', 'fecha_emision', 'estado']);

        $dashboard = [
            'tareas'       => $this->taskCounts($clientId),
            'presupuestos' => $this->quoteCounts($clientId),
            'facturacion'  => $this->billingTotals($clientId),
        ];

        $horasBilling = $this->horasBilling($clientId);

        return Inertia::render('Portal/Index', compact('tasks', 'quotes', 'billings', 'dashboard', 'horasBilling'));
    }

    public function pdf(Quote $quote)
    {
        abort_if($quote->client_id !== auth()->user()->client_id, 403);

        $quote->load(['client', 'items']);

        $pdf = Pdf::loadView('pdf.quote', ['quote' => $quote]);
        $slug = Str::slug($quote->titulo);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "presupuesto-{$quote->id}-{$slug}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    private function taskCounts(int $clientId): array
    {
        return Task::where('client_id', $clientId)
            ->get(['estado'])
            ->groupBy(fn ($t) => $t->estado->value)
            ->map->count()
            ->all();
    }

    private function quoteCounts(int $clientId): array
    {
        return Quote::where('client_id', $clientId)
            ->get(['estado'])
            ->groupBy(fn ($q) => $q->estado->value)
            ->map->count()
            ->all();
    }

    private function billingTotals(int $clientId): array
    {
        return [
            'pendiente' => (float) Billing::where('client_id', $clientId)
                ->where('estado', BillingStatus::Pendiente)
                ->sum('monto'),
            'pagado'    => (float) Billing::where('client_id', $clientId)
                ->where('estado', BillingStatus::Pagado)
                ->sum('monto'),
        ];
    }

    private function horasBilling(int $clientId): array
    {
        $client    = Client::find($clientId);
        $valorHora = (float) ($client?->valor_hora ?? 0);

        $tareasFin = Task::where('client_id', $clientId)
            ->where('estado', TaskStatus::Finalizado)
            ->whereNotNull('horas')
            ->orderByDesc('fecha_finalizacion')
            ->get(['id', 'titulo', 'horas', 'fecha_finalizacion']);

        $tareasConMonto = $tareasFin->map(fn ($t) => [
            'id'                 => $t->id,
            'titulo'             => $t->titulo,
            'horas'              => $t->horas,
            'fecha_finalizacion' => $t->fecha_finalizacion?->format('Y-m-d'),
            'monto'              => round($t->horas * $valorHora, 2),
        ]);

        $now          = now();
        $totalSemanal = round($tareasFin
            ->filter(fn ($t) => $t->fecha_finalizacion?->gte($now->copy()->startOfWeek()))
            ->sum(fn ($t) => $t->horas * $valorHora), 2);
        $totalMensual = round($tareasFin
            ->filter(fn ($t) => $t->fecha_finalizacion?->gte($now->copy()->startOfMonth()))
            ->sum(fn ($t) => $t->horas * $valorHora), 2);

        return [
            'valor_hora'    => $valorHora,
            'tareas'        => $tareasConMonto,
            'total_semanal' => $totalSemanal,
            'total_mensual' => $totalMensual,
        ];
    }
}
