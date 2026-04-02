<?php

/**
 * Gestionnaire du menu dynamique optimisé.
 *
 * - Charge le menu une seule fois par session.
 * - Compatible utilisateurs connectés et guests.
 * - Organise les éléments par menu et ordre.
 */
class Menu
{
    /**
     * Retourne la structure du menu pour l'utilisateur courant.
     *
     * @return array Structure du menu :
     * [
     *     'menu_name' => [
     *         ['label' => '...', 'url' => '/...'],
     *         ...
     *     ],
     *     ...
     * ]
     */
    public static function get($id_role = null): array
    {
        // Si déjà en session pour ce rôle, retourne directement
        if ($id_role === null && isset($_SESSION['menu'])) {
            return $_SESSION['menu'];
        }

        $db = Database::getInstance();
        // Si le rôle n’est pas passé, on prend celui de l’utilisateur actuel
        $role = $id_role ?? Auth::roleId();

        // Requête pour récupérer toutes les pages accessibles par le rôle
        $sql = "
            SELECT 
                pf.permission,
                pf.menu,
                pf.label,
                pf.url
            FROM page_fonction pf
            JOIN permission p
                ON p.permission = pf.permission
            WHERE p.id_role = :role
            AND p.allowed = true
            ORDER BY pf.menu_order, pf.item_order
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['role' => $role]);

        $rows = $stmt->fetchAll();
        $menu = [];

        foreach ($rows as $row) {
            $menuName = $row['menu'];
            if (!isset($menu[$menuName])) {
                $menu[$menuName] = [];
            }

            $addon = '';
            if ((string)($row['menu']) === 'Ident' && (string)($row['label']) === 'Modify') {
                $addon = $_SESSION['user']['id'];
            }
            $menu[$menuName][] = [
                'label' => $row['label'],
                'menu' => $row['menu'],
                'url' => '/' . ltrim($row['url'].'/'.$addon, '/'), // garantit /url absolue
            ];
        }

        if ($id_role === null) {
            // Sauvegarde du menu par rôle pour eviter les futures requetes
            $_SESSION['menu'] = $menu;
        }
        return $menu;
    }

    /**
     * Réinitialise le menu en session.
     *
     * Utile pour tests unitaires ou après changement de rôle/permissions.
     */
    public static function reset(): void
    {
        unset($_SESSION['menu']);
    }
}
