<?php

namespace Grp5\ProjetWeb4All\Models;

use PDO;

class Note
{
    public function __construct(private PDO $pdo) {}

    public function getStats(int $idEntreprise): array
    {
        $stmt = $this->pdo->prepare('
            SELECT COALESCE(AVG(notation)::numeric, 0) AS rating, COUNT(*) AS nb_avis
            FROM note WHERE id_entreprise = :id
        ');
        $stmt->execute([':id' => $idEntreprise]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'rating'  => round((float)($result['rating'] ?? 0), 1),
            'nb_avis' => (int)($result['nb_avis'] ?? 0),
        ];
    }

    // Ajoute une note et met à jour les stats de l'entreprise
    public function add(int $idEntreprise, int $idCompte, int $note, string $comment = ''): bool
    {
        if ($note < 1 || $note > 5) return false;

        $ok = $this->pdo->prepare('
            INSERT INTO note (id_entreprise, notation, commentaire, id_compte, date_notation)
            VALUES (:id_entreprise, :notation, :commentaire, :id_compte, NOW())
        ')->execute([
            ':id_entreprise' => $idEntreprise,
            ':notation'      => $note,
            ':commentaire'   => $comment,
            ':id_compte'     => $idCompte,
        ]);

        if ($ok) $this->updateEntrepriseStats($idEntreprise);
        return $ok;
    }

    private function updateEntrepriseStats(int $idEntreprise): void
    {
        $this->pdo->prepare('
            UPDATE entreprise
            SET nombre_avis = (SELECT COUNT(*) FROM note WHERE id_entreprise = :id),
                rating      = ROUND((SELECT COALESCE(AVG(notation)::numeric, 0) FROM note WHERE id_entreprise = :id), 1)
            WHERE id_entreprise = :id
        ')->execute([':id' => $idEntreprise]);
    }
}