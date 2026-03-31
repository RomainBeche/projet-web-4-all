<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class AccountController extends Controller
{

    // Show account dashboard
    public function index(): void
    {
        $this->requireLogin();

    $dotenv = parse_ini_file(__DIR__ . '/../../.env');
    $pdo = new \PDO(
        "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
        $dotenv['DB_USER'],
        $dotenv['DB_PASSWORD']
    );

    $userRole = $_SESSION['user_role'];
    $userId   = $_SESSION['user_id'];

    if ($userRole === 'etudiant') {
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pilote WHERE id_compte = :id");
    }

    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

    $this->render('pages/compte.twig.html', [
        'user_nom'    => $user['nom'] ?? '',
        'user_prenom' => $user['prenom'] ?? '',
        'user_role'   => $userRole,
        'user_email'  => $user['email_publique'] ?? '',
    ]);
    }


    // Edit account page
    public function edit(): void
    {
        $this->requireLogin();

        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );

        $userRole = $_SESSION['user_role'];
        $userId   = $_SESSION['user_id'];

        if ($userRole === 'etudiant') {
            $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM pilote WHERE id_compte = :id");
        }

        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->render('pages/modification-compte.twig.html', [
            'user_nom'    => $user['nom'] ?? '',
            'user_prenom' => $user['prenom'] ?? '',
            'user_role'   => $userRole,
            'user_email'  => $user['email_publique'] ?? '',
        ]);
    }

    // Edit account validation
    public function editValidation(): void
    {
        $this->requireLogin();

        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );

        $userId   = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];

        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Mise à jour dans etudiant ou pilote
        if ($userRole === 'etudiant') {
           $stmt = $pdo->prepare("UPDATE etudiant SET nom = :nom, prenom = :prenom, email_publique = :email WHERE id_compte = :id");
        } else {
            $stmt = $pdo->prepare("UPDATE pilote SET nom = :nom, prenom = :prenom, email_publique = :email WHERE id_compte = :id");
        }
        $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':id' => $userId]);

        // Mise à jour email dans compte
        $stmt = $pdo->prepare("UPDATE compte SET email_publique = :email WHERE id_compte = :id");
        $stmt->execute([':email' => $email, ':id' => $userId]);

        // Mise à jour mot de passe si renseigné
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE compte SET mot_de_passe = :hash WHERE id_compte = :id");
            $stmt->execute([':hash' => $hash, ':id' => $userId]);
        }

        // Mise à jour de la session
        $_SESSION['user_email'] = $email;

        $this->render('pages/modification-compte-validation.twig.html', [
            'user_nom'    => $nom,
            'user_prenom' => $prenom,
            'user_role'   => $userRole,
        ]);
    }

    // Login page and form handling
    public function login(): void
    {
        // Already logged in → redirect
        if (isset($_SESSION['user_id'])) {
            header('Location: /?page=compte');
            exit;
        }

        $error = null;
        $email = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                // Temporary DB access (move to Model later)
                $dotenv = parse_ini_file(__DIR__ . '/../../.env');

                $pdo = new \PDO(
                    "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
                    $dotenv['DB_USER'],
                    $dotenv['DB_PASSWORD']
                );

                $stmt = $pdo->prepare("SELECT * FROM compte WHERE email_publique = :email");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['mot_de_passe'])) {
                    // Login success → store session
                    $_SESSION['user_id']    = $user['id_compte'];
                    $_SESSION['user_nom']   = $user['nom'] ?? '';
                    $_SESSION['user_prenom'] = $user['prenom'] ?? '';
                    $_SESSION['user_email'] = $user['email_publique'];
                    $_SESSION['user_role']  = $user['role'];

                    header('Location: /?page=accueil');
                    exit;
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }

        // Render login page
        $this->render('pages/login.twig.html', [
            'error' => $error,
            'email' => $email,
        ]);
    }

    // Show logout confirmation page
    public function logoutConfirmation(): void
    {
        $this->requireLogin();
        $this->render('pages/deconnexion.twig.html');
    }

    // Logout user
    public function logout(): void
    {
        $this->requireLogin();
        session_destroy();
        header('Location: /?page=login');
        exit;
    }

    // Show delete account confirmation page
    public function deleteConfirmation(): void
    {
        $this->requireLogin();
        $this->render('pages/suppression-compte-1.twig.html');
    }

        // Delete account and redirect
        public function delete(): void
    {
        $this->requireLogin();

        $dotenv = parse_ini_file(__DIR__ . '/../../.env');
        $pdo = new \PDO(
            "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
            $dotenv['DB_USER'],
            $dotenv['DB_PASSWORD']
        );

        $userId   = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];

        // Supprime d'abord dans la table du rôle (etudiant ou pilote)
        if ($userRole === 'etudiant') {
            $stmt = $pdo->prepare("DELETE FROM etudiant WHERE id_compte = :id");
            $stmt->execute([':id' => $userId]);
        } elseif ($userRole === 'pilote') {
            $stmt = $pdo->prepare("DELETE FROM pilote WHERE id_compte = :id");
            $stmt->execute([':id' => $userId]);
        }

        // Supprime ensuite dans la table compte
        $stmt = $pdo->prepare("DELETE FROM compte WHERE id_compte = :id");
        $stmt->execute([':id' => $userId]);

        // Détruit la session
        $_SESSION = [];
        session_destroy();
        header('Location: /?page=login');
        exit;
    }

    // Show mes eleves page
public function mesEleves(): void
{
    $this->requireLogin();

    if ($_SESSION['user_role'] !== 'pilote') {
        header('Location: /?page=compte');
        exit;
    }

    $this->render('pages/mes-eleves.twig.html', [
        'user_role' => $_SESSION['user_role'],
    ]);
}

// Create student account
public function mesElevesCreation(): void
{
    $this->requireLogin();

    if ($_SESSION['user_role'] !== 'pilote') {
        header('Location: /?page=compte');
        exit;
    }

    $error  = null;
    $succes = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            $dotenv = parse_ini_file(__DIR__ . '/../../.env');
            $pdo = new \PDO(
                "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
                $dotenv['DB_USER'],
                $dotenv['DB_PASSWORD']
            );

            // Hash du mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // 1. Insertion dans compte
            // Récupère le prochain id disponible
            $maxId = $pdo->query("SELECT COALESCE(MAX(id_compte), 0) + 1 FROM compte")->fetchColumn();

            $stmt = $pdo->prepare("
            INSERT INTO compte (id_compte, email_publique, mot_de_passe, role, niveau_permission)
            VALUES (:id, :email, :password, 'etudiant', 1)
            RETURNING id_compte
        ");
        $stmt->execute([
            ':id'       => $maxId,
            ':email'    => $email,
            ':password' => $hash,
        ]);
            $newCompte = $stmt->fetch(\PDO::FETCH_ASSOC);
            $newIdCompte = $newCompte['id_compte'];

            // 2. Insertion dans etudiant
            $stmt = $pdo->prepare("
                INSERT INTO etudiant (nom, prenom, email_publique, id_compte, id_compte_pilote)
                VALUES (:nom, :prenom, :email, :id_compte, :id_compte_pilote)
            ");
            $stmt->execute([
                ':nom'             => $nom,
                ':prenom'          => $prenom,
                ':email'           => $email,
                ':id_compte'       => $newIdCompte,
                ':id_compte_pilote' => $_SESSION['user_id'],
            ]);

            $succes = "Le compte étudiant de $prenom $nom a été créé avec succès.";
        }
    }

    $this->render('pages/mes-eleves-creation.twig.html', [
        'user_role' => $_SESSION['user_role'],
        'error'     => $error,
        'succes'    => $succes,
    ]);
}
}