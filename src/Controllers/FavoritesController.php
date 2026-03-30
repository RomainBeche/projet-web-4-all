<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use PDO;

class FavoritesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        require_once __DIR__ . '/../../src/Database.php';
        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationAnnonces.php';

        $pdo = getConnection();

        $stmt = $pdo->prepare('
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.favori f
            JOIN public.annonce a ON f.id_annonce = a.id_annonce
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE f.id_compte = :id_compte
            ORDER BY f.id_favori DESC
        ');
        $stmt->execute([':id_compte' => $_SESSION['user_id']]);
        $annoncesFavoris = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($annoncesFavoris as &$a) {
            $a['tags'] = json_decode($a['tags'] ?? '[]', true);
            $a['type'] = strtolower($a['type'] ?? '');
        }
        unset($a);

        $pagination = new \PaginationAnnonces($annoncesFavoris, 8);

        $this->render('pages/favoris.twig.html', [
            'currentPage' => 'favoris',
            'annonces'    => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=favoris&'),
        ]);
    }

    public function toggle(): void
    {
        $data      = json_decode(file_get_contents('php://input'), true);
        $idAnnonce = (int) ($data['id'] ?? 0);
        $idCompte  = (int) ($_SESSION['user_id'] ?? 0);

        if (!$idAnnonce || !$idCompte) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Non connecté ou ID invalide']);
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $check = $pdo->prepare('
            SELECT 1 FROM public.favori
            WHERE id_compte = :c AND id_annonce = :a
        ');
        $check->execute([':c' => $idCompte, ':a' => $idAnnonce]);

        if ($check->fetch()) {
            $pdo->prepare('DELETE FROM public.favori WHERE id_compte = :c AND id_annonce = :a')
                ->execute([':c' => $idCompte, ':a' => $idAnnonce]);
            $active = false;
        } else {
            $pdo->prepare('INSERT INTO public.favori (id_compte, id_annonce) VALUES (:c, :a)')
                ->execute([':c' => $idCompte, ':a' => $idAnnonce]);
            $active = true;
        }

        header('Content-Type: application/json');
        echo json_encode(['active' => $active]);
        exit;
    }

    public function add(): void
    {
        $idAnnonce = (int) ($_POST['id_annonce'] ?? 0);
        $idCompte  = (int) ($_SESSION['user_id'] ?? 0);

        if (!$idAnnonce || !$idCompte) {
            header('Location: ?page=annonces');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $pdo->prepare('
            INSERT INTO public.favori (id_compte, id_annonce)
            VALUES (:id_compte, :id_annonce)
            ON CONFLICT DO NOTHING
        ')->execute([':id_compte' => $idCompte, ':id_annonce' => $idAnnonce]);

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=annonces'));
        exit;
    }

    public function remove(): void
    {
        $idAnnonce = (int) ($_POST['id_annonce'] ?? 0);
        $idCompte  = (int) ($_SESSION['user_id'] ?? 0);

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $pdo->prepare('
            DELETE FROM public.favori
            WHERE id_compte = :id_compte AND id_annonce = :id_annonce
        ')->execute([':id_compte' => $idCompte, ':id_annonce' => $idAnnonce]);

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=favoris'));
        exit;
    }
}