<?php
namespace Grp5\ProjetWeb4All\Controllers;
use Grp5\ProjetWeb4All\Core\Controller;

class RegistrationController extends Controller
{
    public function index(): void
    {
        $type = isset($_GET['type']) && $_GET['type'] === 'pilote' ? 'pilote' : 'etudiant';
        $error = null;
        $succes = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom      = trim($_POST['nom'] ?? '');
            $prenom   = trim($_POST['prenom'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $type     = $_POST['type'] ?? 'etudiant';

            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                $dotenv = parse_ini_file(__DIR__ . '/../../.env');
                $pdo = new \PDO(
                    "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
                    $dotenv['DB_USER'],
                    $dotenv['DB_PASSWORD']
                );

                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Récupère le prochain id_compte
                $maxId = $pdo->query("SELECT COALESCE(MAX(id_compte), 0) + 1 FROM compte")->fetchColumn();

                // Insertion dans compte
                $stmt = $pdo->prepare("
                    INSERT INTO compte (id_compte, email_publique, mot_de_passe, role, niveau_permission)
                    VALUES (:id, :email, :password, :role, 1)
                ");
                $stmt->execute([
                    ':id'       => $maxId,
                    ':email'    => $email,
                    ':password' => $hash,
                    ':role'     => $type,
                ]);

                // Insertion dans etudiant ou pilote
                if ($type === 'etudiant') {
                    $maxEtudiantId = $pdo->query("SELECT COALESCE(MAX(id_etudiant), 0) + 1 FROM etudiant")->fetchColumn();
                    $stmt = $pdo->prepare("
                        INSERT INTO etudiant (id_etudiant, id_compte, nom, prenom, email_publique, niveau_permission, role)
                        VALUES (:id_etudiant, :id_compte, :nom, :prenom, :email, 1, 'etudiant')
                    ");
                    $stmt->execute([
                        ':id_etudiant' => $maxEtudiantId,
                        ':id_compte'   => $maxId,
                        ':nom'         => $nom,
                        ':prenom'      => $prenom,
                        ':email'       => $email,
                    ]);
                } elseif ($type === 'pilote') {
                    $maxPiloteId = $pdo->query("SELECT COALESCE(MAX(id_pilote), 0) + 1 FROM pilote")->fetchColumn();
                    $stmt = $pdo->prepare("
                        INSERT INTO pilote (id_pilote, id_compte, nom, prenom, email_publique)
                        VALUES (:id_pilote, :id_compte, :nom, :prenom, :email)
                    ");
                    $stmt->execute([
                        ':id_pilote' => $maxPiloteId,
                        ':id_compte' => $maxId,
                        ':nom'       => $nom,
                        ':prenom'    => $prenom,
                        ':email'     => $email,
                    ]);
                }

                $succes = "Compte créé avec succès !";
            }
        }

        $this->render('pages/creation-compte.twig.html', [
            'type'   => $type,
            'error'  => $error,
            'succes' => $succes,
        ]);
    }
}