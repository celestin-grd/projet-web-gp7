<?php

/**
 * Routeur principal de l'application.
 *
 * - Analyse l'URL et appelle le contrôleur et la méthode correspondante.
 * - Vérifie automatiquement les permissions basées sur le rôle de l'utilisateur.
 * - Enregistre les permissions dynamiquement si elles n'existent pas.
 */
class Router
{
    /**
     * Traite la requête entrante.
     *
     * - Parse l'URI
     * - Détermine le contrôleur, la méthode et le paramètre
     * - Vérifie l'accès via les permissions
     * - Appelle dynamiquement le contrôleur
     *
     * @return void
     */
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        $param = null;
        // Gestion explicite de la racine du site
        if ($uri === '') {
            $controllerSegment = 'home';
            $method = 'index';
            $param = null;
        } elseif ($uri === 'sitemap.xml') {
            // Gestion spécifique de sitemap.xml
            $controllerSegment = 'static';
            $method = 'sitemap';
        } elseif ($uri === 'contact') {
            // Gestion spécifique de contact
            $controllerSegment = 'static';
            $method = 'contact';
        } elseif ($uri === 'mentions_legales') {
            // Gestion spécifique de mentions_legales
            $controllerSegment = 'static';
            $method = 'mentions_legales';
        } elseif ($uri === 'plan_site') {
            // Gestion spécifique de plan_site
            $controllerSegment = 'static';
            $method = 'plan_site';
        } else {
            $segments = explode('/', $uri);
            /*
            Exemple : /entreprise/modify/12
            [0] => entreprise
            [1] => modify
            [2] => 12
            */
            $controllerSegment = $segments[0] ?? 'home';
            $method = $segments[1] ?? 'index';
            $param = $segments[2] ?? null;
        }

        $controllerName = ucfirst($controllerSegment) . 'Controller';

        if (!class_exists($controllerName)) {
            http_response_code(404);
            die("Controller $controllerName not found");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            die("Method $method not found in $controllerName");
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Génération automatique de la permission (ex: entreprise_create)
        $permission = strtolower($controllerSegment . '_' . $method);
        $this->registerPermission($permission);

        // Vérification des droits d'accès
        $this->checkAccess($permission);

        // Appel dynamique avec ou sans paramètre
        if ($param !== null) {
            $controller->$method($param);
        } else {
            $controller->$method();
        }
    }

    /**
     * Vérifie si l'utilisateur a le droit d'accéder à la permission.
     *
     * - Redirige vers login si non connecté
     * - Retourne 403 si connecté mais permission refusée
     *
     * @param string $permission
     * @return bool True si accès autorisé
     */
    public function checkAccess(string $permission): bool
    {
        $roleId = Auth::roleId();
        if (!Auth::can($permission, $roleId)) {

            if (!Auth::check()) {
                header('Location: '. CDN . PREFIX . '/auth/login');
                exit;
            }
            header('Location: '. CDN . PREFIX . '/static/unauthorized');
            exit;
            //http_response_code(403);
            //die("Accès interdit : permission '$permission'");
        }

        return true;
    }

    /**
     * Enregistre une permission automatiquement dans la base si elle n'existe pas.
     *
     * @param string $permission Nom de la permission
     * @return void
     */
    private function registerPermission(string $permission): void
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("
            INSERT INTO page_fonction(permission)
            VALUES(:perm)
            ON CONFLICT DO NOTHING
        ");

        $stmt->execute(['perm' => $permission]);
    }
}
