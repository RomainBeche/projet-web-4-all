<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class RateCompanyController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Entreprises.php';
        require_once __DIR__ . '/../../src/Database.php';

        $entrepriseId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

        $entreprise = null;
        foreach ($entreprises as $e) {
            if ((int)$e['id_entreprise'] === $entrepriseId) {
                $entreprise = $e;
                break;
            }
        }

        if (!$entreprise) {
            $this->render('pages/404.twig.html');
            return;
        }

        // Traitement du formulaire (POST)
        $success = false;
        $error   = false;
        $notLoggedIn = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $note      = isset($_POST['star-rating']) ? (int)$_POST['star-rating'] : 0;
            $comment   = isset($_POST['comment'])     ? trim($_POST['comment'])    : '';
            $id_compte = $_SESSION['user_id'] ?? null;    

            if ($id_compte === null) {
                $notLoggedIn = true;
            } else {
                $inserted = $this->rate($entrepriseId, $note, $id_compte, $comment);
                if ($inserted) {
                    $success = true;
                    // Met à jour les données de l'entreprise pour refléter la nouvelle note
                    $pdo = getConnection();
                    $stmt = $pdo->prepare("
                        UPDATE entreprise
                        SET nombre_avis = (SELECT COUNT(*) FROM note WHERE id_entreprise = :id)
                            , rating = ROUND((SELECT COALESCE(AVG(notation)::numeric, 0) FROM note WHERE id_entreprise = :id), 1)
                        WHERE id_entreprise =  :id
                    ");
                    $stmt->execute([':id' => $entrepriseId]);
                    $stmt->fetch(\PDO::FETCH_ASSOC);
                }   else {
                    $error = true;
                }
            }
        }

        $this->render('pages/evaluation-entreprise.twig.html', [
            'entreprise'  => $entreprise,
            'rating'      => $entreprise['rating'],
            'nb_avis'     => $entreprise['nombre_avis'],
            'success'     => $success,
            'error'       => $error,
            'notLoggedIn' => $notLoggedIn,
        ]);
    }



    // Méthode 1 : Récupère rating + nb_avis (PostgreSQL)
    public function getRate(int $id): array
    {
        $pdo = getConnection();

        $stmt = $pdo->prepare("
            SELECT COALESCE(AVG(notation)::numeric, 0) AS rating,
                   COUNT(*) AS nb_avis
            FROM note
            WHERE id_entreprise = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'rating'  => round((float)($result['rating'] ?? 0), 1),
            'nb_avis' => (int)($result['nb_avis'] ?? 0),
        ];
    }

    // Méthode 2 : Ajoute une note
    public function rate(int $id_entreprise, int $note, int $id_compte, string $comment = ''): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        $pdo = getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO note (id_entreprise, notation, commentaire, id_compte, date_notation)
            VALUES (:id_entreprise, :notation, :commentaire, :id_compte, NOW())
        ");

        return $stmt->execute([
            ':id_entreprise' => $id_entreprise,
            ':notation'      => $note,
            ':commentaire'   => $comment,
            ':id_compte'     => $id_compte,
        ]);

        $stmt->fetch(\PDO::FETCH_ASSOC);

    }
}
