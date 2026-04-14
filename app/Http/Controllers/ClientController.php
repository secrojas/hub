<?php

namespace App\Http\Controllers;

use App\Enums\BillingStatus;
use App\Enums\TaskStatus;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Clients/Index', [
            'clients'      => $clients,
            'filtroEstado' => $request->estado,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Clients/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'unique:clients,email'],
            'empresa'           => ['nullable', 'string', 'max:255'],
            'telefono'          => ['nullable', 'string', 'max:50'],
            'stack_tecnologico' => ['nullable', 'string'],
            'estado'            => ['nullable', 'in:activo,potencial,pausado'],
            'notas'             => ['nullable', 'string'],
            'fecha_inicio'      => ['nullable', 'date'],
            'valor_hora'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['estado'] = $data['estado'] ?? 'activo';

        Client::create($data);

        return redirect()->route('clients.index');
    }

    public function show(Client $client)
    {
        $hasActiveUser = $client->user()->exists();
        $valorHora     = (float) ($client->valor_hora ?? 0);

        $tareasFin = $client->tasks()
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

        return Inertia::render('Admin/Clients/Show', [
            'client'        => $client,
            'hasActiveUser' => $hasActiveUser,
            'billings'      => $client->billings()->latest()->get(['id', 'concepto', 'monto', 'fecha_emision', 'estado']),
            'horasBilling'  => [
                'valor_hora'    => $valorHora,
                'tareas'        => $tareasConMonto,
                'total_semanal' => $totalSemanal,
                'total_mensual' => $totalMensual,
            ],
        ]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Admin/Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', "unique:clients,email,{$client->id}"],
            'empresa'           => ['nullable', 'string', 'max:255'],
            'telefono'          => ['nullable', 'string', 'max:50'],
            'stack_tecnologico' => ['nullable', 'string'],
            'estado'            => ['nullable', 'in:activo,potencial,pausado'],
            'notas'             => ['nullable', 'string'],
            'fecha_inicio'      => ['nullable', 'date'],
            'valor_hora'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index');
    }

    public function portalPreview(Client $client)
    {
        $clientId = $client->id;

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
            'tareas'       => $tasks->groupBy(fn ($t) => $t->estado->value)->map->count()->all(),
            'presupuestos' => $quotes->groupBy('estado')->map->count()->all(),
            'facturacion'  => [
                'pendiente' => (float) Billing::where('client_id', $clientId)->where('estado', BillingStatus::Pendiente)->sum('monto'),
                'pagado'    => (float) Billing::where('client_id', $clientId)->where('estado', BillingStatus::Pagado)->sum('monto'),
            ],
        ];

        $valorHora = (float) ($client->valor_hora ?? 0);

        $tareasFin = Task::where('client_id', $clientId)
            ->where('estado', TaskStatus::Finalizado)
            ->whereNotNull('horas')
            ->orderByDesc('fecha_finalizacion')
            ->get(['id', 'titulo', 'horas', 'fecha_finalizacion']);

        $now = now();

        $horasBilling = [
            'valor_hora'    => $valorHora,
            'tareas'        => $tareasFin->map(fn ($t) => [
                'id'                 => $t->id,
                'titulo'             => $t->titulo,
                'horas'              => $t->horas,
                'fecha_finalizacion' => $t->fecha_finalizacion?->format('Y-m-d'),
                'monto'              => round($t->horas * $valorHora, 2),
            ]),
            'total_semanal' => round($tareasFin->filter(fn ($t) => $t->fecha_finalizacion?->gte($now->copy()->startOfWeek()))->sum(fn ($t) => $t->horas * $valorHora), 2),
            'total_mensual' => round($tareasFin->filter(fn ($t) => $t->fecha_finalizacion?->gte($now->copy()->startOfMonth()))->sum(fn ($t) => $t->horas * $valorHora), 2),
        ];

        return Inertia::render('Portal/Index', [
            'tasks'        => $tasks,
            'quotes'       => $quotes,
            'billings'     => $billings,
            'dashboard'    => $dashboard,
            'horasBilling' => $horasBilling,
            'is_preview'   => true,
            'preview_name' => $client->nombre,
        ]);
    }
}
