<?php

/**
 * Gestionnaire de connexion à la base de données.
 *
 * Implémente le pattern Singleton afin de garantir
 * une seule instance de connexion PDO dans l'application.
 */
class Database
{
    /**
     * Instance unique de PDO.
     *
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     */
    private function __construct()
    {
    }

    /**
     * Retourne l'instance unique de la connexion PDO.
     *
     * Initialise la connexion si elle n'existe pas encore.
     *
     * @return PDO
     * @throws PDOException En cas d'erreur de connexion
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $db   = $_ENV['DB_NAME'];

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";


            try {
                self::$instance = new PDO(
                    $dsn,
                    $user,          // utilisateur
                    $pass,          // mot de passe
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false, // sécurité + perf
                    ]
                );
            } catch (PDOException $e) {
                // En production → ne jamais afficher les détails
                die("Erreur de connexion à la base de données.");
            }
        }

        return self::$instance;
    }
}
