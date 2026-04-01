<?php
namespace Grp5\ProjetWeb4All\Models;

class EleveModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByCompte(int $idCompte, string $role): array|false
    {
        $table = $role === 'etudiant' ? 'etudiant' : 'pilote';
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id_compte = :id");
        $stmt->execute([':id' => $idCompte]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getElevesByPilote(int $idPilote): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiant WHERE id_compte_pilote = :id");
        $stmt->execute([':id' => $idPilote]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findEleveById(int $idCompte, int $idPilote): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM etudiant WHERE id_compte = :id AND id_compte_pilote = :id_pilote");
        $stmt->execute([':id' => $idCompte, ':id_pilote' => $idPilote]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateProfil(int $idCompte, string $nom, string $prenom, string $email, string $role): void
    {
        $table = $role === 'etudiant' ? 'etudiant' : 'pilote';
        $stmt = $this->pdo->prepare("UPDATE $table SET nom = :nom, prenom = :prenom, email_publique = :email WHERE id_compte = :id");
        $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':id' => $idCompte]);
    }

    public function updateProfilSansEmail(int $idCompte, string $nom, string $prenom, string $role): void
    {
        $table = $role === 'etudiant' ? 'etudiant' : 'pilote';
        $stmt = $this->pdo->prepare("UPDATE $table SET nom = :nom, prenom = :prenom WHERE id_compte = :id");
        $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':id' => $idCompte]);
    }

    public function createEtudiant(int $idCompte, string $nom, string $prenom, string $email, int $idPilote): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO etudiant (nom, prenom, email_publique, id_compte, id_compte_pilote)
            VALUES (:nom, :prenom, :email, :id_compte, :id_compte_pilote)
        ");
        $stmt->execute([
            ':nom'              => $nom,
            ':prenom'           => $prenom,
            ':email'            => $email,
            ':id_compte'        => $idCompte,
            ':id_compte_pilote' => $idPilote,
        ]);
    }

    public function deleteEleve(int $idCompte, int $idPilote): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM etudiant WHERE id_compte = :id AND id_compte_pilote = :id_pilote");
        $stmt->execute([':id' => $idCompte, ':id_pilote' => $idPilote]);
    }

    public function deleteByCandidatures(int $idCompte): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM candidature WHERE id_compte = :id");
        $stmt->execute([':id' => $idCompte]);
    }

    public function deleteFavoris(int $idCompte): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM favori WHERE id_compte = :id");
        $stmt->execute([':id' => $idCompte]);
    }
}