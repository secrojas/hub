<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Invitación Hub</title>
</head>
<body style="margin:0;padding:0;background-color:#020617;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    <!-- Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#020617;padding:40px 16px;">
        <tr>
            <td align="center">

                <!-- Container -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

                    <!-- Logo / Brand -->
                    <tr>
                        <td align="center" style="padding-bottom:32px;">
                            <span style="font-size:28px;font-weight:700;color:#f1f5f9;letter-spacing:-0.5px;">
                                Hub
                            </span>
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td style="background-color:#0f172a;border:1px solid #1e293b;border-radius:16px;padding:40px 40px 36px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 8px;font-size:22px;font-weight:700;color:#f1f5f9;line-height:1.3;">
                                Hola, {{ $clientName }}
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#94a3b8;line-height:1.6;">
                                Fuiste invitado a acceder al portal de clientes de <strong style="color:#f1f5f9;">Hub</strong>.
                                Desde ahí vas a poder ver el estado de tus proyectos, presupuestos y facturación en tiempo real.
                            </p>

                            <!-- Divider -->
                            <div style="height:1px;background-color:#1e293b;margin-bottom:28px;"></div>

                            <!-- CTA -->
                            <p style="margin:0 0 20px;font-size:14px;color:#64748b;line-height:1.5;">
                                Hacé clic en el botón para crear tu contraseña y activar tu cuenta. El link es válido por <strong style="color:#94a3b8;">72 horas</strong>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $invitationUrl }}"
                                           target="_blank"
                                           style="display:inline-block;padding:14px 36px;background-color:#7c3aed;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;border-radius:10px;letter-spacing:0.2px;">
                                            Activar mi cuenta →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fallback URL -->
                            <p style="margin:28px 0 0;font-size:12px;color:#475569;line-height:1.6;text-align:center;">
                                Si el botón no funciona, copiá y pegá este link en tu navegador:<br />
                                <a href="{{ $invitationUrl }}"
                                   style="color:#7c3aed;word-break:break-all;text-decoration:none;">
                                    {{ $invitationUrl }}
                                </a>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top:28px;">
                            <p style="margin:0;font-size:12px;color:#334155;line-height:1.6;">
                                Si no esperabas esta invitación, podés ignorar este email.<br />
                                © {{ date('Y') }} Hub — Plataforma de gestión freelance
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
