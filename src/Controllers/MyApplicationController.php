<?php
namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class MyApplicationController extends Controller
{

    // Show application details (read-only)
    public function index(): void
    {
        $this->requireLogin();

        $candidatureId = $_GET['id'] ?? null;

        if (!$candidatureId) {
            header('Location: /?page=accueil');
            exit;
        }

        $pdo = Database::getInstance();

        // Fetch candidature
        $stmt = $pdo->prepare("
            SELECT c.*, a.titre, e.nom AS entreprise
            FROM candidature c
            JOIN annonce a ON a.id_offre = c.id_offre
            JOIN entreprise e ON e.id_entreprise = a.id_entreprise
            WHERE c.id_candidature = :id
              AND c.id_compte = :user_id
        ");
        $stmt->execute([
            ':id'      => $candidatureId,
            ':user_id' => $_SESSION['user_id'],
        ]);
        $candidature = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Prevent access to another user's application
        if (!$candidature) {
            header('Location: /?page=accueil');
            exit;
        }

        $this->render('pages/ma-candidature.twig.html', [
            'candidature' => $candidature,
            'annonce'     => [
                'titre'      => $candidature['titre'],
                'entreprise' => $candidature['entreprise'],
            ],
        ]);
    }
}