<?php

class Router
{
    private $routes = [];

    /**
     * Registrar una ruta GET
     */
    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Registrar una ruta POST
     */
    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Registrar una ruta PUT
     */
    public function put($path, $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }

    /**
     * Registrar una ruta DELETE
     */
    public function delete($path, $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    /**
     * Ejecutar el router
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Soporte para PUT y DELETE mediante _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');

        // Si está vacío, es la raíz
        if (empty($uri)) {
            $uri = '/';
        }

        // Buscar coincidencia exacta
        if (isset($this->routes[$method][$uri])) {
            return $this->executeHandler($this->routes[$method][$uri]);
        }

        // Buscar coincidencia con parámetros
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->convertToRegex($route);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remover la coincidencia completa
                return $this->executeHandler($handler, $matches);
            }
        }

        // Ruta no encontrada
        http_response_code(404);
        echo "404 - Página no encontrada";
    }

    /**
     * Convertir ruta a expresión regular
     */
    private function convertToRegex($route)
    {
        // Convertir {id} a captura de grupo
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Ejecutar el handler
     */
    private function executeHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler)) {
            list($controller, $method) = explode('@', $handler);

            $controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';

            if (!file_exists($controllerFile)) {
                die("Controlador no encontrado: {$controller}");
            }

            require_once $controllerFile;

            $controllerInstance = new $controller();

            if (!method_exists($controllerInstance, $method)) {
                die("Método no encontrado: {$method} en {$controller}");
            }

            return call_user_func_array([$controllerInstance, $method], $params);
        }
    }
}
