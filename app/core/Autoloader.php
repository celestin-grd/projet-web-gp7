<?php

/**
 * Autoloader de l'application.
 *
 * Permet le chargement automatique des classes sans avoir besoin
 * d'inclure manuellement les fichiers avec require/include.
 *
 * Fonctionnement :
 * - Recherche le fichier correspondant au nom de la classe
 * - Parcourt plusieurs répertoires (core, models, controllers)
 * - Inclut le premier fichier trouvé
 */
class Autoloader
{
    /**
     * Enregistre l'autoloader auprès de PHP.
     *
     * Utilise spl_autoload_register pour intercepter
     * l'instanciation de classes non encore chargées.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(function ($class) {

            // Répertoires dans lesquels chercher les classes
            $paths = [
                __DIR__ . '/',                // core
                __DIR__ . '/../models/',      // models
                __DIR__ . '/../controllers/'  // controllers
            ];

            // Parcours des chemins pour trouver le fichier correspondant
            foreach ($paths as $path) {
                $file = $path . $class . '.php';

                // Si le fichier existe, on le charge
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }

            // Optionnel : log si classe introuvable (utile en debug)
            // error_log("Classe non trouvée : " . $class);
        });
    }
}
