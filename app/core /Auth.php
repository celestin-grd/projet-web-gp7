<?php

/**
 * Classe utilitaire d'authentification et d'autorisation.
 */
class Auth
{
    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check()
    {
        return isset($_SESSION['user']);
    }

    public static function role()
    {
        return $_SESSION['user']['role'] ?? 'guest';
    }

    public static function roleId()
    {
        if (!self::check()) {
            return 4; // guest
        }

        return $_SESSION['user']['id_role'];
    }

    /**
     * Vérifie si une permission est accordée à un rôle.
     *
     * Priorité :
     * 1. Session (si disponible)
     * 2. Base de données (fallback, ex: guest)
     */
    public static function can($permission, $roleId = null)
    {
        $roleId = $roleId ?? self::roleId();


        // 0. Cas permission in whitelist -> tjs autorisées pour tout le monde
        $whitelist = [
            'home_index',
            'auth_login',
            'auth_logout',
            'static_sitemap',
            'static_unauthorized',
        ];
        if (in_array($permission, $whitelist)) {
            return true;
        }

        // 1. Cas utilisateur connecté → session
        if (isset($_SESSION['permissions'])) {
            if (array_key_exists($permission, $_SESSION['permissions'])) {
                return true;
            } else {
                return false;
            }
        }

        // 2. Fallback DB (guest ou session absente)
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT count(*)
            FROM permission
            WHERE id_role = :role
            AND permission = :perm
            AND allowed = true
        ");

        $stmt->execute([
            'role' => $roleId,
            'perm' => $permission
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }
}
