<?php

namespace Grp5\ProjetWeb4All\Models;

use PDO;

class Favorites
{
    public function __construct(private PDO $pdo) {}

    public function findByCompte(int $idCompte): array
    {
        $stmt = $this->pdo->prepare('
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.favori f
            JOIN public.annonce a ON f.id_annonce = a.id_annonce
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE f.id_compte = :id_compte
            ORDER BY f.id_favori DESC
        ');
        $stmt->execute([':id_compte' => $idCompte]);
        $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($annonces as &$a) {
            $a['tags'] = json_decode($a['tags'] ?? '[]', true);
            $a['type'] = strtolower($a['type'] ?? '');
        }
        unset($a);
        return $annonces;
    }

    public function isFavorite(int $idCompte, int $idAnnonce): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT 1 FROM public.favori WHERE id_compte = :c AND id_annonce = :a
        ');
        $stmt->execute([':c' => $idCompte, ':a' => $idAnnonce]);
        return (bool) $stmt->fetch();
    }

    public function add(int $idCompte, int $idAnnonce): void
    {
        $this->pdo->prepare('
            INSERT INTO public.favori (id_compte, id_annonce)
            VALUES (:id_compte, :id_annonce) ON CONFLICT DO NOTHING
        ')->execute([':id_compte' => $idCompte, ':id_annonce' => $idAnnonce]);
    }

    public function remove(int $idCompte, int $idAnnonce): void
    {
        $this->pdo->prepare('
            DELETE FROM public.favori WHERE id_compte = :c AND id_annonce = :a
        ')->execute([':c' => $idCompte, ':a' => $idAnnonce]);
    }

    public function toggle(int $idCompte, int $idAnnonce): bool
    {
        if ($this->isFavorite($idCompte, $idAnnonce)) {
            $this->remove($idCompte, $idAnnonce);
            return false;
        }
        $this->add($idCompte, $idAnnonce);
        return true;
    }
}
