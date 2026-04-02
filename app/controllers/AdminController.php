<?php

/**
 * Contrôleur d'administration.
 *
 * Gère les fonctionnalités liées à l'administration du site,
 * notamment la gestion des rôles et des permissions.
 */
class AdminController extends Controller
{
    /**
     * Affiche et traite la matrice des permissions.
     *
     * - En GET : récupère les rôles, permissions et la matrice existante
     *   puis affiche la vue correspondante.
     * - En POST : enregistre la matrice des permissions envoyée par le formulaire
     *   puis redirige vers la page d'accueil.
     *
     * @return void
     */
    public function permissions()
    {

        // Traitement du formulaire (soumission des permissions)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            // Instanciation du modèle de permissions
            $permModel = new Permission();

            // Sauvegarde de la matrice envoyée (fallback sur tableau vide si absent)
            $permModel->saveMatrix($_POST['perm'] ?? []);

            // Redirection après traitement pour éviter la resoumission du formulaire
            $this->redirect('/home');
            return;
        }

        // Instanciation du modèle pour récupération des données
        $permModel = new Permission();

        // Récupération des rôles disponibles
        $roles = $permModel->getRoles();

        // Récupération des permissions disponibles
        $permissions = $permModel->getPermissions();

        // Récupération de la matrice des permissions (rôle x permission)
        $matrix = $permModel->getMatrix();

        // Rendu de la vue avec les données nécessaires
        $this->render('admin/permissions', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'roles'         => $roles,
            'permissions'   => $permissions,
            'matrix'        => $matrix
        ]);
    }

}
