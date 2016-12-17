<?php

$root = realpath(__DIR__ . '/../../../../../');
if (!defined('root')) {
    define('root', $root);
}
if (!defined('env_name')) {
    define('env_name', 'test');
}

// load cms bindings
if (!file_exists($root . '/Loader/Autoload.php')) {
    return;
}

// initialize cms app entry-point with smooth bindings
define('env_no_uri', true);
define('env_type', 'html');
class App extends Ffcms\Core\App {}
function __($text, array $params = []) {
    return \App::$Translate->translate($text, $params);
}

try {
    $app = \App::factory();
    $app->init();
} catch (Exception $e) {
    die('PHPUnit bootstrap ffcms engine error');
}