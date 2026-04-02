<?php


class Statistiques extends Model
{
    /** @var string Nom de la table */
    protected string $table = '';

    /** @var string Clé primaire */
    protected string $primaryKey = '';

    // répartition durée, top wishlist, total offres, moyenne candidatures
    public static function getAllPostulants()
    {
        $postuleModel = new PostuleModel();
        return $postuleModel->getCandidatures([
            'attributes'    => [
                                'o.id_offre'            ,
                                'i.id_ident'            ,
                                'o.titre'               ,
                                'o.description'         ,
                                'o.date_offre'          ,
                                'o.base_remuneration'   ,
                                'e.nom'
                            ],
            'criteria'      => [],
            'order'         => '',
            'limit_offset'  => [],
        ]);
    }

    public static function getAllOffres()
    {
        $offreModel = new Offre();
        return $offreModel->getOffres([
            'attributes'    => [
                                'o.id_offre'            ,
                                'o.titre'               ,
                                'o.description'         ,
                                'o.date_offre'          ,
                                'o.base_remuneration'   ,
                                'e.nom'
                            ],
            'criteria'      => [],
            'order'         => 'date_offre ASC',
            'limit_offset'  => [],
        ]);
    }

    public static function getTopWishlist()
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                i.id_ident      ,
                i.nom           ,
                i.prenom        ,
                count(w.id_offre) AS nb
            FROM ident      i
            JOIN wishlist   w USING (id_ident)
            GROUP BY    i.id_ident      ,
                        i.nom           ,
                        i.prenom
            ORDER BY nb DESC
            LIMIT 3
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTopCandidatures()
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                i.id_ident      ,
                i.nom           ,
                i.prenom        ,
                count(p.id_offre) AS nb
            FROM ident      i
            JOIN postule    p USING (id_ident)
            GROUP BY    i.id_ident      ,
                        i.nom           ,
                        i.prenom
            ORDER BY nb DESC
            LIMIT 3
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
