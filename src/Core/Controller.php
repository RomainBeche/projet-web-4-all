<?php
namespace Grp5\ProjetWeb4All\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    protected ?object $model = null;
    protected Environment $templateEngine;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $this->templateEngine = new Environment($loader, ['cache' => false]);
    }

    protected function render(string $view, array $data = []): void
    {
        echo $this->templateEngine->render($view, $data);
    }

    protected function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /?page=login');
        exit;
    }
}
}