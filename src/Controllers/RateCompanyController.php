<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Entreprises;
use Grp5\ProjetWeb4All\Models\Note;

class RateCompanyController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Database.php';

        $entrepriseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($entrepriseId === 0) {
            $this->render('pages/404.twig.html');
            return;
        }

        $pdo        = getConnection();
        $entreprise = (new Entreprises($pdo))->findById($entrepriseId);

        if (!$entreprise) {
            $this->render('pages/404.twig.html');
            return;
        }

        $success    = false;
        $error      = false;
        $notLoggedIn = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCompte = $_SESSION['user_id'] ?? null;
            if ($idCompte === null) {
                $notLoggedIn = true;
            } else {
                $note    = (int) ($_POST['star-rating'] ?? 0);
                $comment = trim($_POST['comment'] ?? '');
                $ok      = (new Note($pdo))->add($entrepriseId, $idCompte, $note, $comment);
                $ok ? $success = true : $error = true;
                // Recharge l'entreprise pour afficher les stats fraîches
                $entreprise = (new Entreprises($pdo))->findById($entrepriseId);
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
}