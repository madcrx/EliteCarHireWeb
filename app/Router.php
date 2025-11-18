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
        
        // Try exact match first
        if (isset($this->routes[$method][$path])) {
            $this->executeCallback($this->routes[$method][$path], []);
            return;
        }
        
        // Try pattern matching
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $this->executeCallback($callback, $matches);
                return;
            }
        }
        
        // 404
        http_response_code(404);
        echo "404 - Page Not Found";
    }
    
    private function executeCallback($callback, $params) {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controller, $method) = explode('@', $callback);
            $controller = "controllers\\$controller";
            
            if (class_exists($controller) && method_exists($controller, $method)) {
                $instance = new $controller();
                call_user_func_array([$instance, $method], $params);
            } else {
                die("Controller $controller or method $method not found");
            }
        } else if (is_callable($callback)) {
            call_user_func_array($callback, $params);
        }
    }
}
