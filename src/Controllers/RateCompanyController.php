<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class RateCompanyController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $this->render('pages/evaluation-entreprise.twig.html', [
            'user_nom'    => $_SESSION['user_nom'] ?? '',
            'user_prenom' => $_SESSION['user_prenom'] ?? '',
            'user_role'   => $_SESSION['user_role'] ?? '',
            'user_email'  => $_SESSION['user_email'] ?? '',
        ]);
    }

    // Méthode 1: Récupère rating + nb_avis (PostgreSQL)
    public function getRate(int $id): array
    {
        
        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );

        // Requête SQL : moyenne + count notes
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(AVG(note)::numeric, 0) as rating,
                COUNT(*) as nb_avis
            FROM notes 
            WHERE entreprise_id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'rating' => round((float)($result['rating'] ?? 0), 1),
            'nb_avis' => (int)($result['nb_avis'] ?? 0)
        ];
    }

    // Méthode 2: Ajoute une note
    public function rate(int $id, int $note, string $comment = ''): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );

        // Requête INSERT sécurisée
        $stmt = $pdo->prepare("
            INSERT INTO notes (entreprise_id, note, commentaire, created_at) 
            VALUES (:id, :note, :comment, NOW())
        ");
        
        return $stmt->execute([
            ':id' => $id,
            ':note' => $note,
            ':comment' => $comment
        ]);
    }


    
}