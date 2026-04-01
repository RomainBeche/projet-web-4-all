<?php

namespace Grp5\ProjetWeb4All\Models;

use PDO;

class Entreprises
{
    public function __construct(private PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT e.* FROM public.entreprise e
            ORDER BY COALESCE(e."rating", -1) DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM public.entreprise WHERE id_entreprise = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search(string $q): array
    {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT e.*
            FROM public.entreprise e
            WHERE
                to_tsvector('french',
                    coalesce(e.nom, '')         || ' ' ||
                    coalesce(e.description, '') || ' ' ||
                    coalesce(e.secteur, '')
                ) @@ to_tsquery('french', :q)
                OR e.nom ILIKE :qlike
            ORDER BY COALESCE(e."rating", -1) DESC
        SQL);
        $stmt->execute([':q' => $q . ':*', ':qlike' => '%' . $q . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crée entreprise + ville & adresse par cascade
    public function create(array $data, int $idCompte): void
    {
        $stmt = $this->pdo->prepare('SELECT id_ville FROM ville WHERE nom = :nom AND code_postal = :cp');
        $stmt->execute([':nom' => $data['nom_ville'], ':cp' => $data['code_postal']]);
        $ville = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ville) {
            $id_ville = $ville['id_ville'];
        } else {
            $maxVilleId = $this->pdo->query('SELECT COALESCE(MAX(id_ville), 0) + 1 FROM ville')->fetchColumn();
            $this->pdo->prepare('INSERT INTO ville (id_ville, nom, code_postal) VALUES (:id, :nom, :cp)')
                ->execute([':id' => $maxVilleId, ':nom' => $data['nom_ville'], ':cp' => $data['code_postal']]);
            $id_ville = $maxVilleId;
        }

        $maxAdresseId = $this->pdo->query('SELECT COALESCE(MAX(id_adresse), 0) + 1 FROM adresse')->fetchColumn();
        $this->pdo->prepare('INSERT INTO adresse (id_adresse, numero_rue, nom_rue, id_ville) VALUES (:id, :numero, :rue, :ville)')
            ->execute([':id' => $maxAdresseId, ':numero' => $data['numero_rue'], ':rue' => $data['nom_rue'], ':ville' => $id_ville]);

        $maxId = $this->pdo->query('SELECT COALESCE(MAX(id_entreprise), 0) + 1 FROM entreprise')->fetchColumn();
        $this->pdo->prepare('
            INSERT INTO entreprise (id_entreprise, nom, description, email, telephone, secteur, id_compte, id_adresse)
            VALUES (:id, :nom, :description, :email, :telephone, :secteur, :id_compte, :id_adresse)
        ')->execute([
            ':id'          => $maxId,
            ':nom'         => $data['nom'],
            ':description' => $data['description'],
            ':email'       => $data['email'],
            ':telephone'   => $data['telephone'],
            ':secteur'     => $data['secteur'],
            ':id_compte'   => $idCompte,
            ':id_adresse'  => $maxAdresseId,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $this->pdo->prepare('
            UPDATE entreprise
            SET nom = :nom, description = :description, email = :email,
                telephone = :telephone, secteur = :secteur
            WHERE id_entreprise = :id
        ')->execute([
            ':nom'         => $data['nom'],
            ':description' => $data['description'],
            ':email'       => $data['email'],
            ':telephone'   => $data['telephone'],
            ':secteur'     => $data['secteur'],
            ':id'          => $id,
        ]);
    }

    // Supprime candidatures + annonces + entreprise (cascade manuelle)
    public function delete(int $id): void
    {
        $this->pdo->prepare('
            DELETE FROM candidature
            WHERE id_annonce IN (SELECT id_annonce FROM annonce WHERE id_entreprise_appartient = :id)
        ')->execute([':id' => $id]);

        $this->pdo->prepare('DELETE FROM annonce WHERE id_entreprise_appartient = :id')
            ->execute([':id' => $id]);

        $this->pdo->prepare('DELETE FROM entreprise WHERE id_entreprise = :id')
            ->execute([':id' => $id]);
    }
}
