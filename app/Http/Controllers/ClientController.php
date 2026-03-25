<?php

namespace App\Http\Controllers;

use App\Models\Client;
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
        ]);

        $data['estado'] = $data['estado'] ?? 'activo';

        Client::create($data);

        return redirect()->route('clients.index');
    }

    public function show(Client $client)
    {
        $hasActiveUser = $client->user()->exists();

        return Inertia::render('Admin/Clients/Show', [
            'client'        => $client,
            'hasActiveUser' => $hasActiveUser,
            'billings'      => $client->billings()->latest()->get(['id', 'concepto', 'monto', 'fecha_emision', 'estado']),
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
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index');
    }
}
