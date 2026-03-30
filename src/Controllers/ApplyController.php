<?php
namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Core\Database;

class ApplyController extends Controller
{
        public function index(): void
    {
        // requête POST (pour les fichiers)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $allowedTypes = array('pdf');
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

        // Fetch annonce from DB
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT a.*, e.nom AS entreprise
            FROM annonce a
            JOIN entreprise e ON e.id_entreprise = a.id_entreprise
            WHERE a.id_offre = :id
        ");
        $stmt->execute([':id' => $annonceId]);
        $annonce = $stmt->fetch(\PDO::FETCH_ASSOC);

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

        // POST → traitement de la candidature
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store($annonceId);
            return;
        }

        // GET → affichage du formulaire
        $this->render('pages/postuler.twig.html', [
            'annonce' => $annonce,
        ]);
    }

    private function store(int $annonceId): void
    {
        $formation = trim($_POST['formation']  ?? '');
        $niveau    = trim($_POST['niveau']     ?? '');
        $dateDebut = trim($_POST['date_debut'] ?? '');
        $duree     = (int) ($_POST['duree']    ?? 0);
        $portfolio = trim($_POST['portfolio']  ?? '') ?: null;
        $message   = trim($_POST['message']    ?? '') ?: null;

        if (empty($formation) || empty($niveau) || empty($dateDebut) || $duree <= 0) {
            header('Location: ?page=postuler&id=' . $annonceId . '&error=champs_manquants');
            exit;
        }

        // Upload CV
        $cvUrl = null;
        if (!empty($_FILES['cv']['tmp_name'])) {
            $cvUrl = $this->uploadToSupabase(
                $_FILES['cv']['tmp_name'],
                uniqid('cv_') . '.pdf',
                'candidatures'
            );
        }

        // Upload lettre de motivation
        $lettreUrl = null;
        if (!empty($_FILES['lettre']['tmp_name'])) {
            $lettreUrl = $this->uploadToSupabase(
                $_FILES['lettre']['tmp_name'],
                uniqid('lettre_') . '.pdf',
                'candidatures'
            );
        }

        // Insert candidature
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO candidature
                (id_offre, id_compte, formation, niveau, date_debut, duree, cv_url, lettre_motivation_url, portfolio, message)
            VALUES
                (:id_offre, :id_compte, :formation, :niveau, :date_debut, :duree, :cv_url, :lettre_url, :portfolio, :message)
            RETURNING id_candidature
        ");

        $stmt->execute([
            ':id_offre'   => $annonceId,
            ':id_compte'  => $_SESSION['user_id'],
            ':formation'  => $formation,
            ':niveau'     => $niveau,
            ':date_debut' => $dateDebut,
            ':duree'      => $duree,
            ':cv_url'     => $cvUrl,
            ':lettre_url' => $lettreUrl,
            ':portfolio'  => $portfolio,
            ':message'    => $message,
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        header('Location: ?page=ma-candidature&id=' . $row['id_candidature']);
        exit;
    }

    private function uploadToSupabase(string $filePath, string $fileName, string $bucket): string
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $url    = "{$dotenv['SUPABASE_URL']}/storage/v1/object/{$bucket}/{$fileName}";
        $apiKey = $dotenv['SUPABASE_ANON_KEY'];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_PUT            => true,
            CURLOPT_INFILE         => fopen($filePath, 'r'),
            CURLOPT_INFILESIZE     => filesize($filePath),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$apiKey}",
                "Content-Type: application/pdf",
                "x-upsert: true",
            ],
        ]);

        curl_exec($ch);
        curl_close($ch);

        return "{$dotenv['SUPABASE_URL']}/storage/v1/object/public/{$bucket}/{$fileName}";
    }
}