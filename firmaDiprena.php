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
// ---------------------------------------------------------

// 🌟 PARCHE DE COMPATIBILIDAD PARA EVITAR EL FATAL ERROR DE MYSQL 🌟
if (!function_exists('mysql_query')) {
    function mysql_query($query) {
        // Simulamos que la consulta se ejecutó de manera exitosa en la base de datos
        return true; 
    }
}
// -------------------------------------------------------------------------

// --- SIMULACIÓN DE DATOS DEL SISTEMA (Para que funcione independiente) ---
if (!isset($datos)) {
    $datos = "Trámite Oficial de Cargo - DIPRENA - Documento ID: 88319";
}
if (!isset($idCargoActual)) {
    $idCargoActual = "_45"; // ID de ejemplo para el archivo .dat
}
if (!isset($tbCargo)) {
    $tbCargo = "cargos_publicos";
}
// -------------------------------------------------------------------------

// Variables de interfaz gráfica
$error_msg = "";
$status_class = "";
$status_title = "";
$status_desc = "";
$firmaBase64 = "";
$verificacion_resultado = "";
$verificacion_ok = false;

// VERIFICACIÓN PREVIA: Asegurar que existan las llaves en la carpeta 'keys'
if (!is_dir('keys')) {
    mkdir('keys', 0777, true);
}

// Generar par de llaves express en 'keys' si no existen para evitar fallos
if (!file_exists('keys/private_key.pem') || !file_exists('keys/public_key.pem')) {
    $configArgs = array('config' => OPEN_SSL_CONF_PATH, 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA);
    $resKey = openssl_pkey_new($configArgs);
    openssl_pkey_export($resKey, $privKeyExpress, null, $configArgs);
    $pubKeyExpress = openssl_pkey_get_details($resKey)['key'];
    file_put_contents('keys/private_key.pem', $privKeyExpress);
    file_put_contents('keys/public_key.pem', $pubKeyExpress);
}

// --- LOGICA ORIGINAL DE LA PROFESORA ---

$firma = "";

// 1. Buscar strings de llaves en disco
$privateKeyPem = file_get_contents('keys/private_key.pem');
$publicKeyPem = file_get_contents('keys/public_key.pem');

// 2. Obtener la clave privada como resource
$resourcePrivateKey = openssl_get_privatekey($privateKeyPem);

// 3. Crear la firma dentro de la variable $firma (Pasada por referencia)
if (!openssl_sign($datos, $firma, $resourcePrivateKey, OPENSSL_ALGO_SHA256)) {
    $error_msg = "Error en openssl_sign: " . openssl_error_string();
} else {
    
    // 4. Guardar la firma binaria en disco:
    $archivo = 'keys/signature'.$idCargoActual.'.dat';
    file_put_contents($archivo, $firma);
    
    if (is_dir('../Bethel/keys')) {
        file_put_contents('../Bethel/'.$archivo, $firma);
    }

    // Convertir a Base64 para visualización estética
    $firmaBase64 = base64_encode($firma);

    // 5. Consultas SQL Originales (Llaman a nuestra función emulada sin reventar el código)
    $update_str = "idCargo ='$idCargoActual'";
    $consulta1 = "UPDATE $tbCargo SET firma = '$archivo' WHERE $update_str";
    mysql_query($consulta1);
    
    $consulta2 = "UPDATE tramitescargo SET firma = '$archivo' WHERE id ='$idCargoActual'";
    mysql_query($consulta2);

    // 6. Comprobar y verificar la firma con la clave pública
    $comp = openssl_verify($datos, $firma, $publicKeyPem, 'sha256WithRSAEncryption');
    
    if ($comp === 1) {
        $verificacion_ok = true;
        $verificacion_resultado = "la firma es valida y los datos son confiables";
        $status_class = "success";
        $status_title = "✅ Sello de Integridad Validado (DIPRENA)";
        $status_desc = "El trámite ha sido firmado digitalmente y verificado exitosamente. La emulación de persistencia SQL procesó las sentencias de actualización de forma correcta.";
    } else {
        $verificacion_resultado = "la firma es invalida y/o los datos fueron alterados";
        $status_class = "danger";
        $status_title = "❌ Fallo de Verificación";
        $status_desc = "La firma del cargo no coincide matemáticamente con los datos.";
    }
}

if (!empty($error_msg)) {
    $status_class = "danger";
    $status_title = "Error Criptográfico";
    $status_desc = $error_msg;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema DIPRENA - Firma de Trámites y Cargos</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #eaedf1; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #0f172a; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 600; color: #475569; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 14px; word-break: break-all; color: #334155; }
        .sql-box { background-color: #fef3c7; border: 1px solid #fde68a; color: #78350f; font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 12px; border-radius: 6px; margin-bottom: 10px; }
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        .alert.success { background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert.danger { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; }
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Módulo DIPRENA: Firma y Persistencia Seguro</p>
    </div>

    <div class="alert <?php echo $status_class; ?>">
        <h3><?php echo $status_title; ?></h3>
        <p><?php echo $status_desc; ?></p>
    </div>

    <div class="section-title">Datos del Trámite a Firmar ($datos):</div>
    <div class="data-box" style="margin-bottom: 15px;">
        <?php echo htmlspecialchars($datos); ?>
    </div>

    <div class="section-title">Firma Binaria Almacenada en Disco ($archivo):</div>
    <div class="data-box" style="background-color: #faf5ff; border-color: #e9d5ff; color: #6b21a8; margin-bottom: 15px;">
        Ruta: <strong><?php echo $archivo; ?></strong><br><br>
        Contenido Imprimible (Base64):<br>
        <?php echo $firmaBase64; ?>
    </div>

    <div class="section-title">Sentencias SQL Interceptadas (Compatibilidad PHP 8):</div>
    <div class="sql-box">
        🔎 <?php echo htmlspecialchars($consulta1); ?>
    </div>
    <div class="sql-box">
        🔎 <?php echo htmlspecialchars($consulta2); ?>
    </div>

    <div class="section-title">Resultado por Pantalla Exigido por la Guía (Echo original):</div>
    <div class="data-box" style="background-color: #f1f5f9; font-weight: bold; color: #0f172a;">
        la firma es: <?php echo substr($firmaBase64, 0, 25); ?>... <br>
        Resultado verificación: <?php echo $verificacion_resultado; ?>
    </div>

    <div class="footer">
        Auditoría de Integridad de Datos - Seguridad en PHP Avanzado
    </div>
</div>

</body>
</html>