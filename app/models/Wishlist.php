<?php

/**
 * Modèle pour la table "wishlist".
 *
 */
class Wishlist extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'wishlist';

    /** @var string Clé primaire */
    protected string $primaryKey = '';


    public function getWishlist(int $id): array
    {
        // Requête pour récupérer la wishlist
        $sql = "
            SELECT 
                w.id_ident          ,
                w.id_offre          ,
                w.date_wishlist     ,
                o.titre             ,
                o.description       ,
                o.base_remuneration ,
                o.date_offre        ,
                e.nom
            FROM wishlist   w
            JOIN offre      o using (id_offre)
            JOIN entreprise e using (id_entreprise)
            WHERE w.id_ident = :id
            AND o.valide = true
            AND e.valide = true
            ORDER BY w.date_wishlist ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
