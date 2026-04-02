<?php

/**
 * Classe de base des contrôleurs.
 *
 * Fournit des méthodes utilitaires communes à tous les contrôleurs :
 * - Rendu des vues
 * - Redirections HTTP
 */
class Controller
{
    /**
     * Rend une vue avec des données.
     *
     * Délègue le rendu à la classe View en lui passant
     * le nom de la vue ainsi que les données associées.
     *
     * @param string $view Nom de la vue (ex: 'home/index')
     * @param array $data Données à transmettre à la vue
     *
     * @return void
     */
    protected function render($view, $data = [])
    {
        View::render($view, $data);
    }

    protected function url($path = '')
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }

    /**
     * Effectue une redirection HTTP.
     *
     * Envoie un header Location puis termine l'exécution du script.
     *
     * @param string $url URL de destination
     *
     * @return void
     */
    public function redirect(string $url): void
    {
        if (defined('PHPUNIT_RUNNING')) {
            return; // désactivé en test
        }
        header("Location: ". $this->url($url));
        exit;
    }
}
