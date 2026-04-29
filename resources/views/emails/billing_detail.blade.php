<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Detalle de cobro</title>
</head>
<body style="margin:0;padding:0;background-color:#020617;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#020617;padding:40px 16px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

                    <!-- Brand -->
                    <tr>
                        <td align="center" style="padding-bottom:32px;">
                            <span style="font-size:28px;font-weight:700;color:#f1f5f9;letter-spacing:-0.5px;">secrojas · Hub</span>
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td style="background-color:#0f172a;border:1px solid #1e293b;border-radius:16px;padding:40px 40px 36px;">

                            <p style="margin:0 0 6px;font-size:22px;font-weight:700;color:#f1f5f9;line-height:1.3;">
                                Hola, {{ $billing->client->nombre }}
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#94a3b8;line-height:1.6;">
                                A continuación encontrás el detalle del cobro correspondiente a
                                <strong style="color:#f1f5f9;">{{ $billing->concepto }}</strong>.
                            </p>

                            <div style="height:1px;background-color:#1e293b;margin-bottom:28px;"></div>

                            <!-- Comprobante info -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                                <tr>
                                    <td style="padding:12px 16px;background-color:#1e293b;border-radius:10px;">
                                        <p style="margin:0 0 4px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Comprobante</p>
                                        <p style="margin:0;font-size:14px;color:#f1f5f9;">#{{ str_pad($billing->id, 5, '0', STR_PAD_LEFT) }} — {{ $billing->concepto }}</p>
                                    </td>
                                </tr>
                                <tr><td style="padding:4px 0;"></td></tr>
                                <tr>
                                    <td style="padding:12px 16px;background-color:#1e293b;border-radius:10px;">
                                        <p style="margin:0 0 4px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Fecha de emisión</p>
                                        <p style="margin:0;font-size:14px;color:#f1f5f9;">{{ $billing->fecha_emision->format('d/m/Y') }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Items table header -->
                            <p style="margin:0 0 12px;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Detalle de tareas</p>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #1e293b;border-radius:10px;overflow:hidden;margin-bottom:4px;">
                                <!-- Table header -->
                                <tr style="background-color:#1e293b;">
                                    <td style="padding:10px 16px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Concepto</td>
                                    <td style="padding:10px 16px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;text-align:center;width:80px;">Horas</td>
                                    <td style="padding:10px 16px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;text-align:right;width:120px;">Monto</td>
                                </tr>

                                @foreach ($billing->items as $item)
                                <tr style="border-top:1px solid #1e293b;">
                                    <td style="padding:12px 16px;font-size:14px;color:#f1f5f9;vertical-align:top;">
                                        @if ($item->task_id && $item->task)
                                            <a href="{{ route('portal.tasks.show', $item->task->id) }}"
                                               style="color:#a78bfa;text-decoration:none;font-weight:500;">
                                                {{ $item->concepto }}
                                            </a>
                                        @else
                                            {{ $item->concepto }}
                                        @endif
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;color:#94a3b8;text-align:center;vertical-align:top;">
                                        @if ($item->task && $item->task->horas)
                                            @php
                                                $totalMins = round($item->task->horas * 60);
                                                $h = intdiv($totalMins, 60);
                                                $m = $totalMins % 60;
                                                $horasStr = $h > 0 && $m > 0 ? "{$h}h {$m}min" : ($h > 0 ? "{$h}h" : "{$m}min");
                                            @endphp
                                            {{ $horasStr }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="padding:12px 16px;font-size:14px;color:#f1f5f9;text-align:right;vertical-align:top;white-space:nowrap;">
                                        {{ number_format((float) $item->monto, 2, ',', '.') }} ARS
                                    </td>
                                </tr>
                                @endforeach

                                <!-- Total row -->
                                <tr style="border-top:2px solid #334155;background-color:#0f172a;">
                                    <td colspan="2" style="padding:14px 16px;font-size:13px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Total</td>
                                    <td style="padding:14px 16px;font-size:18px;font-weight:700;color:#f1f5f9;text-align:right;white-space:nowrap;">
                                        {{ number_format((float) $billing->monto, 2, ',', '.') }} ARS
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:13px;color:#64748b;line-height:1.6;">
                                Si tenés alguna consulta sobre este cobro, respondé este mensaje.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top:28px;">
                            <p style="margin:0;font-size:12px;color:#334155;line-height:1.6;">
                                © {{ date('Y') }} secrojas · Hub — Plataforma de gestión freelance
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
