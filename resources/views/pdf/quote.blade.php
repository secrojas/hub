<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 40px; }
        h1 { color: #1f2937; margin-bottom: 4px; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 24px; }
        .client-info { margin-bottom: 20px; }
        .client-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f3f4f6; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 14px; }
        .notes { margin-top: 24px; padding: 12px; background: #f9fafb; border-left: 4px solid #d1d5db; }
        .estado-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
    </style>
</head>
<body>
    <h1>srojasweb</h1>
    <p class="subtitle">Fecha: {{ $quote->created_at->format('d/m/Y') }}</p>

    <h2>{{ $quote->titulo }}</h2>
    <p>Estado: {{ ucfirst($quote->estado->value) }}</p>

    <div class="client-info">
        <p><strong>Cliente:</strong> {{ $quote->client->nombre }}</p>
        <p><strong>Empresa:</strong> {{ $quote->client->empresa }}</p>
        <p><strong>Email:</strong> {{ $quote->client->email }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripcion</th>
                <th class="text-right">Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->descripcion }}</td>
                <td class="text-right">$ {{ number_format($item->precio, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="total text-right">Total</td>
                <td class="total text-right">$ {{ number_format($quote->items->sum('precio'), 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($quote->notas)
    <div class="notes">
        <strong>Notas:</strong><br>{{ $quote->notas }}
    </div>
    @endif
</body>
</html>
