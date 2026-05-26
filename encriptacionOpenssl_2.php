<?php
// --- CORRECCIÓN DE LLAVES COMPATIBLES CON AES-256 (32 Bytes) ---
// Usamos hash para asegurar que las llaves tengan el tamaño exacto exigido por el algoritmo
$first_key  = hash('sha256', "Irina", true); // Llave de cifrado (32 bytes)
$second_key = hash('sha256', "Fong", true);  // Llave de autenticación HMAC (32 bytes)
    
$method = "aes-256-cbc";    
$iv_length = openssl_cipher_iv_length($method);
$iv = openssl_random_pseudo_bytes($iv_length);
        
$texto_original = "Hola como estas";

// 1. Cifrado simétrico del mensaje
$first_encrypted = openssl_encrypt(
    $texto_original,
    $method,
    $first_key, 
    OPENSSL_RAW_DATA,
    $iv
);    

// 2. Generación del código de autenticación HMAC (Encrypt-then-MAC) usando SHA3-512
$second_encrypted = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
            
// 3. Empaquetado y codificación final de la carga útil
$output = base64_encode($iv . $second_encrypted . $first_encrypted);  
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio OpenSSL - Cifrado Autenticado</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #eaedf1; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #1e293b; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 600; color: #475569; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; background-color: #f0fdf4; border-left: 5px solid #22c55e; color: #166534; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        
        .data-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #334155; word-break: break-all; }
        .output-box { background-color: #fef8e6; border: 1px solid #fcd34d; color: #78350f; font-family: 'Courier New', Courier, monospace; font-size: 14px; padding: 15px; border-radius: 8px; word-break: break-all; font-weight: bold; }
        
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Cifrado Simétrico Avanzado y Autenticación HMAC</p>
    </div>

    <div class="alert">
        <h3>🔒 Cifrado e Integridad Asegurados Exitosamente</h3>
        <p>El mensaje ha sido protegido utilizando un esquema híbrido de confidencialidad (AES-256-CBC) y firma de datos de un solo sentido (HMAC-SHA3-512) bajo las directrices del laboratorio.</p>
    </div>

    <div class="section-title">Texto Plano de Entrada:</div>
    <div class="data-box" style="margin-bottom: 15px;">
        "<?php echo htmlspecialchars($texto_original); ?>"
    </div>

    <div class="section-title">Detalles de la Carga Útil Criptográfica:</div>
    <div class="data-box" style="background-color: #f1f5f9; margin-bottom: 15px; line-height: 1.6;">
        🔹 <strong>Algoritmo Base:</strong> <?php echo $method; ?><br>
        🔹 <strong>Tamaño del IV generado:</strong> <?php echo $iv_length; ?> bytes<br>
        🔹 <strong>Algoritmo MAC de verificación:</strong> HMAC-SHA3-512 (Clave: Fong)
    </div>

    <div class="section-title">Salida del Script Exigida (Echo original en Base64):</div>
    <div class="output-box">
        <?php echo $output; ?>
    </div>

    <div class="footer">
        Laboratorio OpenSSL - Protección de Datos en Tránsito y Reposo
    </div>
</div>

</body>
</html>