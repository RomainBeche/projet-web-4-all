<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class OfferDetailsController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Annonces.php';     // TODO: REMOVE

        $offreId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

        // Recherche de l'annonce par ID
        $annonce = null;
        foreach ($annonces as $a) {
            if ($a['id'] === $offreId) {
                $annonce = $a;
                break;
            }
        }

        // Annonce introuvable → 404
        if ($annonce === null) {
            $this->render('pages/404.twig.html');
            return;
        }

        // Enrichissement avec les données de session
        $favoris = $_SESSION['favoris'] ?? [];
        $rappels = $_SESSION['rappels'] ?? [];
        $annonce['isFavorite']  = in_array($offreId, $favoris);
        $annonce['hasReminder'] = in_array($offreId, $rappels);

        $this->render('pages/detail-annonce.twig.html', [
            'annonce' => $annonce,
        ]);
    }

    public function toggleFavorite(): void
    {
        $this->handleToggle('favoris');
    }

    public function toggleReminder(): void
    {
        $this->handleToggle('rappels');
    }

    private function handleToggle(string $sessionKey): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $data    = json_decode(file_get_contents('php://input'), true);
        $offreId = isset($data['id']) ? (int)$data['id'] : 0;

        if ($offreId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalide']);
            exit;
        }

        $_SESSION[$sessionKey] = $_SESSION[$sessionKey] ?? [];

        $index = array_search($offreId, $_SESSION[$sessionKey]);

        if ($index !== false) {
            array_splice($_SESSION[$sessionKey], $index, 1);
            $active = false;
        } else {
            $_SESSION[$sessionKey][] = $offreId;
            $active = true;
        }

        header('Content-Type: application/json');
        echo json_encode(['active' => $active]);
        exit;
    }
}