<?php
namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class AccountController extends Controller
{

    // Show account dashboard
    public function index(): void
    {
        $this->requireLogin();

        $this->render('pages/compte.twig.html', [
            'user_nom'    => $_SESSION['user_nom'] ?? '',
            'user_prenom' => $_SESSION['user_prenom'] ?? '',
            'user_role'   => $_SESSION['user_role'] ?? '',
            'user_email'  => $_SESSION['user_email'] ?? '',
        ]);
    }

    // Edit account page
    public function edit(): void
    {
        $this->requireLogin();

        $this->render('pages/modification-compte.twig.html', [
            'user_nom'    => $_SESSION['user_nom'] ?? '',
            'user_prenom' => $_SESSION['user_prenom'] ?? '',
            'user_role'   => $_SESSION['user_role'] ?? '',
            'user_email'  => $_SESSION['user_email'] ?? '',
        ]);
    }

    // Edit account validation
    public function editValidation(): void
    {
        $this->requireLogin();

        $this->render('pages/modification-compte-validation.twig.html', [
            'user_nom'    => $_SESSION['user_nom'] ?? '',
            'user_prenom' => $_SESSION['user_prenom'] ?? '',
            'user_role'   => $_SESSION['user_role'] ?? '',
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

        // TODO: actually delete account from DB

        session_destroy();
        header('Location: /?page=accueil');
        exit;
    }
}