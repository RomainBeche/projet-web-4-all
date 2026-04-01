<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Compte;

class RegistrationController extends Controller
{
    public function index(): void
    {
        $type   = in_array($_GET['type'] ?? '', ['etudiant', 'pilote'])
                    ? $_GET['type']
                    : 'etudiant';
        $error  = null;
        $succes = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom      = trim($_POST['nom']      ?? '');
            $prenom   = trim($_POST['prenom']   ?? '');
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $error = 'Tous les champs sont obligatoires.';
            } else {
                require_once __DIR__ . '/../../src/Database.php';
                $model  = new Compte(getConnection());
                $niveau = $type === 'pilote' ? 2 : 1;
                $newId  = $model->create($email, $password, $type, $niveau);
                $model->createProfil($newId, $nom, $prenom, $email, $type);
                $succes = 'Compte créé avec succès !';
            }
        }

        $this->render('pages/creation-compte.twig.html', [
            'type'   => $type,
            'error'  => $error,
            'succes' => $succes,
        ]);
    }
}