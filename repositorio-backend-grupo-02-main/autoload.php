<?php
/**
 * Carrega automaticamente classes do projeto.
 * Compatível com classes sem namespace e com os namespaces App\Controllers e Services.
 */
spl_autoload_register(static function (string $className): void {
    $root = __DIR__;

    $namespaceMap = [
        'App\\Controllers\\' => $root . '/controllers/',
        'Services\\' => $root . '/services/',
    ];

    foreach ($namespaceMap as $namespace => $directory) {
        if (strncmp($className, $namespace, strlen($namespace)) === 0) {
            $relativeClass = substr($className, strlen($namespace));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';

            if (is_file($file)) {
                require_once $file;
            }

            return;
        }
    }

    $directories = ['controllers', 'core', 'models', 'DAO'];

    foreach ($directories as $directory) {
        $file = $root . '/' . $directory . '/' . $className . '.php';

        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
