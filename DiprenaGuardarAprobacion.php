<?php
/* Con openssl_encrypt() / openssl_decrypt() */

$clave = "clave123456789012"; // 16 caracteres para AES-128
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("AES-128-CBC"));

$texto = "Mensaje secreto";


$cifrado = openssl_encrypt($texto, "AES-128-CBC", $clave, 0, $iv);

$descifrado = openssl_decrypt($cifrado, "AES-128-CBC", $clave, 0, $iv);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio OpenSSL - Cifrado Simétrico</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #eaedf1; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #1e293b; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 600; color: #475569; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .alert { padding: 20px; border-radius: 8px; margin-bottom: 25px; background-color: #ecfdf5; border-left: 5px solid #10b981; color: #065f46; }
        .alert h3 { margin: 0 0 8px 0; font-size: 18px; }
        .alert p { margin: 0; font-size: 14px; line-height: 1.5; }
        
        .data-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #334155; margin-bottom: 15px; word-break: break-all; }
        .output-box { background-color: #f1f5f9; border: 1px solid #cbd5e1; color: #1e293b; font-family: 'Courier New', Courier, monospace; font-size: 14px; padding: 15px; border-radius: 8px; word-break: break-all; }
        
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Cifrado y Descifrado Simétrico (AES-128-CBC)</p>
    </div>

    <div class="alert">
        <h3>🔒 Proceso Criptográfico Completado</h3>
        <p>Se ha verificado el ciclo completo de la información: transformación de texto plano a criptograma y su posterior reversión exacta empleando la misma clave secreta.</p>
    </div>

    <div class="section-title">Texto Plano Original:</div>
    <div class="data-box">
        "<?php echo htmlspecialchars($texto); ?>"
    </div>

    <div class="section-title">Salidas Impresas del Laboratorio:</div>
    <div class="output-box">
        <?php 
        // Salidas idénticas a los echos solicitados en la guía
        echo "el resultado del cifrado es el siguiente ".$cifrado."<br>";
        echo "el resultado del descifrado es: ".$descifrado."<br>";
        ?>
    </div>

    <div class="footer">
        Laboratorio OpenSSL - Demostración de Confidencialidad Simétrica
    </div>
</div>

</body>
</html>