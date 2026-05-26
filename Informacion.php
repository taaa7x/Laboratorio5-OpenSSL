<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio #5 OpenSSL - Información del Entorno</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 40px 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            background-color: #ffffff;
            margin: 0 auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #eaedf1;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #1e293b;
            font-size: 24px;
            margin: 0 0 8px 0;
        }
        .header p {
            color: #64748b;
            margin: 0;
            font-size: 14px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        /* Estilos personalizados para incrustar el phpinfo de forma limpia */
        .phpinfo-wrapper {
            background-color: #fff;
            overflow-x: auto;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        /* Ajustar tablas nativas de phpinfo para que no rompan el diseño */
        .phpinfo-wrapper table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 20px !important; font-size: 13px !important;}
        .phpinfo-wrapper td, .phpinfo-wrapper th { border: 1px solid #cbd5e1 !important; padding: 8px !important; word-break: break-all !important; }
        .phpinfo-wrapper th { background-color: #1e293b !important; color: white !important; text-align: left !important; }
        .phpinfo-wrapper .h { background-color: #f1f5f9 !important; font-weight: bold !important; color: #0f172a !important; }
        .phpinfo-wrapper .e { background-color: #f8fafc !important; font-weight: bold !important; width: 30% !important; color: #334155 !important; }
        .phpinfo-wrapper .v { background-color: #fff !important; color: #475569 !important; }
        .footer {
            text-align: center;
            margin-top: 35px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #eaedf1;
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Auditoría del Sistema Web</p>
    </div>

    <div class="section-title">Diagnóstico y Variables de Configuración de PHP (WAMP)</div>
    
    <div class="phpinfo-wrapper">
        <?php 
        // Capturamos el output de phpinfo y lo limpiamos un poco para integrarlo estéticamente
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();
        
        // Extraemos solo el cuerpo interno del phpinfo original para heredar nuestros estilos CSS
        $pinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);
        echo $pinfo;
        ?>
    </div>

    <div class="footer">
        Evidencia de Configuración del Lado del Servidor - WAMP Server
    </div>
</div>

</body>
</html>