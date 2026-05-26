@ -1,113 +0,0 @@
🔐 Laboratorio de Criptografía Aplicada con OpenSSL y WampServer

📝 1. Introducción y Arquitectura del Proyecto

Objetivo del Laboratorio

El propósito fundamental de esta práctica de laboratorio es comprender, configurar e implementar flujos criptográficos seguros empleando la extensión nativa OpenSSL en un servidor web local (WampServer). A lo largo de la experiencia se abordan problemas prácticos de confidencialidad, integridad, no repudio y persistencia simulada de transacciones firmadas digitalmente sin necesidad de depender de gestores de bases de datos externas.

Estructura y Distribución de Archivos

Este proyecto utiliza un enfoque estructurado y modular en PHP para interactuar de forma directa con el motor de OpenSSL. La separación de responsabilidades y la organización de carpetas se estructuró de la siguiente manera:
Directorio Raíz (/): Contiene los scripts PHP ejecutables que procesan las peticiones en el servidor Apache y renderizan las interfaces unificadas HTML/CSS.
Carpeta de Llaves (/keys): Almacena las llaves generadas de forma asimétrica (clave privada private_key.pem y clave pública public_key.pem) de forma local en el servidor, además del archivo de firma binaria (signature.dat).
Carpeta de Certificados (/keysCert): Destinada al almacenamiento de la Solicitud de Firma de Certificado (CSR) certout.csr y la clave privada asociada privkey.pem.
Archivo de Configuración (/openssl.cnf): Archivo que define las políticas, algoritmos y extensiones válidas para las llamadas del motor OpenSSL en PHP.

🛠️ 2. Prerrequisitos y Ecosistema de Desarrollo

Para la correcta ejecución y despliegue de los scripts de este laboratorio, se requiere configurar el siguiente ecosistema de desarrollo:
PHP: Versión 8.0 o superior (con la extensión openssl habilitada).
Paquete de Servidor Web Local: WampServer (v3.x o superior) o en su defecto Laragon/XAMPP.
Servidor Web: Apache 2.4.
Editor de Código: Visual Studio Code (Recomendado).
Sistema Operativo: Windows 10 / Windows 11.

⚙️ 3. Configuración del Servidor Web (Activación de OpenSSL)

Para levantar el laboratorio y activar los módulos seguros en el servidor Apache, se ejecutó la siguiente secuencia técnica en el panel de WampServer:
# 1. Activación de la extensión criptográfica en el servidor
WAMP Panel (Click Izquierdo) -> PHP -> PHP Extensions -> Click en "openssl"
# 2. Reiniciar los servicios de WampServer automáticamente
# WampServer recargará el archivo php.ini con la línea extension=openssl activa

🔄 4. Flujo y Secuencia de Pasos del Laboratorio
El laboratorio fue ejecutado secuencialmente a través de los siguientes módulos adaptados para garantizar su total funcionalidad en PHP moderno:

Paso 1: Preparación del Entorno (WAMP)
Activación de la extensión nativa en el servidor Apache a través del panel de control de WampServer para habilitar las funciones criptográficas de PHP en la máquina local.

Paso 2: Generación de Llaves y Requerimiento CSR (firma4.php)
Se corrigieron las rutas de configuración de Windows y se estructuró la variable $dn en un arreglo indexado con los datos de identidad de la Universidad Tecnológica de Panamá (FISC, DIPRENA):

$dn = array(
    "countryName" => "PA",
    "stateOrProvinceName" => "Panama",
    "localityName" => "Panama",
    "organizationName" => "Universidad Tecnológica de Panamá",
    "organizationalUnitName" => "FISC",
    "commonName" => "localhost",
    "emailAddress" => "estudiante@utp.ac.pa"
);
$privkey = openssl_pkey_new();
$csr = openssl_csr_new($dn, $privkey, array('config' => './openssl.cnf'));


Paso 3: Cifrado Simétrico y Autenticación Integrada (FirmarMensaje.php)
Se implementó el flujo Encrypt-then-MAC con AES-256-CBC y HMAC-SHA3-512. Se solucionó el error de tamaño de llave aplicando la función hash() para asegurar que las contraseñas basadas en texto plano de usuario midieran exactamente 32 bytes (256 bits):

$first_key  = hash('sha256', "Irina", true);
$second_key = hash('sha256', "Fong", true);
$method = "aes-256-cbc";


Paso 4: Cifrado y Descifrado Simétrico Estándar (cifrado.php)
Demostración del ciclo completo de confidencialidad usando AES-128-CBC. Se garantizó el uso del parámetro 0 de salida binaria estándar en las funciones de descifrado de OpenSSL para prevenir la aparición de caracteres corruptos (garbage data) en el navegador.

Paso 5: Firma Digital por Lotes en Memoria (diprensaguardaraprobacion.php)
Se procesó un bucle de aprobación de cargos simulando transacciones reales de datos. Para evitar los fallos críticos provocados por la obsolescencia de las funciones mysql_* en PHP moderno, se reemplazó la conexión física de base de datos por un mapeo estructurado de datos dinámicos en un array en memoria:

$mock_aprobaciones = array(
    array('puesto' => 'Director de Finanzas - Control de Presupuesto'),
    array('puesto' => 'Analista Criptográfico - Fiscalización DIPRENA'),
    array('puesto' => 'Supervisor General - Módulo de Auditoría')
);

foreach($mock_aprobaciones as $row){
    $datos = $row['puesto'];
}


Paso 6: Formulario Interactivo Web (encriptacion.php)
Se programó una aplicación web interactiva que solicita al usuario un mensaje y una contraseña mediante un formulario HTML. La aplicación procesa la petición vía POST, normaliza la clave de entrada a exactamente 16 bytes y despliega de forma visual el IV en formato Hexadecimal (bin2hex), el criptograma codificado en Base64 y el mensaje original recuperado.

📁 6. Respaldo de Datos Estructurales

Dado que la información del módulo DIPRENA fue procesada y firmada de forma dinámica en memoria a través de arrays para garantizar la compatibilidad del servidor PHP, se adjunta en el repositorio el archivo de texto estructurado con la definición del esquema conceptual lógico optimizado para albergar firmas digitales y criptogramas binarios codificados en Base64 sin pérdida de longitud.

⚠️ 7. Dificultades y Soluciones

Incompatibilidad Fatal de Funciones de Base de Datos (mysql_*):
Dificultad: Las versiones modernas de PHP (PHP 8.0+) en WAMP eliminaron por completo la extensión antigua MySQL, deteniendo el script del módulo DIPRENA con errores del tipo Call to undefined function mysql_query().
Solución: Se desarrolló un parche lógico de emulación para sustituir la consulta física a la base de datos por una estructura de arreglos locales en memoria. Esto permitió mantener intacto el recorrido secuencial de datos para el estampado de firmas digitales RSA con SHA-256.
Fallo Crítico por Ruta Incorrecta del Archivo openssl.cnf:
Dificultad: El instalador original apuntaba a una ruta estática en el disco C:\openssl\... que no existe en el ecosistema de WampServer, impidiendo que openssl_pkey_new() generara las llaves.
Solución: Se reemplazó la ruta rígida de Windows por una llamada dinámica local ./openssl.cnf en los parámetros de configuración, permitiendo al servidor Apache localizar de forma automática el archivo de directivas en el directorio del laboratorio.
Corrupción de Datos en el Descifrado Simétrico:
Dificultad: Al alterar los parámetros de la función openssl_decrypt() para empaquetarla dentro de la interfaz, se obtenían caracteres corruptos (garbage data) en lugar del texto plano original.
Solución: Se corrigieron los argumentos asegurando que el cuarto parámetro se mantuviera estrictamente como el entero 0 para que el flujo de procesamiento binario recuperara la cadena de texto de forma íntegra.

📌 8. Referencias y Fuentes Consultadas

PHP Manual: Extensión de Funciones de OpenSSL
Guía de Configuración y Servidores de WampServer
Estándar de Seguridad Criptográfica RFC 8017 (RSA Cryptography)

📅 Fecha de Ejecución del Laboratorio
27 de mayo de 2026

🏢 Footer - Datos de Identificación
Este laboratorio ha sido desarrollado por los estudiantes de la Universidad Tecnológica de Panamá:
Nombres: Abraham Alcedo y Traly Amaro
Curso: Desarrollo de Software VII
Instructor del Laboratorio: Irina Fong