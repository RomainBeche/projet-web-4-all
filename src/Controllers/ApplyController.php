<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class ApplyController extends Controller
{
        public function index(): void
    {
        // requête POST (pour les fichiers)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $allowedTypes = array('pdf');
            var_dump($_FILES);
            $cv = $_FILES['cv'] ?? null;
            $lettre = $_FILES['lettre'] ?? null;

            $nomcv = $_FILES['cv']['name']; // On récupère le nom pour obtenir les infos sur l'extension.
            $nomlettre = $_FILES['cv']['name'];

            $maxSize = 2 * 1024 * 1024; // 2 Mo
            $cvfileSize = $_FILES['cv']['size'];
            $lettrefileSize = $_FILES['lettre']['size'];

            $cvfileType = strtolower(pathinfo($nomcv, PATHINFO_EXTENSION));
            $lettrefileType = strtolower(pathinfo($nomlettre, PATHINFO_EXTENSION));

            if (!in_array($cvfileType, $allowedTypes)) {echo "Seulement les fichiers PDF sont autorisés dans le champ CV.";}

            if (!in_array($lettrefileType, $allowedTypes)) {echo "Seulement les fichiers PDF sont autorisés dans le champ Lettre de Motivation.";}
            if ($cvfileSize > $maxSize && $lettrefileSize > $maxSize) {echo "File size exceeds the maximum limit of 2MB."; exit;}
            
            if ($cv && $lettre && $cv['error'] === 0 && $lettre['error'] === 0) {

                $uploadDir = __DIR__ . '/../../public/uploads/';

                $cvName = uniqid() . '_' . $cv['name'];
                $lettreName = uniqid() . '_' . $lettre['name'];

                move_uploaded_file($cv['tmp_name'], $uploadDir . $cvName);
                move_uploaded_file($lettre['tmp_name'], $uploadDir . $lettreName);

                //Sauvegarder dans la BDDDDD


                header('Location: ?page=annonces');
                exit;
            }
        }

        // requête GET (récup dans les params)
        $annonceId = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if ($annonceId === null) {
            header('Location: ?page=annonces');
            exit;
        }

        require_once __DIR__ . '/../../src/Models/Annonces.php';

        $annonce = null;
        foreach ($annonces as $a) {
            if ((int) $a['id'] === $annonceId) {
                $annonce = $a;
                break;
            }
        }

        if ($annonce === null) {
            header('Location: ?page=annonces');
            exit;
        }

        $this->render('pages/postuler.twig.html', [
            'annonce' => $annonce,
        ]);
    }
}