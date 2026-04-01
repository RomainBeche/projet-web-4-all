<?php

namespace Grp5\ProjetWeb4All\Models;

use PDO;

class Annonces
{
    public function __construct(private PDO $pdo) {}

    private function hydrate(array $annonces): array
    {
        foreach ($annonces as &$a) {
            $a['tags'] = json_decode($a['tags'] ?? '[]', true);
            $a['type'] = strtolower($a['type'] ?? '');
        }
        unset($a);
        return $annonces;
    }

    // Récupère toutes les annonces en triant par nb_candidatures
    public function findAll(string $orderBy = 'a.id_annonce ASC'): array
    {
        $stmt = $this->pdo->query("
            SELECT a.*, e.nom AS entreprise_nom,
                   (SELECT COUNT(*) FROM candidature c WHERE c.id_annonce = a.id_annonce) AS nb_candidatures
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            ORDER BY $orderBy
        ");
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Recherche full-text + fallback ILIKE sur le nom d'entreprise
    public function search(string $q, string $orderBy = 'a.id_annonce ASC'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, e.nom AS entreprise_nom,
                   (SELECT COUNT(*) FROM candidature c WHERE c.id_annonce = a.id_annonce) AS nb_candidatures
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE
                to_tsvector('french',
                    coalesce(a.titre, '')       || ' ' ||
                    coalesce(a.description, '') || ' ' ||
                    coalesce(a.lieu, '')        || ' ' ||
                    coalesce(a.secteur, '')     || ' ' ||
                    coalesce(a.type::text, '')  || ' ' ||
                    coalesce(a.niveau, '')      || ' ' ||
                    coalesce(e.nom, '')
                ) @@ to_tsquery('french', :q)
                OR e.nom ILIKE :qlike
            ORDER BY $orderBy
        ");
        $stmt->execute([':q' => $q . ':*', ':qlike' => '%' . $q . '%']);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE a.id_annonce = :id
        ");
        $stmt->execute([':id' => $id]);
        $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$annonce) return false;
        $annonce['tags'] = json_decode($annonce['tags'] ?? '[]', true);
        $annonce['type'] = strtolower($annonce['type'] ?? '');
        return $annonce;
    }

    public function findByEntreprise(int $entrepriseId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE a.id_entreprise_appartient = :id
            ORDER BY a.id_annonce ASC
        ");
        $stmt->execute([':id' => $entrepriseId]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Construit une clause ORDER BY valide et sécurisée (whitelist)
    public function buildOrderBy(string $sort, string $dir): string
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return match($sort) {
            'duree'        => "CAST(REGEXP_REPLACE(a.duree, '[^0-9]', '', 'g') AS INTEGER) $dir",
            'likes'        => "a.vues $dir",
            'candidatures' => "nb_candidatures $dir",
            default        => 'a.id_annonce ASC',
        };
    }
}