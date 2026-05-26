<?php
// --- CONFIGURACIÓN DINÁMICA DE OPENSSL.CNF PARA TU WAMP ---
$phpBinaryDir = dirname(PHP_BINARY); 
$configPath = $phpBinaryDir . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';

if (!file_exists($configPath)) {
    $alternativas = [
        'C:\wamp64\bin\php\php' . PHP_VERSION . '\extras\ssl\openssl.cnf',
        'C:/wamp64/bin/php/php' . PHP_VERSION . '/extras/ssl/openssl.cnf',
        'C:\wamp\bin\php\php' . PHP_VERSION . '\extras\ssl\openssl.cnf'
    ];
    foreach ($alternativas as $ruta) {
        if (file_exists($ruta)) {
            $configPath = $ruta;
            break;
        }
    }
}
// ---------------------------------------------------------

// Variables para el control de la interfaz web
$error_msg = "";
$status_class = "";
$status_title = "";
$status_desc = "";
$pubKeyTexto = "";
$privKeyTexto = "";

// Configuración de los parámetros criptográficos (Usando la ruta dinámica de tu WAMP)
$configArgs = array(
    "config" => $configPath,   
    'private_key_bits' => 2048,      // Tamaño de la llave (RSA estándar seguro)
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
);

// 1. Generar de forma asimétrica el recurso de la llave privada
$privateKey = openssl_pkey_new($configArgs);

if (!$privateKey) {
    $error_msg = "Error crítico al generar el par de llaves asimétricas. Verifica que la extensión de OpenSSL esté activa en WAMP. Detalles: " . openssl_error_string();
} else {
    // 2. Guardar la llave privada en el archivo private.key (Formato PEM en el disco local)
    // No compartir este archivo con nadie (Secreto del Emisor)
    if (!openssl_pkey_export_to_file($privateKey, 'private.key', null, array("config" => $configPath))) {
        $error_msg = "Error al exportar la llave privada a archivo: " . openssl_error_string();
    } else {
        // Capturar el string de la clave privada para mostrarlo en la interfaz de auditoría
        openssl_pkey_export($privateKey, $privKeyTexto, null, array("config" => $configPath));

        // 3. Generar/extraer la llave pública correspondiente desde los detalles de la privada
        $a_key = openssl_pkey_get_details($privateKey);
        $pubKeyTexto = $a_key['key'];

        // 4. Guardar la llave pública en un archivo físico 'public.key'
        file_put_contents('public.key', $pubKeyTexto);

        // Configurar interfaz en modo Éxito
        $status_class = "success";
        $status_title = "🔑 Generación Asimétrica Exitosa";
        $status_desc = "Se ha estructurado matemáticamente un par de claves RSA de 2048 bits. Los archivos físicos han sido escritos correctamente en la raíz de tu servidor local.";
    }

    // 5. Liberar de forma segura el recurso de memoria de la llave privada (Práctica de código limpio)
    // Nota: openssl_free_key está obsoleta en PHP 8 por recolección de basura automática, 
    // pero la dejamos bajo una validación para mantener compatibilidad exacta con la guía de la profesora.
    if (PHP_VERSION_ID < 80000) {
        openssl_free_key($privateKey);
    }
}

if (!empty($error_msg)) {
    $status_class = "danger";
    $status_title = "❌ Error en Infraestructura de Llaves (PKI)";
    $status_desc = $error_msg;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio OpenSSL - Generación de Llaves</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 850px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #eaedf1; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #1e293b; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 600; color: #475569; margin-top: 25px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        .alert.success { background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert.danger { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; }
        
        .grid-keys { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .key-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 11px; white-space: pre-wrap; word-break: break-all; height: 300px; overflow-y: auto; color: #334155; }
        .file-tag { font-weight: bold; color: #0f172a; margin-bottom: 5px; display: block; font-size: 12px; }
        
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Criptografía Asimétrica: Generador de Infraestructura Clave (PKI)</p>
    </div>

    <div class="alert <?php echo $status_class; ?>">
        <h3><?php echo $status_title; ?></h3>
        <p><?php echo $status_desc; ?></p>
    </div>

    <?php if (empty($error_msg)): ?>
        <div class="grid-keys">
            <div>
                <div class="section-title">🔒 1. Llave Privada Generada</div>
                <span class="file-tag">Archivo guardado: <code>private.key</code> (No compartir)</span>
                <div class="key-box" style="background-color: #fff5f5; border-color: #feb2b2;">
                    <?php echo htmlspecialchars($privKeyTexto); ?>
                </div>
            </div>
            
            <div>
                <div class="section-title">🔓 2. Llave Pública Extraída</div>
                <span class="file-tag">Archivo guardado: <code>public.key</code> (Pública)</span>
                <div class="key-box" style="background-color: #f0fff4; border-color: #9ae6b4;">
                    <?php echo htmlspecialchars($pubKeyTexto); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="footer">
        Generador de Claves Criptográficas RSA - Entorno Seguro WAMP Server
    </div>
</div>

</body>
</html>