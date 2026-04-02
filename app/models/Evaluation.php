<?php

/**
 * Modèle pour la table "competence".
 *
 * Fournit les opérations CRUD via Model et une méthode de recherche paginée.
 */
class Evaluation extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'evaluation';

    /** @var string Clé primaire */
    protected string $primaryKey = '';


    public function evaluate(int $id_entreprise, int $evaluation, string $commentaire = ''): bool
    {
        // D'abord on supprime toutes les compétences requises pour cette offre si elles existaient
        $evaluationModel = $this->getEvaluationModel();
        $evaluationModel->deleteWithCriteria([
            ['id_ident', $_SESSION['user']['id'], '='],
            ['id_entreprise', $id_entreprise, '=']
        ]);

        // On prepare les datas ) insérer
        $rows = [
            [
                $_SESSION['user']['id'],
                $id_entreprise,
                $evaluation,
                (new DateTime())->format('Y-m-d H:i:s'),
                $commentaire
            ],
        ];

        // On insère les data
        return $evaluationModel->insert(['id_ident', 'id_entreprise', 'note', 'date_evaluation', 'commentaire'], $rows);
    }


    public function moyenne(int $id_entreprise): array
    {
        $ret = ['moyenne' => 0, 'count' => 0];
        $sql = "SELECT COUNT(*) AS nbre, ROUND(AVG(note)::numeric, 2) AS moyenne FROM {$this->table} WHERE id_entreprise =  :id_entreprise;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_entreprise' => $id_entreprise]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            $ret = [
                'moyenne'   => $r['moyenne'],
                'nbre'      => $r['nbre'],
            ];
        }
        // On insère les data
        return $ret;
    }


    public function search(int $id, int $page = 1, int $perPage = 3): array
    {
        $params = [];
        $params[] = ['id_entreprise', $id, '='];
        // Compte le nombre de résulat pour la requête selective en cours
        $total = $this->count($params);
        // Pagination
        $offset = ($page - 1) * $perPage;
        $limit_offset['limit']    = (int)($perPage);
        $limit_offset['offset']   = (int)($offset);
        // Requete de recherche
        return [
            'results' => $this->getEvaluations($params, 'date_evaluation ASC', $limit_offset),
            'total' => $total
        ];
    }


    public function getEvaluations(array $criteria = [], string $order = "", array $limit_offset = []): array
    {
        $conditions = [];
        $params = [];
        $i = 0;
        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'ILIKE'];
        foreach ($criteria as $crit) {
            // Format attendu : [champ, valeur, opérateur]
            [$field, $value, $operator] = $crit;
            $operator = strtoupper(trim($operator));
            // Sécurité : whitelist des opérateurs
            if (!in_array($operator, $allowedOperators)) {
                throw new InvalidArgumentException("Opérateur non autorisé : $operator");
            }
            // Paramètre unique (évite collision si même champ plusieurs fois)
            $paramName = $field . '_' . $i++;
            $conditions[] = "$field $operator :$paramName";
            $params[$paramName] = $value;
        }
        $sql = "SELECT 
                        e.note              AS note             ,
                        e.date_evaluation   AS date_evaluation  ,
                        e.commentaire       AS commentaire      ,
                        i.nom               AS nom              ,
                        i.prenom            AS prenom           ,
                        i.email             AS email            ,
                        ent.nom             AS entreprise
                FROM evaluation         e
                INNER JOIN ident        i   USING (id_ident)
                INNER JOIN entreprise   ent USING (id_entreprise)";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        // ORDER BY (⚠️ ne pas binder → whitelist conseillé)
        if (!empty($order)) {
            $sql .= " ORDER BY $order";
        }
        // limit / offset
        if (!empty($limit_offset)) {
            if (isset($limit_offset['limit'])) {
                $sql .= " LIMIT :limit";
                $params['limit'] = (int)$limit_offset['limit'];
            }
            if (isset($limit_offset['offset'])) {
                $sql .= " OFFSET :offset";
                $params['offset'] = (int)$limit_offset['offset'];
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

    /**
     * Fournit une instance du modèle Evaluation.
     *
     * Méthode isolée pour faciliter le mock en test.
     *
     * @return Evaluation
     */
    protected function getEvaluationModel()
    {
        return new Evaluation();
    }

}
