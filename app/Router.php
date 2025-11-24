<?php
class Router {
    private $routes = [];
    
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) $path = '/';

        // Debug logging
        error_log("Router - Method: $method, Path: $path");

        // Try exact match first
        if (isset($this->routes[$method][$path])) {
            error_log("Router - Found exact match for: $path");
            $this->executeCallback($this->routes[$method][$path], []);
            return;
        }

        // Try pattern matching
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            error_log("Router - Testing pattern: $pattern against path: $path");

            if (preg_match($pattern, $path, $matches)) {
                error_log("Router - Pattern matched! Matches: " . json_encode($matches));
                array_shift($matches);
                $this->executeCallback($callback, $matches);
                return;
            }
        }

        // 404
        error_log("Router - No route matched, returning 404 for path: $path");
        http_response_code(404);
        echo "404 - Page Not Found";
    }
    
    private function executeCallback($callback, $params) {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controller, $method) = explode('@', $callback);
            $controller = "controllers\\$controller";

            error_log("Router - Attempting to load controller: $controller, method: $method");
            error_log("Router - Class exists: " . (class_exists($controller) ? 'yes' : 'no'));
            error_log("Router - Method exists: " . (method_exists($controller, $method) ? 'yes' : 'no'));

            if (class_exists($controller) && method_exists($controller, $method)) {
                error_log("Router - Creating controller instance and calling method with params: " . json_encode($params));
                $instance = new $controller();
                call_user_func_array([$instance, $method], $params);
            } else {
                error_log("Router - ERROR: Controller or method not found!");
                die("Controller $controller or method $method not found");
            }
        } else if (is_callable($callback)) {
            call_user_func_array($callback, $params);
        }
    }
}
