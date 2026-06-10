<?php
// prepare_deploy.php

$sourceDir = __DIR__;
$deployDir = __DIR__ . '/deploy_siteground';

echo "Iniciando preparación de despliegue para SiteGround...\n";

// 1. Limpiar o crear directorio de despliegue
if (file_exists($deployDir)) {
    echo "Limpiando directorio anterior de despliegue...\n";
    deleteDirectory($deployDir);
}
mkdir($deployDir, 0755, true);
mkdir($deployDir . '/proyecto_laravel', 0755, true);
mkdir($deployDir . '/public_html', 0755, true);

// 2. Definir exclusiones
$exclude = [
    '.git',
    '.github',
    '.idea',
    '.vscode',
    'node_modules',
    'vendor',
    'tests',
    'deploy_siteground',
    'public', // Se copia por separado
    'prepare_deploy.php',
    'phpunit.xml',
    '.env', // Evitar copiar el .env local con credenciales locales
];

// 3. Copiar archivos de Laravel a proyecto_laravel
echo "Copiando archivos a proyecto_laravel...\n";
copyRecursive($sourceDir, $deployDir . '/proyecto_laravel', $exclude);

// 4. Copiar carpeta public a public_html
echo "Copiando carpeta pública a public_html...\n";
copyRecursive($sourceDir . '/public', $deployDir . '/public_html');

// 5. Modificar index.php para apuntar a la nueva ruta
echo "Ajustando rutas en public_html/index.php...\n";
$indexPath = $deployDir . '/public_html/index.php';
if (file_exists($indexPath)) {
    $content = file_get_contents($indexPath);
    
    // Cambiar paths relativos
    $content = str_replace(
        "__DIR__.'/../storage",
        "__DIR__.'/../proyecto_laravel/storage",
        $content
    );
    $content = str_replace(
        "__DIR__.'/../vendor",
        "__DIR__.'/../proyecto_laravel/vendor",
        $content
    );
    $content = str_replace(
        "__DIR__.'/../bootstrap",
        "__DIR__.'/../proyecto_laravel/bootstrap",
        $content
    );
    
    file_put_contents($indexPath, $content);
}

// 6. Crear un template de .env para producción (.env.production)
echo "Creando plantilla .env.production...\n";
$envExample = $sourceDir . '/.env.example';
if (file_exists($envExample)) {
    $envContent = file_get_contents($envExample);
    // Personalizar un poco para producción
    $envContent = str_replace('APP_ENV=local', 'APP_ENV=production', $envContent);
    $envContent = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $envContent);
    file_put_contents($deployDir . '/proyecto_laravel/.env.production', $envContent);
}

echo "\n¡Listo! Todo ha sido preparado en: $deployDir\n";
echo "Instrucciones:\n";
echo "1. Sube el contenido de 'proyecto_laravel' a tu carpeta fuera de public_html en SiteGround.\n";
echo "2. Sube el contenido de 'public_html' dentro de la carpeta public_html en SiteGround.\n";
echo "3. Renombra '.env.production' a '.env' en el servidor y edita los datos de la base de datos MySQL.\n";

function copyRecursive($source, $dest, $exclude = []) {
    if (!is_dir($source)) return;
    if (!file_exists($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = dir($source);
    while (false !== ($entry = $dir->read())) {
        if ($entry == '.' || $entry == '..') continue;
        if (in_array($entry, $exclude)) continue;
        
        $sourcePath = $source . '/' . $entry;
        $destPath = $dest . '/' . $entry;
        
        if (is_dir($sourcePath)) {
            copyRecursive($sourcePath, $destPath, $exclude);
        } else {
            copy($sourcePath, $destPath);
        }
    }
    $dir->close();
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}
