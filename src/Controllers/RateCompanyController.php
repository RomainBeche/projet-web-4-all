<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class RateCompanyController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );

        $entrepriseId   = $_SESSION['id_entreprise'];

        $stmt = $pdo->prepare("SELECT * FROM entreprise WHERE id_entreprise = :id");
        $stmt->execute([':id' => $entrepriseId]);
        $entreprise = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->render('pages/evaluation-entreprise.twig.html', [
            'Nom_entreprise'    => $entreprise['nom'] ?? '',
            'Secteur' => $entreprise['Secteur'] ?? '',
            'Rating'   => $entreprise['Rating'] ?? '',
            'Nb_avis'  => $user['Nombre_avis'] ?? '',
    ]);
    }

    // Méthode 1: Récupère rating + nb_avis (PostgreSQL)
    public function getRate(int $id): array
    {
        $this->requireLogin();

        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );
        // Requête SQL : moyenne + count notes
        $stmt = $pdo->prepare("SELECT COALESCE(AVG(note)::numeric, 0) as rating COUNT(*) as nb_avis FROM note WHERE id_entreprise = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'rating' => round((float)($result['rating'] ?? 0), 1),
            'nb_avis' => (int)($result['nb_avis'] ?? 0)
        ];
    }

    // Méthode 2: Ajoute une note
    public function rate(int $id_entreprise, int $id_notation, int $note, string $comment = '', string $nom = '', string $prenom = ''): bool
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
            INSERT INTO note (entreprise_id, id_notation, notation, commentaire, nom, prenom) 
            VALUES (:id_entreprise, :id_notation, :notation, :commentaire, :nom, :prenom, NOW())
        ");
        
        return $stmt->execute([
            ':id_entreprise' => $id_entreprise,
            ':id_notation' => $id_notation,
            ':notation' => $note,
            ':commentaire' => $comment,
            ':nom' => $nom,
            ':prenom' => $prenom
        ]);
    }


    
}