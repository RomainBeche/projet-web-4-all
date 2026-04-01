<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Annonces;
use Grp5\ProjetWeb4All\Models\Candidatures;

class ApplyController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        require_once __DIR__ . '/../../src/Database.php';

        $annonceId = (int)($_GET['id'] ?? 0);
        if ($annonceId === 0) {
            header('Location: ?page=annonces');
            exit;
        }

        $pdo = getConnection();
        $annonceModel = new Annonces($pdo);
        $annonce = $annonceModel->findById($annonceId);

        if (!$annonce) {
            $this->render('pages/404.twig.html');
            return;
        }

        // Vérifie si l'utilisateur a déjà postulé
        $candidatureModel = new Candidatures($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->store($pdo, $annonceId);

            if ($error) {
                $this->render('pages/postuler.twig.html', [
                    'annonce'   => $annonce,
                    'annonceId' => $annonceId,
                    'error'     => $error,
                ]);
                return;
            }

            header('Location: ?page=mes-candidatures');
            exit;
        }

        $this->render('pages/postuler.twig.html', [
            'annonce'   => $annonce,
            'annonceId' => $annonceId,
        ]);
    }

    // -------------------------------------------------------------------------

    private function store(\PDO $pdo, int $annonceId): ?string
    {
        $formation  = trim($_POST['formation']  ?? '');
        $niveau     = trim($_POST['niveau']     ?? '');
        $dateDebut  = trim($_POST['date_debut'] ?? '');
        $duree      = (int)($_POST['duree']     ?? 0);
        $portfolio  = trim($_POST['portfolio']  ?? '') ?: null;
        $message    = trim($_POST['message']    ?? '') ?: null;

        if (empty($formation) || empty($niveau) || empty($dateDebut) || $duree <= 0) {
            return 'Veuillez remplir tous les champs obligatoires.';
        }

        $cvUrl     = $this->uploadFile($_FILES['cv']     ?? null, 'cv');
        $lettreUrl = $this->uploadFile($_FILES['lettre'] ?? null, 'lettre');

        if (!$cvUrl)     return 'Le CV doit être un fichier PDF valide (max 2 Mo).';
        if (!$lettreUrl) return 'La lettre de motivation doit être un fichier PDF valide (max 2 Mo).';

        $model = new Candidatures($pdo);

        $model->deleteByUserAndAnnonce($_SESSION['user_id'], $annonceId);

        $model->create([
            'id_annonce' => $annonceId,
            'id_compte'  => $_SESSION['user_id'],
            'formation'  => $formation,
            'niveau'     => $niveau,
            'date_debut' => $dateDebut,
            'duree'      => $duree,
            'cv'         => $cvUrl,
            'lettre'     => $lettreUrl,
            'portfolio'  => $portfolio,
            'message'    => $message,
        ]);

        return null;
    }

    // -------------------------------------------------------------------------

    private function uploadFile(?array $file, string $type): string|false
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            error_log("[$type] Erreur fichier : " . ($file['error'] ?? 'null'));
            return false;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            error_log("[$type] Fichier trop lourd : " . $file['size']);
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if ($mime !== 'application/pdf') {
            error_log("[$type] MIME invalide : $mime");
            return false;
        }

        $filename    = $type . '/' . uniqid($type . '_', true) . '.pdf';
        $fileContent = file_get_contents($file['tmp_name']);

        $supabaseUrl = rtrim($_ENV['SUPABASE_URL']       ?? '', '/');
        $bucket      = $_ENV['SUPABASE_BUCKET']           ?? '';
        $serviceKey  = $_ENV['SUPABASE_SERVICE_KEY']      ?? '';

        error_log("URL=$supabaseUrl | BUCKET=$bucket | KEY=" . substr($serviceKey, 0, 10));
        
        $endpoint    = "$supabaseUrl/storage/v1/object/$bucket/$filename";

        error_log("[$type] Upload vers : $endpoint");

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $fileContent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/pdf',
                'apikey: ' . $serviceKey,
                'Authorization: Bearer ' . $serviceKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        error_log("[$type] HTTP $httpCode — Réponse : $response");

        if ($httpCode !== 200) return false;

        return "$supabaseUrl/storage/v1/object/public/$bucket/$filename";
    }

    public function getSignedUrl(string $path, int $expiresIn = 3600): string
    {
        $supabaseUrl = rtrim($_ENV['SUPABASE_URL'], '/');
        $bucket      = $_ENV['SUPABASE_BUCKET'];
        $serviceKey  = $_ENV['SUPABASE_SERVICE_KEY'];

        $ch = curl_init("$supabaseUrl/storage/v1/object/sign/$bucket/$path");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['expiresIn' => $expiresIn]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: ' . $serviceKey,
                'Authorization: Bearer ' . $serviceKey,
            ],
        ]);

        $result = json_decode(curl_exec($ch), true);

        return $supabaseUrl . $result['signedURL'];
    }
}