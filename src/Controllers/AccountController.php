<?php
namespace Grp5\ProjetWeb4All\Controllers;
use Grp5\ProjetWeb4All\Core\Controller;

class AccountController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        $this->render('pages/compte.twig.html', [
            'user_nom'    => $_SESSION['user_nom'],
            'user_prenom' => $_SESSION['user_prenom'],
            'user_role'   => $_SESSION['user_role'],
            'user_email'  => $_SESSION['user_email'],
        ]);
    }

    public function edit(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        $this->render('pages/modification-compte.twig.html', [
            'user_nom'    => $_SESSION['user_nom'],
            'user_prenom' => $_SESSION['user_prenom'],
            'user_role'   => $_SESSION['user_role'],
            'user_email'  => $_SESSION['user_email'],
        ]);
    }

    public function editValidation(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?page=login');
        exit;
    }
    $this->render('pages/modification-compte-validation.twig.html', [
        'user_nom'    => $_SESSION['user_nom'],
        'user_prenom' => $_SESSION['user_prenom'],
        'user_role'   => $_SESSION['user_role'],
    ]);
}


    public function logout(): void
    {
        session_destroy();
        header('Location: ?page=accueil');
        exit;
    }

    public function delete(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        // TODO: supprimer le compte en BDD
        session_destroy();
        header('Location: ?page=accueil');
        exit;
    }
}