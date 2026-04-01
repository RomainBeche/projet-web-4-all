<?php

namespace Grp5\ProjetWeb4All\Models;

use PDO;

class Candidatures
{
    public function __construct(private PDO $pdo) {}

    public function hasApplied(int $idCompte, int $idAnnonce): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT 1 FROM candidature
            WHERE id_compte = :id_compte AND id_annonce = :id_annonce
        ');
        $stmt->execute([':id_compte' => $idCompte, ':id_annonce' => $idAnnonce]);
        return (bool) $stmt->fetch();
    }

    public function create(array $data): void
    {
        $maxId = $this->pdo->query('SELECT COALESCE(MAX(id_candidature), 0) + 1 FROM candidature')->fetchColumn();
        $stmt  = $this->pdo->prepare('
            INSERT INTO candidature (
                id_candidature, id_annonce, id_compte,
                formation, niveau, date_debut, duree,
                cv, lettre, portfolio, message,
                date_candidature
            ) VALUES (
                :id, :id_annonce, :id_compte,
                :formation, :niveau, :date_debut, :duree,
                :cv, :lettre, :portfolio, :message,
                NOW()
            )
        ');
        $stmt->execute([
            ':id'   => $maxId,
            ':id_annonce'       => $data['id_annonce'],
            ':id_compte'        => $data['id_compte'],
            ':formation'        => $data['formation'],
            ':niveau'           => $data['niveau'],
            ':date_debut'       => $data['date_debut'],
            ':duree'            => $data['duree'],
            ':cv'               => $data['cv'],
            ':lettre'           => $data['lettre'],
            ':portfolio'        => $data['portfolio'],
            ':message'          => $data['message'],
        ]);
    }

    // Récupère toutes les candidatures associées à un compte
    public function findByCompte(int $idCompte): array
    {
        $stmt = $this->pdo->prepare('
            SELECT c.*, a.titre, a.lieu, a.type,
                   a.duree AS annonce_duree, a.id_annonce,
                   e.nom AS entreprise_nom
            FROM candidature c
            JOIN annonce a ON c.id_annonce = a.id_annonce
            JOIN entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE c.id_compte = :id
            ORDER BY c.date_candidature DESC
        ');
        $stmt->execute([':id' => $idCompte]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Une candidature par ID, vérifiée par id_compte (ganranti accès sécurisé 
    // des détails d'une candidature depuis page "ma-candidatura")
    public function findByIdAndCompte(int $id, int $idCompte): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT c.*, a.titre, a.id_annonce, e.nom AS entreprise
            FROM candidature c
            JOIN annonce a ON c.id_annonce = a.id_annonce
            JOIN entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE c.id_candidature = :id
              AND c.id_compte = :user_id
        ');
        $stmt->execute([':id' => $id, ':user_id' => $idCompte]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Candidatures d'un étudiant (pour affichage depuis compte pilote)
    public function findByEtudiant(int $idCompte): array
    {
        $stmt = $this->pdo->prepare('
            SELECT c.*, a.titre, a.lieu, a.type, a.duree
            FROM candidature c
            JOIN annonce a ON c.id_annonce = a.id_annonce
            WHERE c.id_compte = :id
        ');
        $stmt->execute([':id' => $idCompte]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Utile pour supprimer une annonce si elle existe déjà
    public function deleteByUserAndAnnonce(int $idCompte, int $idAnnonce): void
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM public.candidature
            WHERE id_compte = :c AND id_annonce = :a
        ');
        $stmt->execute([':c' => $idCompte, ':a' => $idAnnonce]);
    }
}