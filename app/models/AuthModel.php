<?php

/**
 * Modèle pour la gestion de l'authentification et des permissions.
 *
 * Hérite de Model pour la connexion à la base et les opérations génériques.
 */
class AuthModel extends Model
{
    protected string $table = 'ident';
    protected string $primaryKey = 'id_ident';

    /**
     * Authentifie un utilisateur via email et mot de passe.
     *
     * @param string $email
     * @param string $password Mot de passe en clair (à sécuriser avec hash)
     * @return array|false Tableau des informations utilisateur ou false si échec
     */
    public function login(string $email, string $password): array|false
    {
        $sql = "
            SELECT i.*, r.role
            FROM ident i
            JOIN role r ON r.id_role = i.id_role
            WHERE i.email = :email
            AND i.valide = true
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // comparer les mots de passe hachés
        if (! password_verify($password, $user['passwd'])) {
            return false;
        }

        return $user;
    }

    /**
     * Récupère les permissions d'un rôle.
     *
     * @param int $roleId
     * @return array Tableau clé=permission, valeur=bool (allowed)
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT permission, allowed FROM permission WHERE id_role = :role and allowed=true";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role' => $roleId]);

        // PDO::FETCH_KEY_PAIR : clé = permission, valeur = allowed
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
