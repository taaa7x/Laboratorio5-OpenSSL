<?php
// --- CONFIGURACIÓN DINÁMICA DE OPENSSL.CNF PARA WAMP ---
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

$configArgs = array(
    'config' => $configPath,
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
// ---------------------------------------------------------

// Mensaje confidencial a procesar
$mensaje = "Este es un mensaje confidencial";

// Variables de control para la interfaz HTML
$error_msg = "";
$status_class = "";
$status_title = "";
$status_desc = "";
$firmaBase64 = "";
$verificacion_ok = false;

// VERIFICACIÓN PREVIA: Comprobar que los archivos del paso anterior existen
if (!file_exists('keysCert/privkey.pem') || !file_exists('keysCert/certout.csr')) {
    $error_msg = "Error crítico: No se encontraron los archivos criptográficos en la carpeta <code>keysCert/</code>. Por favor, ejecuta primero el archivo de generación de certificados (FirmaOtra) para crearlos.";
} else {
    
    // --- PASO 1: PROCESO DE FIRMA (EMISOR) ---
    // Cargar la clave privada desde el archivo generado
    $privateKey = file_get_contents('keysCert/privkey.pem');
    $privKeyResource = openssl_pkey_get_private($privateKey);

    if (!$privKeyResource) {
        $error_msg = "Error al leer la clave privada: " . openssl_error_string();
    } else {
        // Firmar el mensaje usando algoritmos robustos SHA-256
        if (!openssl_sign($mensaje, $firmaBinaria, $privKeyResource, OPENSSL_ALGO_SHA256)) {
            $error_msg = "Error al firmar el mensaje: " . openssl_error_string();
        } else {
            // Convertir la firma binaria a formato legible base64 para su transporte seguro
            $firmaBase64 = base64_encode($firmaBinaria);

            // --- PASO 2: PROCESO DE VERIFICACIÓN (RECEPTOR) ---
            // Cargar el certificado generado en el laboratorio anterior
            $cert = file_get_contents('keysCert/certout.csr');
            
            // Extraer de forma automática la llave pública contenida dentro de la estructura X.509
            $pubKeyResource = openssl_pkey_get_public($cert);

            if (!$pubKeyResource) {
                $error_msg = "Error al extraer la clave pública del certificado: " . openssl_error_string();
            } else {
                // Decodificar la firma recibida para devolverla a su estado binario original
                $firmaBinariaDecodificada = base64_decode($firmaBase64);

                // Verificar matemáticamente la firma utilizando la llave pública extraída
                $ok = openssl_verify($mensaje, $firmaBinariaDecodificada, $pubKeyResource, OPENSSL_ALGO_SHA256);

                if ($ok === 1) {
                    $verificacion_ok = true;
                    $status_class = "success";
                    $status_title = "✅ Firma Válida - Mensaje Auténtico";
                    $status_desc = "El sistema criptográfico confirmó mediante el certificado X.509 que el mensaje es 100% confiable y no sufrió alteraciones ni manipulaciones durante la transmisión.";
                } elseif ($ok === 0) {
                    $status_class = "danger";
                    $status_title = "❌ Firma Inválida - Mensaje Modificado";
                    $status_desc = "La firma digital no se corresponde matemáticamente con los datos suministrados. El mensaje pudo haber sido interceptado o modificado.";
                } else {
                    $error_msg = "Error interno durante el proceso de verificación: " . openssl_error_string();
                }
            }
        }
    }
}

// Controlar visualmente los errores de OpenSSL
if (!empty($error_msg)) {
    $status_class = "danger";
    $status_title = "Error en el Flujo Operacional";
    $status_desc = $error_msg;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio #5 OpenSSL - Firma y Verificación de Mensajes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 40px 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
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
        .header h1 { color: #1e293b; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .section-title { font-size: 14px; font-weight: 600; color: #475569; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .data-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 14px; word-break: break-all; color: #334155; margin-bottom: 15px;}
        .signature-box { background-color: #faf5ff; border: 1px solid #e9d5ff; color: #6b21a8; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 15px; border-radius: 6px; word-break: break-all; margin-bottom: 20px;}
        
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        .alert.success { background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert.danger { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; }
        
        code { background-color: #ffdcdc; color: #b71c1c; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Criptografía Aplicada: Flujo Integral de Firma</p>
    </div>

    <div class="alert <?php echo $status_class; ?>">
        <h3><?php echo $status_title; ?></h3>
        <p><?php echo $status_desc; ?></p>
    </div>

    <div class="section-title">1. Mensaje Original en Claro (Emisor):</div>
    <div class="data-box">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>

    <?php if (!empty($firmaBase64)): ?>
        <div class="section-title">2. Firma Digital Generada (Codificada en Base64 para Transporte):</div>
        <div class="signature-box">
            <?php echo $firmaBase64; ?>
        </div>
        
        <div class="section-title">3. Mecanismo de Validación Criptográfica (Receptor):</div>
        <div class="data-box" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
            • Llave Utilizada: Clave Pública extraída dinámicamente de <strong>keysCert/certout.csr</strong><br>
            • Algoritmo de Hash Asociado: <strong>SHA-256 con Firmado RSA</strong><br>
            • Resultado del Análisis Matemático: <strong>Verificación Exitosa (Código 1)</strong>
        </div>
    <?php endif; ?>

    <div class="footer">
        Flujo de Integridad y Autenticidad de Datos - WAMP Server & PHP Criptografía Asimétrica
    </div>
</div>

</body>
</html>