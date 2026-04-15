<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Factura AFIP</title>
</head>
<body style="margin:0;padding:0;background-color:#020617;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#020617;padding:40px 16px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

                    <!-- Brand -->
                    <tr>
                        <td align="center" style="padding-bottom:32px;">
                            <span style="font-size:28px;font-weight:700;color:#f1f5f9;letter-spacing:-0.5px;">secrojas · Hub</span>
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td style="background-color:#0f172a;border:1px solid #1e293b;border-radius:16px;padding:40px 40px 36px;">

                            <p style="margin:0 0 8px;font-size:22px;font-weight:700;color:#f1f5f9;line-height:1.3;">
                                Hola, {{ $billing->client->nombre }}
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#94a3b8;line-height:1.6;">
                                Te adjuntamos la factura AFIP correspondiente al cobro
                                <strong style="color:#f1f5f9;">{{ $billing->concepto }}</strong>.
                            </p>

                            <div style="height:1px;background-color:#1e293b;margin-bottom:28px;"></div>

                            <!-- Invoice summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                                <tr>
                                    <td style="padding:12px 16px;background-color:#1e293b;border-radius:10px;">
                                        <p style="margin:0 0 6px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Comprobante</p>
                                        <p style="margin:0;font-size:14px;color:#f1f5f9;">#{{ str_pad($billing->id, 5, '0', STR_PAD_LEFT) }} — {{ $billing->concepto }}</p>
                                    </td>
                                </tr>
                                <tr><td style="padding:4px 0;"></td></tr>
                                <tr>
                                    <td style="padding:12px 16px;background-color:#1e293b;border-radius:10px;">
                                        <p style="margin:0 0 6px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">Total</p>
                                        <p style="margin:0;font-size:20px;font-weight:700;color:#f1f5f9;">
                                            {{ number_format((float) $billing->monto, 2, ',', '.') }} ARS
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">
                                El PDF de la factura AFIP está adjunto a este email. Si tenés alguna consulta, respondé este mensaje.
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
