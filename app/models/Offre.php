<?php

/**
 * Modèle pour la table "offre".
 *
 * Fournit les opérations CRUD via Model et une méthode de recherche paginée.
 */
class Offre extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'offre';

    /** @var string Clé primaire */
    protected string $primaryKey = 'id_offre';

    /**
     * Recherche des offres selon des filtres et pagine les résultats.
     *
     * @param array $filters Tableau clé => valeur pour filtrer
     * @param int $page Page courante (>=1)
     * @param int $perPage Nombre d'enregistrements par page
     * @return array ['results' => array, 'total' => int]
     */
    public function search(array $filters, int $page = 1, int $perPage = 3): array
    {
        $params = [];
        if (!empty($filters['titre'])) {
            $params[] = ['titre', $filters['titre'], 'ILIKE'];
        }

        if (!empty($filters['description'])) {
            $params[] = ['description', $filters['description'], 'ILIKE'];
        }

        if (!empty($filters['remuneration'])) {
            $params[] = ['remuneration', $filters['remuneration'] * 0.9, '>='];
        }

        if (!empty($filters['date_offre'])) {
            $params[] = ['date_offre', $filters['date_offre'], '>='];
        }

        if (!empty($filters['id_entreprise'])) {
            $params[] = ['id_entreprise', $filters['id_entreprise'], '='];
        }

        if (is_bool($filters['valide'])) {
            $params[] = ['valide', (bool)($filters['valide']), '='];
        }

        // Compte le nombre de résulat pour la requête selective en cours
        $total = $this->count($params);

        // Pagination
        $offset = ($page - 1) * $perPage;
        $limit_offset['limit']    = (int)($perPage);
        $limit_offset['offset']   = (int)($offset);

        // Requete de recherche

        return [
            'results' => $this->findBy($params, 'titre ASC', $limit_offset),
            'total' => $total
        ];
    }

    public function getOffres(array $data): array
    {
        // Requête pour récupérer la wishlist
        $attribs = implode(', ', $data['attributes']);
        $sql = "
            SELECT DISTINCT
                $attribs
            FROM offre              o
            INNER JOIN entreprise   e USING (id_entreprise)
            WHERE o.valide = true
            AND e.valide = true
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
