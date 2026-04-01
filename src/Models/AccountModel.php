<?php
namespace Grp5\ProjetWeb4All\Models;

class AccountModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM compte WHERE id_compte = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM compte WHERE email_publique = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getEmailById(int $id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT email_publique FROM compte WHERE id_compte = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn();
    }

    public function create(int $id, string $email, string $hash, string $role, int $niveau = 1): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO compte (id_compte, email_publique, mot_de_passe, role, niveau_permission)
            VALUES (:id, :email, :password, :role, :niveau)
        ");
        $stmt->execute([
            ':id'       => $id,
            ':email'    => $email,
            ':password' => $hash,
            ':role'     => $role,
            ':niveau'   => $niveau,
        ]);
    }

    public function updateEmail(int $id, string $email): void
    {
        $stmt = $this->pdo->prepare("UPDATE compte SET email_publique = :email WHERE id_compte = :id");
        $stmt->execute([':email' => $email, ':id' => $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->pdo->prepare("UPDATE compte SET mot_de_passe = :hash WHERE id_compte = :id");
        $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM compte WHERE id_compte = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getNextId(): int
    {
        return (int) $this->pdo->query("SELECT COALESCE(MAX(id_compte), 0) + 1 FROM compte")->fetchColumn();
    }
}