<?php

/**
 * Contrôleur d'authentification.
 *
 * Gère les actions liées à l'authentification des utilisateurs :
 * connexion et déconnexion.
 */
class AuthController extends Controller
{
    /**
     * Gère la connexion utilisateur.
     *
     * - En GET : affiche le formulaire de connexion.
     * - En POST : tente d'authentifier l'utilisateur avec les identifiants fournis.
     *   - En cas d'échec : réaffiche le formulaire avec un message d'erreur.
     *   - En cas de succès : enregistre les informations utilisateur et ses permissions
     *     en session puis redirige vers la page d'accueil.
     *
     * @return void
     */
    public function login()
    {
        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }

            // Validation
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'password'  => ['required', 'alpha'],
                'email'     => ['required', 'email'],

            ]);

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('auth/login', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'result'        => ['email' => $_POST['email']]
                ]);
            }

            // Récupération des données du formulaire
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;

            // Tentative d'authentification via le modèle
            $auth = new AuthModel();
            $user = $auth->login($email, $password);

            // Si l'authentification échoue
            if (!$user) {
                return $this->render('auth/login', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => [ ['Identifiants invalides'] ],
                    'result'        => [ 'email' => $_POST['email'] ],
                ]);

            }

            // Stockage des informations utilisateur en session
            $_SESSION['user'] = [
                'id'        => $user['id_ident'],
                'nom'       => $user['nom'],
                'prenom'    => $user['prenom'],
                'id_role'   => $user['id_role'],
                'role'      => $user['role'],
            ];
            // Chargement en cache des menus
            Menu::reset();
            Menu::get($user['id_role']);
            // Chargement et stockage des permissions associées au rôle
            $_SESSION['permissions'] = $auth->getPermissions($user['id_role']);

            // Redirection après connexion réussie
            $this->redirect('home/index');

        }

        // Affichage du formulaire de connexion (GET)
        $this->render('auth/login', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'result'        => [ 'email' => null ],
        ]);
    }


    /**
     * Déconnecte l'utilisateur courant.
     *
     * Détruit la session d'authentification puis redirige vers la page d'accueil.
     *
     * @return void
     */
    public function logout()
    {
        // Appel à la logique de déconnexion (suppression session, etc.)
        Auth::logout();

        // Redirection vers l'accueil
        header('Location: '. CDN . PREFIX . '/home/index');
        exit;
    }

}
