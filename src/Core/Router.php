<?php

namespace Grp5\ProjetWeb4All\Core;

class Router
{
    protected array $routes = [];
    protected string $defaultController = 'AccueilController';
    protected string $defaultAction = 'index';

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    public function route(?string $page)
    {
        $page = $page ?? 'accueil';

        if (!isset($this->routes[$page])) {
            http_response_code(404);
            $controller = 'Grp5\ProjetWeb4All\Controllers\ErrorController';
            $action = 'notFound';
        } else {
            $controller = $this->routes[$page]['controller'];
            $action = $this->routes[$page]['action'];
        }

        if (class_exists($controller)) {
            $controllerInstance = new $controller();

            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                http_response_code(500);
                echo "Action {$action} not found in controller {$controller}";
            }
        } else {
            http_response_code(500);
            echo "Controller {$controller} not found";
        }
    }
}