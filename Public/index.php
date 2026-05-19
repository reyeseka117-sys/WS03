<?php 
    define('BASE_PATH', dirname(__DIR__));

    require __DIR__ . '/../vendor/autoload.php';
    require '../helpers.php';

    use Framework\Router;
    use Framework\Session;

    $router = new Router();
    $routes = require basePath('routes.php');

    Session::start();

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST' && isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    }

    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($basePath !== '' && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }
    $uri = $uri === '' ? '/' : $uri;

    $router->route($uri, $method);

?>