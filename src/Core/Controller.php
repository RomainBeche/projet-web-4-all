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
    protected function getPdo(): \PDO
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        return new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );
    }

}