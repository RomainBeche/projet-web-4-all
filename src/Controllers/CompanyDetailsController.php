<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class CompanyDetailsController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Database.php';

        $pdo          = getConnection();
        $entrepriseId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$entrepriseId) {
            $this->render('pages/404.twig.html');
            return;
        }

        // Récupère l'entreprise par son ID
        $stmt = $pdo->prepare('
            SELECT *
            FROM public.entreprise
            WHERE id_entreprise = :id
        ');
        $stmt->execute([':id' => $entrepriseId]);
        $entreprise = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Entreprise introuvable → 404
        if (!$entreprise) {
            $this->render('pages/404.twig.html');
            return;
        }

        // Récupère toutes les annonces de cette entreprise
        $stmtAnnonces = $pdo->prepare('
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE a.id_entreprise_appartient = :id
            ORDER BY a.id_annonce ASC
        ');
        $stmtAnnonces->execute([':id' => $entrepriseId]);
        $annonces = $stmtAnnonces->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($annonces as &$annonce) {
            $annonce['tags'] = json_decode($annonce['tags'] ?? '[]', true);
            $annonce['type'] = strtolower($annonce['type'] ?? '');
        }
        unset($annonce);

        $this->render('pages/detail-entreprise.twig.html', [
            'entreprise' => $entreprise,
            'annonces'   => $annonces,
        ]);
    }
}