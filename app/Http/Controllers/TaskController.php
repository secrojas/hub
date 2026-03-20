<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('client')
            ->when($request->filled('cliente'), fn ($q) => $q->where('client_id', $request->cliente))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->prioridad))
            ->when($request->filled('titulo'), fn ($q) => $q->where('titulo', 'like', "%{$request->titulo}%"))
            ->latest()
            ->get();

        $columns = [
            'backlog'     => $query->filter(fn ($t) => $t->estado === TaskStatus::Backlog)->values(),
            'en_progreso' => $query->filter(fn ($t) => $t->estado === TaskStatus::EnProgreso)->values(),
            'en_revision' => $query->filter(fn ($t) => $t->estado === TaskStatus::EnRevision)->values(),
            'finalizado'  => $query->filter(fn ($t) => $t->estado === TaskStatus::Finalizado)->values(),
        ];

        return Inertia::render('Admin/Tasks/Index', [
            'columns' => $columns,
            'clients' => Client::orderBy('nombre')->get(['id', 'nombre']),
            'filtros' => $request->only(['cliente', 'estado', 'prioridad', 'titulo']),
        ]);
    }

    public function store(StoreTaskRequest $request)
    {
        Task::create(array_merge($request->validated(), ['estado' => 'backlog']));

        return back();
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return back();
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return back();
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'estado' => ['required', 'in:backlog,en_progreso,en_revision,finalizado'],
        ]);

        $task->update(['estado' => $request->estado]);

        return back();
    }
}
