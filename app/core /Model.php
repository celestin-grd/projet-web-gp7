<?php

/**
 * Classe abstraite de base pour les modèles.
 *
 * Fournit des méthodes CRUD génériques pour interagir avec la base de données.
 * Chaque modèle concret doit définir :
 * - $table : nom de la table
 * - $primaryKey : clé primaire
 */
abstract class Model
{
    /** @var PDO Instance de connexion PDO */
    protected PDO $db;

    /** @var string Nom de la table */
    protected string $table;

    /** @var string Clé primaire */
    protected string $primaryKey;

    /**
     * Constructeur.
     *
     * Initialise la connexion à la base de données via Database singleton.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retourne tous les enregistrements de la table.
     *
     * @return array Liste des enregistrements
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Trouve un enregistrement par sa clé primaire.
     *
     * @param mixed $id Valeur de la clé primaire
     * @return array|null Enregistrement ou null si non trouvé
     */
    public function findById($id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Trouve des enregistrements selon des critères.
     *
     * @param array $criteria Tableau clé => valeur (ex: ['nom' => 'ABC'])
     * @param string $order chaine de caractere contenant les critère d'ORDER BY (ex: 'nom ASC')
     * @param array $limit_offset Tableau clé => valeur (ex: ['limit' => 3, 'offset' => 0]
     * @return array Liste des enregistrements correspondants
     * exemple : $criteria = [
     *              ['id', 3, '='],
     *              ['nom', '%toto%', 'ILIKE'],
     *              ['age', 5, '>='],
     *          ];
     *
     *          $results = $model->findBy($criteria, 'nom ASC', [
     *              'limit' => 10,
     *              'offset' => 0
     *          ]);
     */
    public function findBy(array $criteria = [], string $order = "", array $limit_offset = []): array
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

        $sql = "SELECT * FROM {$this->table}";

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
     * Compte le nombre d'enregistrements correspondant à certains critères.
     *
     * @param array $criteria Tableau clé => valeur facultatif
     * @return int Nombre d'enregistrements correspondants
     */
    public function count(array $criteria = []): int
    {
        $conditions = [];
        $params = [];
        $i = 0;

        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'ILIKE'];

        foreach ($criteria as $crit) {

            [$field, $value, $operator] = $crit;

            $operator = strtoupper(trim($operator));

            // Sécurité opérateurs
            if (!in_array($operator, $allowedOperators)) {
                throw new InvalidArgumentException("Opérateur non autorisé : $operator");
            }

            $paramName = $field . '_' . $i++;

            $conditions[] = "$field $operator :$paramName";
            $params[$paramName] = $value;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);

        // Binding typé (identique à findBy)
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

        return (int) $stmt->fetchColumn();
    }


    /**
     * Insère des enregistrements
     *
     * @param array $columns => nom des colonnes
     * @param array $rows => valeurs à insérer
     * @return bool True si les enregistrements ont été insérés, false sinon
     * exemple : $columns = [id_offre', 'id_competence']
     *           $rows = [
     *               [1, 3],
     *               [1, 5],
     *               ...
     *           ]
     */
    public function insert(array $columns, array $rows): bool
    {
        if (empty($columns) || empty($rows)) {
            throw new InvalidArgumentException("Colonnes ou données vides");
        }

        $colString = implode(',', $columns);

        $valuesSql = [];
        $params = [];
        $i = 0;

        foreach ($rows as $row) {

            if (count($row) !== count($columns)) {
                throw new InvalidArgumentException("Nombre de valeurs incohérent avec les colonnes");
            }

            $placeholders = [];

            foreach ($row as $value) {
                $paramName = "p" . $i++;
                $placeholders[] = ":$paramName";
                $params[$paramName] = $value;
            }

            $valuesSql[] = '(' . implode(',', $placeholders) . ')';
        }

        $sql = "INSERT INTO {$this->table} ($colString) VALUES " . implode(',', $valuesSql) . " ON CONFLICT DO NOTHING";

        $stmt = $this->db->prepare($sql);

        // Binding typé (comme ton findBy)
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

        return $stmt->execute();
    }




    /**
     * Vérifie si un enregistrement existe par sa clé primaire.
     *
     * @param mixed $id Valeur de la clé primaire
     * @return bool True si l'enregistrement existe, false sinon
     */
    public function exists($id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = :id"
        );
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }


    public function existsBy(array $criteria = []): bool
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

        $sql = "SELECT 1 FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
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
        return (bool) $stmt->fetchColumn();
    }




    /**
     * Crée un nouvel enregistrement.
     *
     * @param array $data Tableau clé => valeur des colonnes
     * @return int|bool Integer representant le lastInsertId ou bien True si succès, false sinon
     */
    public function create(array $data): int|false
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns)
                VALUES ($placeholders)
                RETURNING {$this->primaryKey}";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($data);

        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : false;
    }

    /**
     * Met à jour un enregistrement existant.
     *
     * @param mixed $id Clé primaire de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool True si succès, false sinon
     */
    public function update($id, array $data): bool
    {

        $set = implode(',', array_map(fn ($k) => "$k = :$k", array_keys($data)));
        $data[$this->primaryKey] = $id;

        $sql = "UPDATE {$this->table} SET $set
                WHERE {$this->primaryKey} = :{$this->primaryKey}";
        //var_dump($sql); var_dump($data); exit;
        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
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
        return $stmt->execute();
    }

    /**
     * Supprime un enregistrement par sa clé primaire.
     *
     * @param mixed $id Clé primaire de l'enregistrement
     * @return bool True si succès, false sinon
     */
    public function delete($id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table}
             WHERE {$this->primaryKey} = :id"
        );

        return $stmt->execute(['id' => $id]);
    }


    /**
     * Supprime un enregistrement par sa clé primaire.
     *
     * @param mixed $id Clé primaire de l'enregistrement
     * @return bool True si succès, false sinon
     */
    public function deleteWithCriteria(array $criteria): bool
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

        $sql = "DELETE FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
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

        return $stmt->execute();
    }


    public function updateWithCriteria(array $data, array $criteria): bool
    {
        // Processing de $criteria afin de fabriquer la clause WHERE
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

        // Processing de $data afin de fabriquer la clause SET
        $set = implode(',', array_map(fn ($k) => "$k = :$k", array_keys($data)));

        $sql = "UPDATE {$this->table} SET $set ";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        //var_dump($sql); var_dump($data); exit;
        $stmt = $this->db->prepare($sql);

        // Binding typé pour la clause SET
        foreach ($data as $key => $value) {
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

        // Binding typé pour la clause WHERE
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

        return $stmt->execute();
    }


}
