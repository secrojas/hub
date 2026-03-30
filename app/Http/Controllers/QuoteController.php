<?php

namespace App\Http\Controllers;

use App\Enums\QuoteStatus;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Client;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = Quote::with('client', 'items')
            ->latest()
            ->get()
            ->map(fn ($q) => array_merge($q->toArray(), [
                'total' => $q->items->sum('precio'),
            ]));

        return Inertia::render('Admin/Quotes/Index', [
            'quotes'  => $quotes,
            'clients' => Client::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Quotes/Create', [
            'clients' => Client::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreQuoteRequest $request)
    {
        $quote = Quote::create($request->safe()->except('items'));

        foreach ($request->validated()['items'] as $item) {
            $quote->items()->create($item);
        }

        return redirect()->route('quotes.index');
    }

    public function edit(Quote $quote)
    {
        $quote->load(['client', 'items']);

        return Inertia::render('Admin/Quotes/Edit', [
            'quote'   => $quote,
            'clients' => Client::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(UpdateQuoteRequest $request, Quote $quote)
    {
        abort_if($quote->estado !== QuoteStatus::Borrador, 403);

        $quote->update($request->safe()->except('items'));

        $quote->items()->delete();

        foreach ($request->validated()['items'] as $item) {
            $quote->items()->create($item);
        }

        return redirect()->route('quotes.index');
    }

    public function destroy(Quote $quote)
    {
        abort_if($quote->estado !== QuoteStatus::Borrador, 403);

        $quote->delete();

        return redirect()->route('quotes.index');
    }

    public function updateEstado(Request $request, Quote $quote)
    {
        $request->validate([
            'estado' => ['required', Rule::enum(QuoteStatus::class)],
        ]);

        $quote->update(['estado' => $request->estado]);

        return back();
    }
}
