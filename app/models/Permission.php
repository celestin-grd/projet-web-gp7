<?php

/**
 * Modèle pour la gestion des rôles et permissions.
 *
 * Permet de récupérer les rôles, les permissions disponibles, la matrice rôle/permission
 * et de sauvegarder cette matrice dans la base.
 */
class Permission extends Model
{
    protected string $table = 'permission';
    protected string $primaryKey = 'id'; // champ fictif, pas utilisé pour cette table

    /**
     * Retourne tous les rôles.
     *
     * @return array Liste des rôles (id_role, role, ...)
     */
    public function getRoles(): array
    {
        $sql = "SELECT * FROM role ORDER BY id_role";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne toutes les permissions déclarées.
     *
     * @return array Liste des permissions (champ 'permission')
     */
    public function getPermissions(): array
    {
        $sql = "SELECT permission FROM page_fonction ORDER BY permission";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne la matrice complète des permissions.
     *
     * @return array Tableau [id_role][permission] = allowed (true/false)
     */
    public function getMatrix(): array
    {
        $sql = "SELECT id_role, permission, allowed FROM permission";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $matrix = [];

        foreach ($rows as $row) {
            $matrix[(int)$row['id_role']][$row['permission']] = filter_var($row['allowed'], FILTER_VALIDATE_BOOLEAN);
        }

        return $matrix;
    }

    /**
     * Sauvegarde la matrice des permissions pour tous les rôles.
     *
     * @param array $data Tableau [roleId][permission] = "1"|"0" ou true/false
     * @return void
     */
    public function saveMatrix(array $data): void
    {
        $sql = "
            INSERT INTO permission(id_role, permission, allowed)
            VALUES(:role, :perm, :allowed)
            ON CONFLICT (id_role, permission)
            DO UPDATE SET allowed = :allowed
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $roleId => $permissions) {
            foreach ($permissions as $perm => $value) {
                $allowed = ($value === "1" || $value === true) ? "true" : "false";

                $stmt->execute([
                    'role' => $roleId,
                    'perm' => $perm,
                    'allowed' => $allowed
                ]);
            }
        }
    }
}
