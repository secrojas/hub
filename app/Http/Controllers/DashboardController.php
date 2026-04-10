<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\NoteService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly NoteService $noteService) {}

    public function index(): Response
    {
        $ventana = [today(), today()->addDays(7)];

        $vencenProonto = Task::with('client')
            ->where('estado', '!=', TaskStatus::Finalizado)
            ->whereNotNull('fecha_limite')
            ->whereBetween('fecha_limite', $ventana)
            ->orderBy('fecha_limite', 'asc')
            ->get();

        $vencenProntoIds = $vencenProonto->pluck('id');

        $enProgreso = Task::with('client')
            ->where('estado', TaskStatus::EnProgreso)
            ->whereNotIn('id', $vencenProntoIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'enProgreso'       => $enProgreso,
            'vencenProonto'    => $vencenProonto,
            'notasDestacadas'  => $this->noteService->getForDashboard(),
        ]);
    }
}
