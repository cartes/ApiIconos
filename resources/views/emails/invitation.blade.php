<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación a {{ $tenantNombre }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 40px 40px 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.75); margin: 8px 0 0; font-size: 14px; }
        .body { padding: 36px 40px; }
        .body p { color: #475569; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .badge { display: inline-block; background: #ede9fe; color: #7c3aed; font-size: 12px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; padding: 4px 12px; border-radius: 100px; }
        .cta { text-align: center; margin: 32px 0; }
        .cta a {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            letter-spacing: -0.2px;
        }
        .expiry { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin-top: 24px; }
        .expiry p { font-size: 13px; color: #94a3b8; margin: 0; }
        .footer { padding: 20px 40px 32px; text-align: center; }
        .footer p { font-size: 12px; color: #94a3b8; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ $tenantNombre }}</h1>
            <p>Te han invitado a colaborar</p>
        </div>
        <div class="body">
            <p>Hola,</p>
            <p>
                Has sido invitado a unirte al espacio de trabajo de
                <strong>{{ $tenantNombre }}</strong> con el rol de
                <span class="badge">{{ $rol }}</span>.
            </p>
            <p>
                Haz clic en el botón a continuación para aceptar la invitación y configurar tu acceso.
                La invitación es válida por <strong>7 días</strong>.
            </p>

            <div class="cta">
                <a href="{{ $acceptUrl }}">Aceptar invitación</a>
            </div>

            <div class="expiry">
                <p>🔗 Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                <strong style="color: #6366f1; word-break: break-all;">{{ $acceptUrl }}</strong></p>
            </div>
        </div>
        <div class="footer">
            <p>Si no esperabas esta invitación, puedes ignorar este correo con seguridad.<br>
            © {{ date('Y') }} Aiconic Comercial</p>
        </div>
    </div>
</body>
</html>
