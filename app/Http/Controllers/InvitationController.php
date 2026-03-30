<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvitationController extends Controller
{
    // GET /invitations/create -- admin form
    public function create()
    {
        return Inertia::render('Admin/Invitations/Create');
    }

    // POST /invitations -- admin generates link
    public function store(Request $request)
    {
        $request->validate([
            'email'       => ['required', 'email', 'unique:users,email'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_id'   => ['nullable', 'exists:clients,id'],
        ]);

        $token = Str::uuid()->toString();

        Invitation::create([
            'token'       => $token,
            'email'       => $request->email,
            'client_name' => $request->client_name,
            'client_id'   => $request->client_id,
            'expires_at'  => now()->addHours(72),
        ]);

        $url = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addHours(72),
            ['token' => $token]
        );

        return back()->with('invitation_url', $url);
    }

    // GET /invitation/accept?token=...&signature=...&expires=... -- client sees form
    public function show(Request $request)
    {
        $invitation = Invitation::where('token', $request->query('token'))->first();

        if (! $invitation) {
            abort(404);
        }

        if ($invitation->used_at) {
            return Inertia::render('Error', [
                'status'  => 403,
                'message' => 'Esta invitacion ya fue utilizada. Contacta al administrador.',
            ])->toResponse($request)->setStatusCode(403);
        }

        return Inertia::render('Invitation/Accept', [
            'email'       => $invitation->email,
            'client_name' => $invitation->client_name,
            'token'       => $invitation->token,
            'accept_url'  => $request->fullUrl(),
        ]);
    }

    // POST /invitation/accept -- client sets password
    public function accept(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $invitation = Invitation::where('token', $request->token)
            ->whereNull('used_at')
            ->firstOrFail();

        $clientId = $invitation->client_id ?? Client::create([
            'nombre' => $invitation->client_name,
            'email'  => $invitation->email,
        ])->id;

        $user = User::create([
            'name'              => $invitation->client_name,
            'email'             => $invitation->email,
            'password'          => Hash::make($request->password),
            'role'              => Role::Client,
            'client_id'         => $clientId,
            'email_verified_at' => now(),
        ]);

        $invitation->update(['used_at' => now()]);

        Auth::login($user);

        return redirect()->route('portal');
    }
}
