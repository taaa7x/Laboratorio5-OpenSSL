<?php
$procesado = false;
$error = "";
$iv_hex = "";
$texto_cifrado = "";
$texto_descifrado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    $clave_usuario = isset($_POST['clave']) ? trim($_POST['clave']) : '';

    if (empty($mensaje) || empty($clave_usuario)) {
        $error = "Todos los campos del formulario son estrictamente obligatorios.";
    } else {
        $clave_normalizada = str_pad(substr($clave_usuario, 0, 16), 16, "\0");
        
        $metodo = "aes-128-cbc";
        $iv_longitud = openssl_cipher_iv_length($metodo);
        $iv_binario = openssl_random_pseudo_bytes($iv_longitud);
        
        $cifrado_crudo = openssl_encrypt($mensaje, $metodo, $clave_normalizada, OPENSSL_RAW_DATA, $iv_binario);
        
        $iv_hex = bin2hex($iv_binario);
        $texto_cifrado = base64_encode($cifrado_crudo);
        
        $texto_descifrado = openssl_decrypt($cifrado_crudo, $metodo, $clave_normalizada, OPENSSL_RAW_DATA, $iv_binario);
        
        $procesado = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTP - Ejercicio Práctico Adicional</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; background-color: #ffffff; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #eaedf1; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #1e293b; font-size: 24px; margin: 0 0 8px 0; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #475569; font-size: 14px; }
        textarea, input[type="text"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 14px; font-family: inherit; }
        textarea { resize: vertical; min-height: 100px; }
        input[type="text"]:focus, textarea:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .btn-submit { background-color: #0284c7; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; width: 100%; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #0369a1; }
        .error-box { background-color: #fef2f2; border-left: 5px solid #ef4444; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 600; color: #475569; margin-top: 25px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 13px; color: #334155; word-break: break-all; }
        .output-box { background-color: #fef8e6; border: 1px solid #fcd34d; color: #78350f; font-family: 'Courier New', Courier, monospace; font-size: 14px; padding: 15px; border-radius: 8px; word-break: break-all; font-weight: bold; }
        .footer { text-align: center; margin-top: 35px; font-size: 12px; color: #94a3b8; border-top: 1px solid #eaedf1; padding-top: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Universidad Tecnológica de Panamá</h1>
        <p>FISC - Desarrollo de Software VII | Cifrado Simétrico Interactivo con Formulario Web</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-box">
            <strong>⚠️ Error de Validación:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="mensaje">Mensaje en Claro (Texto a Proteger):</label>
            <textarea id="mensaje" name="mensaje" required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="clave">Clave Secreta Compartida:</label>
            <input type="text" id="clave" name="clave" required value="<?php echo isset($_POST['clave']) ? htmlspecialchars($_POST['clave']) : ''; ?>">
        </div>

        <button type="submit" class="btn-submit">Procesar Bloque Criptográfico</button>
    </form>

    <?php if ($procesado): ?>
        <div class="section-title">1. Vector de Inicialización (IV) - Formato Hexadecimal [bin2hex]:</div>
        <div class="data-box">
            <?php echo $iv_hex; ?>
        </div>

        <div class="section-title">2. Texto Cifrado Resultante - Representación Base64:</div>
        <div class="data-box" style="background-color: #f1f5f9;">
            <?php echo $texto_cifrado; ?>
        </div>

        <div class="section-title">3. Resultado Final del Descifrado (Ciclo Completo):</div>
        <div class="output-box">
            <?php echo htmlspecialchars($texto_descifrado); ?>
        </div>
    <?php endif; ?>

    <div class="footer">
        Laboratorio OpenSSL - Ejercicio Práctico Adicional (D.5.1)
    </div>
</div>

</body>
</html>