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

define("OPEN_SSL_CONF_PATH", $configPath);
define("OPEN_SSL_CERT_DAYS_VALID", 365);

$configArgs = array(
    'config' => OPEN_SSL_CONF_PATH,
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
// ---------------------------------------------------------

// Datos de Identidad del Certificado (Distinguished Name)
$dn = array (
    "countryName" => "PA",
    "localityName" => "PA-8",
    "organizationName" => "MEF",
    "commonName" => "Irina Fong",
    "emailAddress" => "dreamsweb7@gmail.com"
);

// Variables de estado para la interfaz gráfica
$error_msg = "";
$status_class = "";
$status_title = "";
$status_desc = "";
$certout = "";
$proceso_exitoso = false;

// 1. Generar el par de claves asimétricas
$resourceNewKeyPair = openssl_pkey_new($configArgs);
if (!$resourceNewKeyPair) {
    $error_msg = "Error al generar la clave (openssl_pkey_new): " . openssl_error_string();
} else {
    $details = openssl_pkey_get_details($resourceNewKeyPair);
    $publicKeyPem = $details['key'];

    // 2. Exportar la clave privada
    if (!openssl_pkey_export($resourceNewKeyPair, $privkey, null, $configArgs)) {
        $error_msg = "Error al exportar la clave privada (openssl_pkey_export): " . openssl_error_string();
    } else {
        
        // 3. Crear la CSR (Certificate Signing Request) usando el recurso de la llave
        $csr = openssl_csr_new($dn, $resourceNewKeyPair, $configArgs);
        if (!$csr) {
            $error_msg = "Error al crear la CSR (openssl_csr_new): " . openssl_error_string();
        } else {
            
            // 4. Firmar el certificado digital (Autofirmado por 365 días)
            $sscert = openssl_csr_sign($csr, null, $resourceNewKeyPair, OPEN_SSL_CERT_DAYS_VALID, $configArgs);
            if (!$sscert) {
                $error_msg = "Error al firmar la CSR (openssl_csr_sign): " . openssl_error_string();
            } else {
                
                // 5. Exportar el certificado X.509 final a formato PEM (Texto)
                openssl_x509_export($sscert, $certout);
                
                // Asegurar de forma automática que exista la carpeta 'keysCert'
                if (!is_dir('keysCert')) {
                    mkdir('keysCert', 0777, true);
                }

                // Guardar las evidencias en el disco
                file_put_contents('keysCert/certout.csr', $certout);
                file_put_contents('keysCert/privkey.pem', $privkey);
                
                $proceso_exitoso = true;
                $status_class = "success";
                $status_title = "¡Certificado Digital X.509 Generado!";
                $status_desc = "La Entidad de Certificación interna ha procesado la CSR y emitido el certificado autofirmado con validez de 365 días de forma exitosa.";
            }
        }
    }
}

if (!empty($error_msg)) {
    $status_class = "danger";
    $status_title = "Error del Sistema Criptográfico";
    $status_desc = $error_msg;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio #5 OpenSSL - Certificado Digital</title>
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
        .section-title { font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Tabla de Identidad */
        .dn-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 14px; }
        .dn-table th, .dn-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .dn-table th { background-color: #f8fafc; color: #475569; font-weight: 600; width: 35%; }
        
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        .alert.success { background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert.danger { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; }
        
        .cert-box { background-color: #1e293b; color: #38bdf8; border-radius: 8px; padding: 20px; font-family: 'Courier New', Courier, monospace; font-size: 12px; overflow-x: auto; white-space: pre-wrap; word-break: break-all; max-height: 300px; box-shadow: inset 0 2px 8px rgba(0,0,0,0.5); margin-bottom: 25px; }
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
        <p>FISC - Desarrollo de Software VII | Emisión de Certificado Autofirmado X.509</p>
    </div>

    <div class="section-title">Datos del Sujeto (Distinguished Name - DN):</div>
    <table class="dn-table">
        <tr><th>País (countryName):</th><td><?php echo $dn['countryName']; ?> (Panamá)</td></tr>
        <tr><th>Localidad (localityName):</th><td><?php echo $dn['localityName']; ?></td></tr>
        <tr><th>Organización (organizationName):</th><td><?php echo $dn['organizationName']; ?></td></tr>
        <tr><th>Nombre Común (commonName):</th><td><strong><?php echo $dn['commonName']; ?></strong></td></tr>
        <tr><th>Correo Electrónico (emailAddress):</th><td><?php echo $dn['emailAddress']; ?></td></tr>
    </table>

    <div class="alert <?php echo $status_class; ?>">
        <h3><?php echo $status_title; ?></h3>
        <p><?php echo $status_desc; ?></p>
    </div>

    <?php if ($proceso_exitoso): ?>
        <div class="section-title">Certificado X.509 en Formato PEM (certout):</div>
        <div class="cert-box"><?php echo htmlspecialchars($certout); ?></div>

        <div class="section-title">Archivos Almacenados en el Servidor WAMP:</div>
        <ul class="file-list">
            <li class="file-item">
                <span class="file-icon">📜</span> 
                <span><strong>keysCert/certout.csr</strong> (Certificado Final X.509 emitido)</span>
            </li>
            <li class="file-item">
                <span class="file-icon">🔑</span> 
                <span><strong>keysCert/privkey.pem</strong> (Clave privada del certificado)</span>
            </li>
        </ul>
    <?php endif; ?>

    <div class="footer">
        Infraestructura de Clave Pública (PKI) Local - WAMP Server & OpenSSL
    </div>
</div>

</body>
</html>