<?php
// Datos que se quieren firmar:
$datos = 'Este texto será firmado. Thanks for your attention :)';

// --- DETECCIÓN AUTOMÁTICA E INFALIBLE DE OPENSSL.CNF EN WAMP ---
// Buscamos dinámicamente la carpeta donde está corriendo PHP activo
$phpBinaryDir = dirname(PHP_BINARY); 
$configPath = $phpBinaryDir . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';

// Si por alguna razón la ruta dinámica no responde, se usa alternativas estándar de WAMP
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

$configArgs = array(
    'config' => $configPath, 
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
// --------------------------------------------------------------------------

// Inicializamos variables para controlar la interfaz gráfica HTML
$error_msg = "";
$status_class = "";
$status_title = "";
$status_desc = "";
$claves_creadas = false;

// Intentamos generar el recurso del par de llaves criptográficas
$resourceNewKeyPair = openssl_pkey_new($configArgs);
if (!$resourceNewKeyPair) {
    $error_msg = 'Error al inicializar openssl_pkey_new. <br>Ruta intentada en tu WAMP: <code>' . htmlspecialchars($configPath) . '</code><br>Mensaje interno de Windows: ' . openssl_error_string();
} else {
    // Obtengo del recurso la clave pública como un string 
    $details = openssl_pkey_get_details($resourceNewKeyPair);
    $publicKeyPem = $details['key'];

    // Obtengo la clave privada como string dentro de la variable $privateKeyPem
    if (!openssl_pkey_export($resourceNewKeyPair, $privateKeyPem, NULL, $configArgs)) {
        $error_msg = 'Error en openssl_pkey_export: ' . openssl_error_string();
    } else {
        // Asegurar de forma automática que exista la carpeta 'keys'
        if (!is_dir('keys')) {
            mkdir('keys', 0777, true);
        }

        // Guardo la clave pública y privada en disco:
        file_put_contents('keys/private_key.pem', $privateKeyPem);
        file_put_contents('keys/public_key.pem', $publicKeyPem);

        // Se va a buscar a disco para verificar que el archivo private_key.pem se generó bien:
        $privateKeyPem = file_get_contents('keys/private_key.pem');

        // Obtengo la clave privada como resource desde el string
        $resourcePrivateKey = openssl_get_privatekey($privateKeyPem);

        // Crear la firma digital
        if (!openssl_sign($datos, $firma, $resourcePrivateKey, OPENSSL_ALGO_SHA256)) {
            $error_msg = 'Error en openssl_sign: ' . openssl_error_string();
        } else {
            // Guardar la firma en disco:
            file_put_contents('keys/signature.dat', $firma);
            $claves_creadas = true;

            // Comprobar la firma
            $resultado_verificacion = openssl_verify($datos, $firma, $publicKeyPem, 'sha256WithRSAEncryption');
            if ($resultado_verificacion === 1) {
                $status_class = "success";
                $status_title = "¡Firma Válida y Datos Confiables!";
                $status_desc = "El proceso criptográfico asimétrico concluyó con éxito. La firma digital coincide perfectamente y los archivos han sido generados en la carpeta /keys.";
            } else {
                $status_class = "danger";
                $status_title = "Firma Digital Inválida";
                $status_desc = "La firma es incorrecta o los datos originales fueron modificados en el transcurso.";
            }
        }
    }
}

// Si se detectó un fallo crítico de OpenSSL en alguna función, lo exponemos de forma elegante
if (!empty($error_msg)) {
    $status_class = "danger";
    $status_title = "Error del Sistema OpenSSL";
    $status_desc = $error_msg;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio #5 OpenSSL - Firma Digital (firma7)</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 40px 20px;
            color: #333;
        }
        .container {
            max-width: 750px;
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
            font-size: 15px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            word-break: break-all;
            margin-bottom: 25px;
            color: #334155;
        }
        .alert {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        .alert.success { background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert.danger { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; }
        code { background-color: #ffdcdc; color: #b71c1c; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
        .file-list { list-style: none; padding: 0; margin: 0; }
        .file-item { display: flex; align-items: center; padding: 12px; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 8px; font-size: 13px; }
        .file-icon { font-weight: bold; color: #2563eb; margin-right: 10px; font-size: 16px; }
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Demostración de Firma Criptográfica</p>
    </div>

    <div class="section-title">Texto del Mensaje que se Procederá a Firmar:</div>
    <div class="data-box">
        <?php echo htmlspecialchars($datos); ?>
    </div>

    <div class="alert <?php echo $status_class; ?>">
        <h3><?php echo $status_title; ?></h3>
        <p><?php echo $status_desc; ?></p>
    </div>

    <?php if ($claves_creadas): ?>
        <div class="section-title">Evidencias Creadas Localmente en el Directorio:</div>
        <ul class="file-list">
            <li class="file-item">
                <span class="file-icon">🔑</span> 
                <span><strong>keys/private_key.pem</strong> (Clave privada asimétrica generada)</span>
            </li>
            <li class="file-item">
                <span class="file-icon">🔓</span> 
                <span><strong>keys/public_key.pem</strong> (Clave pública vinculada para verificación)</span>
            </li>
            <li class="file-item">
                <span class="file-icon">🔏</span> 
                <span><strong>keys/signature.dat</strong> (Firma binaria del bloque calculada en SHA-256)</span>
            </li>
        </ul>
    <?php endif; ?>

    <div class="footer">
        Arquitectura de Simulación Segura - Ejecutada sobre WAMP Server & PHP 8.2.13
    </div>
</div>

</body>
</html>