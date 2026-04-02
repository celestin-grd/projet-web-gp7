<?php

require_once __DIR__ . '/../core/Controller.php';

/**
 * Contrôleur principal (page d'accueil).
 *
 * Gère l'affichage de la page d'accueil de l'application
 * ainsi que certaines redirections simples.
 */
class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil.
     *
     * - Si l'URL est exactement /home : redirige vers la racine (/)
     *   afin d'éviter les doublons d'URL.
     * - Sinon : affiche la vue principale.
     *
     * @return void
     */
    public function index()
    {
        // Évite le duplicate content en redirigeant /home vers /
        if ($_SERVER['REQUEST_URI'] === '/home') {
            $this->redirect('/');
        }

        // Rendu de la page d'accueil
        $this->render('home/index');
    }
}
