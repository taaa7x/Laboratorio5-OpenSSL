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

// Variables para el control de la interfaz gráfica
$error_msg = "";
$status_class = "";
$status_title = "";
$status_desc = "";
$csrTexto = "";

// Configuración de los parámetros (Usando la ruta corregida para WAMP)
$config = array(
    'config' => $configPath,
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);

// Datos de Identidad Requeridos para el Certificado (Distinguished Name)
// Corregido: openssl_csr_new exige un array con esta estructura en lugar de un string simple.
$dn = array(
    "countryName" => "PA",                                // País
    "stateOrProvinceName" => "Panama",                    // Provincia
    "localityName" => "Ancon",                            // Localidad
    "organizationName" => "Universidad Tecnologica de Panama", // Organización
    "organizationalUnitName" => "FISC",                   // Facultad / Unidad
    "commonName" => "DIPRENA S.A.",                        // Nombre del Dominio o Sistema
    "emailAddress" => "estudiante.utp@utp.ac.pa"          // Correo institucional
);

// 1. Generar la llave privada necesaria para firmar el requerimiento CSR
$pkey = openssl_pkey_new($config);

if (!$pkey) {
    $error_msg = "Error al generar la clave privada interna para el CSR: " . openssl_error_string();
} else {
    // 2. Generar el CSR (Certificate Signing Request) usando los datos de identidad ($dn) y la llave
    $csr = openssl_csr_new($dn, $pkey, $config);

    if (!$csr) {
        $error_msg = "Error crítico al compilar el CSR. Detalles: " . openssl_error_string();
    } else {
        // 3. Exportar el CSR creado en memoria a una variable de texto plano (Formato PEM legible)
        openssl_csr_export($csr, $csrTexto);

        // 4. Guardar opcionalmente el requerimiento en un archivo físico .csr
        file_put_contents('solicitud_identidad.csr', $csrTexto);

        // Configurar estado exitoso
        $status_class = "success";
        $status_title = "📜 Requerimiento CSR Compilado con Éxito";
        $status_desc = "La solicitud de firma de certificado (CSR) ha sido estructurada matemáticamente usando criptografía asimétrica y ligada a los datos institucionales de la UTP.";
    }
}

if (!empty($error_msg)) {
    $status_class = "danger";
    $status_title = "❌ Error en Validación de Identidad (CSR)";
    $status_desc = $error_msg;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio OpenSSL - Generación de CSR</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #eaedf1; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #1e293b; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 600; color: #475569; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        .alert.success { background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert.danger { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; }
        
        .data-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #334155; }
        .csr-box { background-color: #f0fdfa; border: 1px solid #99f6e4; color: #115e59; font-family: 'Courier New', Courier, monospace; font-size: 12px; padding: 15px; border-radius: 8px; white-space: pre-wrap; word-break: break-all; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 14px; }
        table th, table td { padding: 10px; border: 1px solid #e2e8f0; text-align: left; }
        table th { background-color: #f1f5f9; color: #475569; }
        
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Infraestructura Pública: Certificate Signing Request (CSR)</p>
    </div>

    <div class="alert <?php echo $status_class; ?>">
        <h3><?php echo $status_title; ?></h3>
        <p><?php echo $status_desc; ?></p>
    </div>

    <?php if (empty($error_msg)): ?>
        <div class="section-title">📋 Datos de Identidad Incorporados (DN):</div>
        <table>
            <tr><th>Campo Criptográfico</th><th>Valor Asignado</th></tr>
            <?php foreach($dn as $key => $value): ?>
                <tr><td><strong><?php echo $key; ?></strong></td><td><?php echo htmlspecialchars($value); ?></td></tr>
            <?php endforeach; ?>
        </table>

        <div class="section-title">📜 Bloque de Requerimiento CSR Exportado:</div>
        <div class="csr-box">
            <?php echo htmlspecialchars($csrTexto); ?>
        </div>
        <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Archivo guardado automáticamente: <code>solicitud_identidad.csr</code></p>
    <?php endif; ?>

    <div class="footer">
        Módulo de Certificación Segura - Servidor Local WAMP
    </div>
</div>

</body>
</html>