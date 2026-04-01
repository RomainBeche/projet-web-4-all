<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\AccountModel;
use Grp5\ProjetWeb4All\Models\EleveModel;
use Grp5\ProjetWeb4All\Models\Entreprises;

class AccountController extends Controller
{

    // Show account dashboard
    public function index(): void
    {
        $this->requireLogin();

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

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

        
 
        
  
        $this->render('pages/mes-eleves.twig.html', [
            
        ]);
    }


    
    // Edit account page
    public function edit(): void
    {
        $this->requireLogin();

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

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



    public function editValidation(): void
    {
        $this->requireLogin();

        $pdo          = $this->getPdo();
        $accountModel = new AccountModel($pdo);
        $eleveModel   = new EleveModel($pdo);

        $userId   = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $currentEmail = $accountModel->getEmailById($userId);

        if ($email !== $currentEmail) {
            $eleveModel->updateProfil($userId, $nom, $prenom, $email, $userRole);
            $accountModel->updateEmail($userId, $email);
        } else {
            $eleveModel->updateProfilSansEmail($userId, $nom, $prenom, $userRole);
        }

        if (!empty($password)) {
            $accountModel->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT));
        }

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
                require_once __DIR__ . '/../../src/Database.php';
                $pdo = getConnection();

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

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

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
                require_once __DIR__ . '/../../src/Database.php';
                $pdo = getConnection();

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



    // Liste des élèves du pilote
    // public function mesEleves(): void
    // {
    //     $this->requireLogin();

    //     if ($_SESSION['user_role'] !== 'pilote') {
    //         header('Location: /?page=compte');
    //         exit;
    //     }

    //     require_once __DIR__ . '/../../src/Database.php';
    //     $pdo = getConnection();

    //     $model = new EleveModel($pdo);
    //     $q     = trim($_GET['q'] ?? '');

    //     $etudiants = $q !== '' ? $model->search($q) : $model->findAll();

    //     // Récupère tous les étudiants du pilote connecté
    //     $stmt = $pdo->prepare("
    //         SELECT * FROM etudiant 
    //         WHERE id_compte_pilote = :id_pilote
    //     ");
    //     $stmt->execute([':id_pilote' => $_SESSION['user_id']]);
    //     $eleves = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    //     $this->render('pages/mes-eleves.twig.html', [
    //         'user_role' => $_SESSION['user_role'],
    //         'eleves'    => $eleves,
    //         'searchQuery' => $q,
    //     ]);
    // }


    public function mesEleves(): void
    {
        $this->requireLogin();

        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $model = new EleveModel($pdo);
        $q     = trim($_GET['q'] ?? '');
        
        $idPilote = $_SESSION['user_id'];
        $etudiants = $q !== '' 
            ? $model->search($q, $idPilote) 
            : $model->getElevesByPilote($idPilote);

        $this->render('pages/mes-eleves.twig.html', [
            'user_role' => $_SESSION['user_role'],
            'eleves'    => $etudiants,
            'searchQuery' => $q,
        ]);
    }



    // Détails d'un élève avec ses candidatures
    public function mesElevesDetail(): void
    {
        $this->requireLogin();

        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $id_compte_etudiant = (int)($_GET['id'] ?? 0);
        if ($id_compte_etudiant === 0) {
            header('Location: /?page=mes-eleves');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        // Récupère les infos de l'étudiant
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id");
        $stmt->execute([':id' => $id_compte_etudiant]);
        $eleve = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$eleve) {
            header('Location: /?page=mes-eleves');
            exit;
        }

        // Récupère les candidatures de l'étudiant avec les infos de l'annonce
        $stmt = $pdo->prepare("
            SELECT candidature.*, annonce.titre, annonce.lieu, annonce.type, annonce.duree
            FROM candidature
            JOIN annonce ON candidature.id_annonce = annonce.id_annonce
            WHERE candidature.id_compte = :id
        ");
        $stmt->execute([':id' => $id_compte_etudiant]);
        $candidatures = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('pages/mes-eleves-detail.twig.html', [
            'user_role'    => $_SESSION['user_role'],
            'eleve'        => $eleve,
            'candidatures' => $candidatures,
        ]);
    }



    public function entreprisesGestion(): void
    {
        $this->requireLogin();

        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $this->render('pages/entreprises-gestion.twig.html', [
            'user_role' => $_SESSION['user_role'],
        ]);
    }



    public function creationEntreprise(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $error  = null;
        $succes = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom         = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $telephone   = trim($_POST['telephone'] ?? '');
            $secteur     = trim($_POST['secteur'] ?? '');
            $numero_rue  = trim($_POST['numero_rue'] ?? '');
            $nom_rue     = trim($_POST['nom_rue'] ?? '');
            $nom_ville   = trim($_POST['nom_ville'] ?? '');
            $code_postal = trim($_POST['code_postal'] ?? '');

            if (empty($nom) || empty($email) || empty($secteur) || empty($nom_rue) || empty($nom_ville)) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else {
                require_once __DIR__ . '/../../src/Database.php';
                $pdo = getConnection();

                // 1. Vérifie si la ville existe, sinon la crée
                $stmt = $pdo->prepare("SELECT id_ville FROM ville WHERE nom = :nom AND code_postal = :cp");
                $stmt->execute([':nom' => $nom_ville, ':cp' => $code_postal]);
                $ville = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($ville) {
                    $id_ville = $ville['id_ville'];
                } else {
                    $maxVilleId = $pdo->query("SELECT COALESCE(MAX(id_ville), 0) + 1 FROM ville")->fetchColumn();
                    $stmt = $pdo->prepare("INSERT INTO ville (id_ville, nom, code_postal) VALUES (:id, :nom, :cp)");
                    $stmt->execute([':id' => $maxVilleId, ':nom' => $nom_ville, ':cp' => $code_postal]);
                    $id_ville = $maxVilleId;
                }

                // 2. Crée l'adresse
                $maxAdresseId = $pdo->query("SELECT COALESCE(MAX(id_adresse), 0) + 1 FROM adresse")->fetchColumn();
                $stmt = $pdo->prepare("INSERT INTO adresse (id_adresse, numero_rue, nom_rue, id_ville) VALUES (:id, :numero, :rue, :ville)");
                $stmt->execute([':id' => $maxAdresseId, ':numero' => $numero_rue, ':rue' => $nom_rue, ':ville' => $id_ville]);

                // 3. Crée l'entreprise
                $maxEntrepriseId = $pdo->query("SELECT COALESCE(MAX(id_entreprise), 0) + 1 FROM entreprise")->fetchColumn();
                $stmt = $pdo->prepare("
                    INSERT INTO entreprise (id_entreprise, nom, description, email, telephone, secteur, id_compte, id_adresse)
                    VALUES (:id, :nom, :description, :email, :telephone, :secteur, :id_compte, :id_adresse)
                ");
                $stmt->execute([
                    ':id'          => $maxEntrepriseId,
                    ':nom'         => $nom,
                    ':description' => $description,
                    ':email'       => $email,
                    ':telephone'   => $telephone,
                    ':secteur'     => $secteur,
                    ':id_compte'   => $_SESSION['user_id'],
                    ':id_adresse'  => $maxAdresseId,
                ]);

                $succes = "L'entreprise \"$nom\" a été créée avec succès !";
            }
        }

        $this->render('pages/creation-entreprise.twig.html', [
            'user_role' => $_SESSION['user_role'],
            'error'     => $error,
            'succes'    => $succes,
        ]);
    }



    public function modificationEntreprise(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $error  = null;
        $succes = null;
        $entreprise_selectionnee = null;

        // Récupère toutes les entreprises
        $entreprises = $pdo->query("SELECT * FROM entreprise")->fetchAll(\PDO::FETCH_ASSOC);

        // Étape 1 : sélection via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'modification') {
            $stmt = $pdo->prepare("SELECT * FROM entreprise WHERE id_entreprise = :id");
            $stmt->execute([':id' => (int)$_POST['id_entreprise']]);
            $entreprise_selectionnee = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Étape 2 : modification via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_entreprise = (int)($_POST['id_entreprise'] ?? 0);
            $nom           = trim($_POST['nom'] ?? '');
            $description   = trim($_POST['description'] ?? '');
            $email         = trim($_POST['email'] ?? '');
            $telephone     = trim($_POST['telephone'] ?? '');
            $secteur       = trim($_POST['secteur'] ?? '');

            if (empty($nom) || empty($email) || empty($secteur)) {
                $error = "Tous les champs obligatoires doivent être remplis.";
                $stmt = $pdo->prepare("SELECT * FROM entreprise WHERE id_entreprise = :id");
                $stmt->execute([':id' => $id_entreprise]);
                $entreprise_selectionnee = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE entreprise 
                    SET nom = :nom, description = :description, email = :email, telephone = :telephone, secteur = :secteur
                    WHERE id_entreprise = :id
                ");
                $stmt->execute([
                    ':nom'         => $nom,
                    ':description' => $description,
                    ':email'       => $email,
                    ':telephone'   => $telephone,
                    ':secteur'     => $secteur,
                    ':id'          => $id_entreprise,
                ]);
                $succes = "Entreprise mise à jour avec succès !";
            }
        }

        $this->render('pages/modification-entreprise.twig.html', [
            'user_role'               => $_SESSION['user_role'],
            'entreprises'             => $entreprises,
            'entreprise_selectionnee' => $entreprise_selectionnee,
            'error'                   => $error,
            'succes'                  => $succes,
        ]);
    }



    public function suppressionEntreprise(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $succes = null;
        $entreprise_selectionnee = null;

        // Récupère toutes les entreprises
        $entreprises = $pdo->query("SELECT * FROM entreprise")->fetchAll(\PDO::FETCH_ASSOC);

        // Étape 1 : sélection
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
            $stmt = $pdo->prepare("SELECT * FROM entreprise WHERE id_entreprise = :id");
            $stmt->execute([':id' => (int)$_POST['id_entreprise']]);
            $entreprise_selectionnee = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Étape 2 : suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'suppression') {
            $id_entreprise = (int)$_POST['id_entreprise'];
            
            // Supprime d'abord les candidatures liées aux annonces de cette entreprise
            $stmt = $pdo->prepare("
                DELETE FROM candidature 
                WHERE id_offre IN (
                    SELECT id_annonce FROM annonce WHERE id_entreprise_appartient = :id
                )
            ");
            $stmt->execute([':id' => $id_entreprise]);

            // Supprime ensuite les annonces liées
            $stmt = $pdo->prepare("DELETE FROM annonce WHERE id_entreprise_appartient = :id");
            $stmt->execute([':id' => $id_entreprise]);

            // Supprime enfin l'entreprise
            $stmt = $pdo->prepare("DELETE FROM entreprise WHERE id_entreprise = :id");
            $stmt->execute([':id' => $id_entreprise]);

            $succes = "Entreprise et ses annonces supprimées avec succès !";

            // Recharge la liste
            $entreprises = $pdo->query("SELECT * FROM entreprise")->fetchAll(\PDO::FETCH_ASSOC);
        }

        $this->render('pages/suppression-entreprise.twig.html', [
            'user_role'               => $_SESSION['user_role'],
            'entreprises'             => $entreprises,
            'entreprise_selectionnee' => $entreprise_selectionnee,
            'succes'                  => $succes,
        ]);
    }



    public function gestionEleves(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }
        $this->render('pages/gestion-eleves.twig.html', [
            'user_role' => $_SESSION['user_role'],
        ]);
    }



    public function modificationEleve(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $error  = null;
        $succes = null;
        $eleve_selectionne = null;

        // Récupère les étudiants du pilote connecté
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte_pilote = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $eleves = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Étape 1 : sélection
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
            $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id AND id_compte_pilote = :id_pilote");
            $stmt->execute([':id' => (int)$_POST['id_compte'], ':id_pilote' => $_SESSION['user_id']]);
            $eleve_selectionne = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Étape 2 : modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'modification') {
            $id_compte = (int)$_POST['id_compte'];
            $nom       = trim($_POST['nom'] ?? '');
            $prenom    = trim($_POST['prenom'] ?? '');
            $email     = trim($_POST['email'] ?? '');

            if (empty($nom) || empty($prenom) || empty($email)) {
                $error = "Tous les champs sont obligatoires.";
                $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id");
                $stmt->execute([':id' => $id_compte]);
                $eleve_selectionne = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                // Récupère l'email actuel
                $stmt = $pdo->prepare("SELECT email_publique FROM compte WHERE id_compte = :id");
                $stmt->execute([':id' => $id_compte]);
                $currentEmail = $stmt->fetchColumn();

                // Met à jour etudiant
                $stmt = $pdo->prepare("UPDATE etudiant SET nom = :nom, prenom = :prenom, email_publique = :email WHERE id_compte = :id");
                $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':id' => $id_compte]);

                // Met à jour compte si email changé
                if ($email !== $currentEmail) {
                    $stmt = $pdo->prepare("UPDATE compte SET email_publique = :email WHERE id_compte = :id");
                    $stmt->execute([':email' => $email, ':id' => $id_compte]);
                }

                $succes = "Compte de $prenom $nom mis à jour avec succès !";
            }
        }

        $this->render('pages/modification-eleve.twig.html', [
            'user_role'         => $_SESSION['user_role'],
            'eleves'            => $eleves,
            'eleve_selectionne' => $eleve_selectionne,
            'error'             => $error,
            'succes'            => $succes,
        ]);
    }



    public function suppressionEleve(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $succes = null;
        $eleve_selectionne = null;

        // Récupère les étudiants du pilote connecté
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte_pilote = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $eleves = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Étape 1 : sélection
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
            $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id AND id_compte_pilote = :id_pilote");
            $stmt->execute([':id' => (int)$_POST['id_compte'], ':id_pilote' => $_SESSION['user_id']]);
            $eleve_selectionne = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        // Étape 2 : suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'suppression') {
            $id_compte = (int)$_POST['id_compte'];

            // Supprime les candidatures
            $stmt = $pdo->prepare("DELETE FROM candidature WHERE id_compte = :id");
            $stmt->execute([':id' => $id_compte]);

            // Supprime les favoris
            $stmt = $pdo->prepare("DELETE FROM favori WHERE id_compte = :id");
            $stmt->execute([':id' => $id_compte]);

            // Supprime l'étudiant
            $stmt = $pdo->prepare("DELETE FROM etudiant WHERE id_compte = :id AND id_compte_pilote = :id_pilote");
            $stmt->execute([':id' => $id_compte, ':id_pilote' => $_SESSION['user_id']]);

            // Supprime le compte
            $stmt = $pdo->prepare("DELETE FROM compte WHERE id_compte = :id");
            $stmt->execute([':id' => $id_compte]);

            $succes = "Compte étudiant supprimé avec succès !";

            // Recharge la liste
            $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_compte_pilote = :id");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $eleves = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $this->render('pages/suppression-eleve.twig.html', [
            'user_role'         => $_SESSION['user_role'],
            'eleves'            => $eleves,
            'eleve_selectionne' => $eleve_selectionne,
            'succes'            => $succes,
        ]);
    }
}