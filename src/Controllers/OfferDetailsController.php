<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use PDO;

class OfferDetailsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        require_once __DIR__ . '/../../src/Database.php';

        $annonceId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pdo = getConnection();

        // Récupère l'annonce par son id
        $stmt = $pdo->prepare('
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE a.id_annonce = :id
        ');
        
        $stmt->execute([':id' => $annonceId]);
        $annonce = $stmt->fetch(PDO::FETCH_ASSOC);

        // Annonce introuvable : 404
        if (!$annonce) {
            $this->render('pages/404.twig.html');
            return;
        }

        // Même traitement que dans Annonces.php
        $annonce['tags'] = json_decode($annonce['tags'] ?? '[]', true);
        $annonce['type'] = strtolower($annonce['type'] ?? '');

        // isFavorite depuis la BDD
        $check = $pdo->prepare('
            SELECT 1 FROM public.favori
            WHERE id_compte = :c AND id_annonce = :a
        ');

        $check->execute([':c' => $_SESSION['user_id'] ?? 0, ':a' => $annonceId]);
        $annonce['isFavorite']  = (bool) $check->fetch();
        $annonce['hasReminder'] = false;

        $this->render('pages/detail-annonce.twig.html', [
            'annonce' => $annonce,
        ]);
    }
}