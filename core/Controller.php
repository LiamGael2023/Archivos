<?php

abstract class Controller
{
    /**
     * Cargar una vista
     */
    protected function view($viewPath, $data = [])
    {
        extract($data);

        $viewFile = __DIR__ . '/../app/views/' . str_replace('.', '/', $viewPath) . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: {$viewPath}");
        }
    }

    /**
     * Retornar JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redireccionar
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Obtener datos POST
     */
    protected function input($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Obtener datos GET
     */
    protected function query($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Validar datos requeridos
     */
    protected function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if ($rule === 'required' && empty($data[$field])) {
                $errors[$field] = "El campo {$field} es requerido";
            }
        }

        return $errors;
    }
}
