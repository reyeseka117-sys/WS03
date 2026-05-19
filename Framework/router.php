<?php
namespace Framework;

//   $routes = require basePath('routes.php');

//    if (array_key_exists($uri, $routes)) {
//         require basePath($routes[$uri]);
//     } else {
//         http_response_code(404);
//         require basePath($routes['404']);
//     }

class Router {  
    protected $routes = [];

    public function registerRoute($method, $uri, $controller, $middleware = null) {
            $this->routes []= [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'middleware' => $middleware
        ];
    }
    /**
     * add get route 
     * 
     * @param string $uri
     * @param string $controller
     * return void
     * 
     */

    public function get(string $uri, string $controller, $middleware = null) {
    $this->registerRoute('GET', $uri, $controller, $middleware);
    }

        /**
     * add post route 
     * 
     * @param string $uri
     * @param string $controller
     * return void
     * 
     */

    public function post(string $uri, string $controller, $middleware = null) {
        $this->registerRoute('POST', $uri, $controller, $middleware);
    }

        /**
     * add put route 
     * 
     * @param string $uri
     * @param string $controller
     * return void
     * 
     */

    public function put(string $uri, string $controller, $middleware = null) {
        $this->registerRoute('PUT', $uri, $controller, $middleware);
    }

        /**
     * add delete route 
     * 
     * @param string $uri
     * @param string $controller
     * return void
     * 
     */

    public function delete(string $uri, string $controller, $middleware = null) {
        $this->registerRoute('DELETE', $uri, $controller, $middleware);
    }

            /**
     * load error page  
     * 
     * @param int $httpCode
     * return void
     * 
     */

    public function error(int $httpCode = 404) {
        http_response_code($httpCode);
        loadView("error/{$httpCode}");
        exit;
    }

    /**
     * Route the request 
     * 
     * @param string $uri
     * @param string $method
     * return void
     * 
     */
    public function route(string $uri, string $method) {
        foreach($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $routePattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $route['uri']);
            $routeRegex = '#^' . $routePattern . '$#';
            $params = [];

            if (preg_match($routeRegex, $uri, $matches)) {
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                if (!empty($route['middleware'])) {
                    $middlewareItems = is_array($route['middleware'])
                        ? $route['middleware']
                        : [$route['middleware']];

                    $middlewareHandler = new \Framework\Middleware\Authorize();
                    foreach ($middlewareItems as $middleware) {
                        $middlewareHandler->handle($middleware);
                    }
                }

                $this->dispatch($route['controller'], $params);
                return;
            }
        }

        $this->error();
    }

    protected function dispatch(string $controller, array $params = []) {
        if (strpos($controller, '@') === false) {
            $this->error();
            return;
        }

        list($controllerName, $methodName) = explode('@', $controller);
        $controllerClass = strpos($controllerName, '\\') === false
            ? "App\\Controllers\\{$controllerName}"
            : $controllerName;

        if (!class_exists($controllerClass)) {
            $this->error();
            return;
        }

        $controllerObject = new $controllerClass();

        if (!method_exists($controllerObject, $methodName)) {
            $this->error();
            return;
        }

        $controllerObject->{$methodName}($params);
    }
}
?>