<?php

/**
 * Modèle pour la table "postule".
 *
 */
class PostuleModel extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'postule';

    /** @var string Clé primaire */
    protected string $primaryKey = '';


    // public function getPostuleData(int $id): array
    // {
    //     // Requête pour récupérer la liste des offre postulées
    //     $sql = "
    //         SELECT
    //             p.id_ident          ,
    //             p.id_offre          ,
    //             p.date_postule      ,
    //             p.cv                ,
    //             p.lettre_motivation ,
    //             p.file_lm           ,
    //             p.file_cv           ,
    //             o.titre             ,
    //             o.description       ,
    //             o.base_remuneration ,
    //             o.date_offre        ,
    //             e.nom
    //         FROM postule    p
    //         JOIN offre      o using (id_offre)
    //         JOIN entreprise e using (id_entreprise)
    //         WHERE p.id_ident = :id
    //         AND o.valide = true
    //         AND e.valide = true
    //         AND p.valide = true
    //         ORDER BY p.date_postule ASC
    //     ";

    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute(['id' => $id]);

    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // public function getPostuleDataByOffreAndIdent(int $id_offre, int $id_ident): array
    // {
    //     // Requête pour récupérer la liste des offre postulées
    //     $sql = "
    //         SELECT
    //             p.*                 ,
    //             o.titre             ,
    //             o.description       ,
    //             o.base_remuneration ,
    //             o.date_offre        ,
    //             e.nom
    //         FROM postule    p
    //         JOIN offre      o using (id_offre)
    //         JOIN entreprise e using (id_entreprise)
    //         WHERE   o.id_offre = :id_offre
    //         AND     p.id_ident = :id_ident
    //         AND o.valide = true
    //         AND e.valide = true
    //         AND p.valide = true
    //         ORDER BY p.date_postule ASC
    //     ";

    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute(['id_offre' => $id_offre, 'id_ident' => $id_ident]);

    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function getCandidatures(array $data): array
    {
        // Requête pour récupérer la wishlist
        $attribs = implode(', ', $data['attributes']);
        $sql = "
            SELECT DISTINCT
                $attribs
            FROM postule            p
            INNER JOIN offre        o USING (id_offre)
            INNER JOIN entreprise   e USING (id_entreprise)
            INNER JOIN ident        i USING (id_ident)
            WHERE p.valide = true
            AND o.valide = true
            AND e.valide = true
            AND i.valide = true
            ";
        $conditions = [];
        $params = [];
        $i = 0;
        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'ILIKE'];
        foreach ($data['criteria'] as $crit) {
            // Format attendu : [champ, valeur, opérateur]
            [$field, $value, $operator] = $crit;
            $operator = strtoupper(trim($operator));
            // Sécurité : whitelist des opérateurs
            if (!in_array($operator, $allowedOperators)) {
                throw new InvalidArgumentException("Opérateur non autorisé : $operator");
            }
            // Paramètre unique (évite collision si même champ plusieurs fois)
            $paramName = $field . '_' . $i++;
            $paramName = str_replace('.', '_', $paramName);
            $conditions[] = "$field $operator :$paramName";
            $params[$paramName] = $value;
        }
        if (!empty($conditions)) {
            $sql .= " AND " . implode(' AND ', $conditions);
        }
        // ORDER BY (⚠️ ne pas binder → whitelist conseillé)
        if (!empty($data['order'])) {
            $sql .= " ORDER BY ".$data['order'];
        }
        // limit / offset
        if (!empty($data['limit_offset'])) {
            if (isset($data['limit_offset']['limit'])) {
                $sql .= " LIMIT :limit";
                $params['limit'] = (int)$data['limit_offset']['limit'];
            }
            if (isset($data['limit_offset']['offset'])) {
                $sql .= " OFFSET :offset";
                $params['offset'] = (int)$data['limit_offset']['offset'];
            }
        }

        $stmt = $this->db->prepare($sql);
        // Binding typé
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
            } elseif (is_int($value)) {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } elseif (is_null($value)) {
                $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
