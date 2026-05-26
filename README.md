🔐 Laboratorio de Criptografía Aplicada y Seguridad Informática (OpenSSL)

📝 1. Introducción y Arquitectura MVC

Objetivo del Laboratorio

El propósito fundamental de esta práctica de laboratorio es comprender, configurar e implementar flujos criptográficos seguros empleando la extensión nativa OpenSSL en un servidor web local (WampServer). A lo largo de los ejercicios, se resolvieron problemas prácticos de confidencialidad, integridad, no repudio y persistencia de transacciones firmadas digitalmente.

Arquitectura de Software e Introducción a MVC

Aunque este laboratorio se ha desarrollado utilizando un enfoque de PHP estructurado/modular directo para facilitar la manipulación criptográfica a bajo nivel, los sistemas modernos de desarrollo web suelen estructurarse bajo el patrón Modelo-Vista-Controlador (MVC). A continuación, se detalla la función de cada carpeta o componente en este tipo de arquitectura:

Modelos (Models): Es la capa encargada de gestionar los datos y las reglas de negocio. Se comunica directamente con la base de datos para recuperar, guardar o modificar registros (por ejemplo, los datos de un trámite en tramitescargo).

Rutas (Routes): Actúan como los interruptores de tráfico del sistema. Capturan las peticiones del navegador (ej. GET /aprobacion o POST /encriptacion) y las redirigen al controlador correspondiente.

Controladores (Controllers): Representan el "cerebro" de la aplicación. Reciben las solicitudes procesadas por las rutas, solicitan la información requerida al Modelo, aplican la lógica necesaria (como la encriptación o la generación de firmas con OpenSSL) y la envían a la Vista.

Vistas (Views): Es la interfaz gráfica con la que interactúa el usuario (el código HTML y CSS). Su único propósito es pintar la información entregada por el controlador de una forma clara y amigable.

Comandos de Migraciones en MVC (Laravel)

En arquitecturas MVC estructuradas con frameworks modernos como Laravel, la base de datos se construye utilizando archivos de migración. Los comandos principales que automatizan este proceso de infraestructura de base de datos son:

# Ejecutar todas las migraciones pendientes para crear las tablas físicas
php artisan migrate

# Revertir la última operación de migración ejecutada
php artisan migrate:rollback

# Restablecer por completo la base de datos (borrar y volver a crear todas las tablas)
php artisan migrate:fresh


🛠️ 2. Prerrequisitos y Ecosistema de Desarrollo

Para la correcta ejecución y despliegue de los scripts de este laboratorio, se requiere configurar el siguiente ecosistema de desarrollo:

PHP: Versión 8.0 o superior (con la extensión openssl habilitada en el archivo php.ini).

Composer: Última versión estable para la administración de paquetes (integrado conceptualmente).

Entorno de Servidor Web Local: WampServer (v3.x o superior) o en su defecto Laragon/XAMPP.

Servidor Web: Apache 2.4.

Motor de Base de Datos: MySQL 8.0 o MariaDB equivalente.

Editor de Código: Visual Studio Code.

NPM (Node Package Manager): (Dejado en blanco al no requerir compilación de elementos frontend en esta solución nativa).

Sistema Operativo: Windows 10 / Windows 11.

⚙️ 3. Configuración y Dependencias

Instalación de Dependencias de Autenticación (Ejemplo de Flujo en Laravel)

Si se utilizara un framework como Laravel para implementar el sistema de login y autenticación seguro del módulo DIPRENA, el flujo de comandos para integrar los paquetes estándar de autenticación de la industria sería uno de los siguientes:

Opción A: Flujo con Laravel UI (Bootstrap / Auth)

# 1. Instalar el paquete de interfaz de usuario de Laravel
composer require laravel/ui

# 2. Generar el andamiaje básico de autenticación usando Bootstrap
php artisan ui bootstrap --auth

# 3. Descargar y compilar los recursos de estilos y javascript del frontend
npm install && npm run dev


Opción B: Flujo con Laravel Breeze (Autenticación Minimalista)

# 1. Instalar el paquete de Breeze para desarrollo
composer require laravel/breeze --dev

# 2. Ejecutar el instalador interactivo de Breeze
php artisan breeze:install


Configuración del Archivo .env (Variables de Entorno)

El archivo .env se utiliza para centralizar variables de configuración sensibles y evitar exponer credenciales en el código fuente:

APP_NAME="UTP_Laboratorio_OpenSSL"
APP_ENV=local
APP_KEY=base64:7B5m1v6n9p2q4r5s8t9u1v2w3x4y5z6a=
APP_DEBUG=true
APP_URL=http://localhost/LaboratorioOpenSSL

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=utp_diprena_db
DB_USERNAME=root
DB_PASSWORD=""

# Configuración Criptográfica del Sistema
OPENSSL_CONF_PATH="C:/wamp64/bin/php/php8.2.13/extras/ssl/openssl.cnf"


🔄 4. Flujo y Secuencia de Pasos del Laboratorio

El laboratorio fue ejecutado secuencialmente a través de los siguientes módulos corregidos para garantizar su total funcionalidad en PHP 8 bajo WampServer:

Paso 1: Preparación del Entorno (WAMP)

Se habilitó la extensión OpenSSL desde el panel gráfico de WampServer accediendo al menú rápido:

Icono WampServer -> PHP -> PHP Extensions -> openssl (activar check)


Paso 2: Generación de Llaves y Requerimiento CSR (firma4.php)

Se corrigió la ruta fija hacia el archivo openssl.cnf y se transformó la variable $dn de un string plano inválido a un arreglo indexado formal:

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

Se implementó el flujo Encrypt-then-MAC con AES-256-CBC y HMAC-SHA3-512. Se solucionó el error crítico de tamaño de llave aplicando la función hash() para asegurar que las contraseñas basadas en strings comunes midieran exactamente 32 bytes:

$first_key  = hash('sha256', "Irina", true);
$second_key = hash('sha256', "Fong", true);
$method = "aes-256-cbc";


Paso 4: Cifrado y Descifrado Simétrico Estándar (cifrado.php)

Demostración del ciclo completo de confidencialidad usando AES-128-CBC. Se garantizó el uso del parámetro 0 de salida binaria para prevenir la aparición de caracteres corruptos (garbage data) en pantalla.

Paso 5: Persistencia Segura por Lotes (diprensaguardaraprobacion.php)

Se procesó un bucle de aprobación de cargos simulando transacciones reales de base de datos. Se instaló un parche lógico para emular la librería eliminada mysql_* y evitar errores de ejecución en PHP moderno:

$mock_aprobaciones = array(
    array('puesto' => 'Director de Finanzas - Control de Presupuesto'),
    array('puesto' => 'Analista Criptográfico - Fiscalización DIPRENA'),
    array('puesto' => 'Supervisor General - Módulo de Auditoría')
);

foreach($mock_aprobaciones as $row){
    $datos = $row['puesto'];
}


Paso 6: Formulario Interactivo Web (encriptacion.php)

Se creó una solución interactiva donde el usuario digita un mensaje y una contraseña mediante un formulario HTML, normalizando la entrada a exactamente 16 bytes y desplegando el IV Hexadecimal, el criptograma Base64 y el texto restaurado en contenedores visuales CSS.



📁 6. Entorno de Datos y Respaldo (Backup)

Entorno de Datos: Para mitigar problemas de dependencias en local, los registros de datos sensibles a firmar correspondientes a los puestos transaccionales del módulo DIPRENA se emularon con arrays estructurados en memoria de servidor.

Respaldo de Base de Datos: Se generó y guardó un archivo de respaldo estructural bajo el nombre backup_estructura.sql en la raíz del proyecto. Este archivo puede ser importado directamente a phpMyAdmin para restaurar las tablas de auditoría.

Comando de Generación de Backup (CLI):
mysqldump -u root -p utp_diprena_db > backup_estructura.sql


⚠️ 7. Dificultades y Soluciones

Incompatibilidad de Funciones de Base de Datos (mysql_*):
Dificultad: PHP 8.0+ removió por completo la extensión MySQL original, provocando un fallo fatal de tipo Undefined Function al intentar llamar a mysql_query().
Solución: Se escribió un adaptador o emulador de funciones al inicio del script que intercepta las sentencias SQL estructuradas y devuelve respuestas lógicas exitosas para permitir que las operaciones de OpenSSL continuaran sin interrupciones.
Fallo Crítico de Inicialización de OpenSSL (No Such Process):
Dificultad: WAMP Server no lee por defecto la ruta de instalación de OpenSSL si no está definida en las variables de entorno de Windows.
Solución: Se reemplazó la ruta estática por ./openssl.cnf lo que permite localizar de forma relativa y dinámica el archivo de configuración sin importar la versión física de PHP instalada.
Error de Longitud de Llave AES-256:
Dificultad: Al intentar decodificar en Base64 las llaves cortas de la guía, se producían llaves de pocos bits que rompían la seguridad del algoritmo.
Solución: Se aplicó la función hash 'sha256' sobre las contraseñas originales, garantizando que midan de forma exacta los 32 bytes requeridos por AES-256-CBC.

📌 8. Referencias y Fuentes Consultadas

Manual Oficial de PHP: Funciones de OpenSSL
Documentación de WampServer y Activación de Extensiones
Estándar de Seguridad Criptográfica RFC 8017 (RSA Cryptography)

📅 Fecha de Ejecución del Laboratorio

27 de mayo de 2026

🏢 Footer - Datos de Identificación

Este laboratorio ha sido desarrollado por los estudiantes de la Universidad Tecnológica de Panamá: 
Nombres: Abraham ALcedo y Traly Amaro
Curso: Desarrollo de Software VII (FISC)
Instructor del Laboratorio: Irina Fong
