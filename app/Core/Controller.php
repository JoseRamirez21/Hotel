<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [])
    {
        extract($data);

        $view = str_replace('.', DIRECTORY_SEPARATOR, $view);

        $viewFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';

        if (!file_exists($viewFile)) {
            die("La vista no existe: {$viewFile}");
        }

        ob_start();

        require $viewFile;

        $content = ob_get_clean();

        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'master.php';
    }

    protected function redirect(string $url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}