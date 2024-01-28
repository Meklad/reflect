<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use ReflectionClass;

class HomeController extends Controller
{
    /**
     * Rander home view
     *
     * @return void
     */
    public function index()
    {
        $exclude = [
            ".",
            "..",
            "HomeController.php"
        ];

        $adminControllers = collect(scandir(app_path("Http/Controllers/Admin")));

        $controllersArray = $adminControllers->lazy()->map(function($controller) use($exclude) {
            if(!in_array($controller, $exclude)) {
                return "\\App\\Http\\Controllers\\Admin\\" . str_replace(".php", "", $controller);
            }
        })->filter();

        $controllersWithoutParent = $controllersArray->map(function($controller) {
            $reflection = new ReflectionClass($controller);
            $parentClass = $reflection->getParentClass();
            $allMethods = $reflection->getMethods();
            $parentMethods = ($parentClass) ? $parentClass->getMethods() : [];

            $controllerMethods = array_filter($allMethods, function ($method) use ($parentMethods) {
                foreach ($parentMethods as $parentMethod) {
                    if ($method->getName() === $parentMethod->getName()) {
                        return false;
                    }
                }
                return true;
            });

            $controllerMethodNames = array_map(function ($method) {
                return $method->getName();
            }, $controllerMethods);

            $controllerName = strtolower(
                str_replace(
                    "Controller",
                    "",
                    str_replace(
                        "\\App\\Http\\Controllers\\Admin\\",
                        "",
                        $controller . "s"
                    )
                )
            );

            return [
                "controller" => $controllerName,
                "methods" => $controllerMethodNames
            ];
        })->values()->toArray();

        return $controllersWithoutParent;
    }
}
