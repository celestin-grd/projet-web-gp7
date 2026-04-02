<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Datanormalizer.php';

/**
 * Contrôleur de gestion des entreprises.
 *
 * Gère les opérations CRUD ainsi que la recherche avec pagination :
 * - Création
 * - Recherche (avec filtres et pagination)
 * - Modification
 * - Suppression
 */
class EntrepriseController extends Controller
{
    /**
     * Création d'une entreprise.
     *
     * - En GET : affiche le formulaire de création.
     * - En POST : valide les données puis crée une entreprise.
     *   - En cas d'erreur : réaffiche le formulaire avec les erreurs.
     *   - En cas de succès : redirige vers la page de recherche.
     *
     * @return void
     */
    public function create()
    {
        $filters = [
            'nom'           => null,
            'description'   => null,
            'telephone'     => null,
            'email'         => null,
            'valide'        => true,
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            // Données filtrées pour pré-remplissage en cas d'erreur
            $filters = [
                'nom'           => $_POST['nom'] ?? null,
                'description'   => $_POST['description'] ?? null,
                'telephone'     => $_POST['telephone'] ?? null,
                'email'         => $_POST['email'] ?? null,
                'valide'        => isset($_POST['valide']) ? true : false,
            ];

            // Validation des données
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'nom'           => ['required', 'alpha_prime'],
                'description'   => ['required', 'txt'],
                'telephone'     => ['required', 'phone'],
                'email'         => ['required', 'email']
            ]);

            // Retour formulaire avec erreurs
            if (!$valid) {
                return $this->render('entreprise/create', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'filters'       => $filters,
                ]);
            }

            // Création en base
            $entreprise = $this->getEntrepriseModel();
            $id_entreprise = $entreprise->create($filters);

            // $evaluationModel = new Evaluation();
            // $evaluationModel->evaluate($id_entreprise, null, '');
            // Redirection après succès
            $this->redirect('/entreprise/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('entreprise/create', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'filters'       => $filters
        ]);
    }

    /**
     * Recherche d'entreprises avec filtres et pagination.
     *
     * - En GET : affiche la page de recherche vide.
     * - En POST : applique les filtres, valide les entrées et retourne les résultats paginés.
     *
     * @return void
     */
    public function recherche()
    {
        $filters = [
            'nom'           => null,
            'description'   => null,
            'telephone'     => null,
            'email'         => null,
            'valide'        => true,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            // Gestion de la pagination
            $page = $_POST['page'] ?? 1;
            $page = max(1, (int)$page);

            $perPage = ITEM_PER_PAGES;

            // Filtres de recherche
            $filters = [
                'nom'           => $_POST['nom'] ?? null,
                'description'   => $_POST['description'] ?? null,
                'telephone'     => $_POST['telephone'] ?? null,
                'email'         => $_POST['email'] ?? null,
                'valide'        => isset($_POST['valide']) ? true : false,
            ];
            $validator = new Validator();
            $valid = true;

            // Validation conditionnelle des champs
            if (!empty($_POST['nom'])) {
                $valid = $validator->validate($_POST, ['nom' => ['required', 'alpha']]);
            }

            if ($valid && !empty($_POST['description'])) {
                $valid = $validator->validate($_POST, [
                    'description'   => ['required', 'txt'],
                    'telephone'     => ['required', 'phone'],
                    'email'         => ['required', 'email']
                ]);
            }

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('entreprise/recherche', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'filters'       => $filters,
                    'results'       => [],
                    'page'          => 1,
                    'totalPages'    => 0,
                    'pagination'    => [],
                ]);
            }

            // Exécution de la recherche
            $entreprise = $this->getEntrepriseModel();
            $data = $entreprise->search($filters, $page, $perPage);

            $results = $data['results'];
            $total = $data['total'];

            // Calcul du nombre total de pages
            $totalPages = ceil($total / $perPage);

            $resultat = [];
            $evaluationModel = new Evaluation();
            $postuleModel = new PostuleModel();
            $offreModel = new Offre();
            foreach ($results as $res) {
                $r = $evaluationModel->moyenne($res['id_entreprise']);
                $resultat[$res['id_entreprise']] = $res;
                $resultat[$res['id_entreprise']]['eval_moyenne']    = $r['moyenne'];
                $resultat[$res['id_entreprise']]['eval_nbre']       = $r['nbre'];
                $resultat[$res['id_entreprise']]['eval_stars']      = floor($r['moyenne']);

                $notation = $evaluationModel->findBy([
                    ['id_ident'     , $_SESSION['user']['id']   , "="],
                    ['id_entreprise', $res['id_entreprise']     , "="],
                ]);
                if (isset($notation[0])) {
                    $resultat[$res['id_entreprise']]['in_evaluate'] = 1;
                } else {
                    $resultat[$res['id_entreprise']]['in_evaluate'] = 0;
                }

                // gestion des boutons par entreprise
                $resultat[$res['id_entreprise']]['buttons'] = [
                    'evaluate' => View::button([
                                                'permission' => 'evaluation_create',
                                                'url'        => '/evaluation/create/'.$res['id_entreprise'],
                                                'class'      => 'wishlist',
                                                'icon'       => '☆',
                                                'title'      => 'Evaluer',
                                            ], false),                  // retourne string
                    'edit'     => View::button([
                                                'permission' => 'entreprise_modify',
                                                'url'        => '/entreprise/modify/'.$res['id_entreprise'],
                                                'class'      => 'edit',
                                                'icon'       => '✏',
                                                'title'      => 'Modifier',
                                            ], false),                  // retourne string
                    'delete'   => View::button([
                                                'permission' => 'entreprise_delete',
                                                'url'        => '/entreprise/delete/'.$res['id_entreprise'],
                                                'class'      => 'delete',
                                                'icon'       => '🗑',
                                                'title'      => 'Supprimer',
                                                'attributes' => [
                                                    'onclick' => "return confirm('Confirmer la suppression ?');"
                                                ]
                                            ], false),                 // retourne string
                ];
                $candidatures = $postuleModel->getCandidatures([
                    'attributes'    => [
                                        'o.id_offre'
                                    ],
                    'criteria'      => [
                                        ['e.id_entreprise', $res['id_entreprise']   , '='],
                                    ],
                    'order'         => '',
                    'limit_offset'  => [],
                ]);
                $resultat[$res['id_entreprise']]['candidatures'] = count($candidatures);

                $offres = $offreModel->getOffres([
                    'attributes'    => [
                                        'o.id_offre'
                                    ],
                    'criteria'      => [
                                        ['e.id_entreprise', $res['id_entreprise']   , '='],
                                    ],
                    'order'         => '',
                    'limit_offset'  => [],
                ]);
                $resultat[$res['id_entreprise']]['offres'] = count($offres);
            }


            // Rendu des résultats
            return $this->render('entreprise/recherche', [
                'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                'errors'        => [],
                'filters'       => $filters,
                'results'       => $resultat,
                'page'          => $page,
                'totalPages'    => $totalPages,
                'pagination'    => View::buildPagination($page, $totalPages),
            ]);
        }

        // Affichage initial (GET)
        $this->render('entreprise/recherche', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'filters'       => $filters,
            'results'       => [],
            'page'          => 1,
            'totalPages'    => 0,
            'pagination'    => [],
        ]);
    }

    /**
     * Modification d'une entreprise existante.
     *
     * @param int $id Identifiant de l'entreprise
     *
     * - Vérifie l'existence de l'entreprise
     * - En GET : affiche le formulaire pré-rempli
     * - En POST : valide puis met à jour les données
     *
     * @return void
     * @throws \Exception Si l'entreprise est introuvable (en environnement de test)
     */
    public function modify($id)
    {
        $entrepriseModel = $this->getEntrepriseModel();
        $old_entreprise = $entrepriseModel->findById($id);


        // Vérification existence
        if (!$old_entreprise) {
            if (defined('PHPUNIT_RUNNING')) {
                throw new \Exception("Entreprise introuvable");
            }
            http_response_code(404);
            die("Entreprise introuvable");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            // Données modifiées
            $entreprise = [
                'nom'           => $_POST['nom'] ?? null,
                'description'   => $_POST['description'] ?? null,
                'telephone'     => $_POST['telephone'] ?? null,
                'email'         => $_POST['email'] ?? null,
                'valide'        => (bool) $_POST['valide'],
            ];

            // Validation
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'nom'           => ['required', 'alpha'],
                'description'   => ['required', 'txt'],
                'telephone'     => ['required', 'phone'],
                'email'         => ['required', 'email']
            ]);

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('entreprise/modify', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'entreprise'    => $entreprise,
                ]);
            }

            // Verification s'il y a eu une modif ou pas
            $schema = [
                'nom'           => 'string',
                'description'   => 'string',
                'telephone'     => 'string',
                'email'         => 'string',
                'valide'        => 'bool',
            ];
            $cleanPost  = Datanormalizer::normalizeWithSchema($entreprise, $schema);
            $cleanDb    = Datanormalizer::normalizeWithSchema($old_entreprise, $schema);
            if ($cleanPost !== $cleanDb) {
                // il y a une différence entre la data en sgbd et la data du formulaire
                $entreprise['valide_id_ident'] = $_SESSION['user']['id'];
                $entreprise['valide_lastupdate'] = (new DateTime())->format('Y-m-d H:i:s');
            }

            // Nettoyage des données avec update
            foreach ($entreprise as $key => $value) {
                if ($value === '') {
                    $entreprise[$key] = null;
                }
            }
            // Mise à jour
            $entrepriseModel->update($id, $entreprise);

            // Redirection après succès
            $this->redirect('/entreprise/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('entreprise/modify', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'entreprise'    => $old_entreprise,
            'errors'        => [],
        ]);
    }


    /**
     * Suppression d'une entreprise.
     *
     * @param int $id Identifiant de l'entreprise
     *
     * Supprime l'entité puis redirige vers la liste.
     *
     * @return void
     */
    public function delete($id)
    {
        $entrepriseModel = $this->getEntrepriseModel();

        $entreprise = [
            'valide_id_ident'   => $_SESSION['user']['id'],
            'valide'            => false,
            'valide_lastupdate' => (new DateTime())->format('Y-m-d H:i:s'),
        ];
        // Mise à jour
        $entrepriseModel->update($id, $entreprise);

        //$entrepriseModel->delete($id);

        $this->redirect('/entreprise/recherche');
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
    // protected function redirect($url)
    // {
    //     if (defined('PHPUNIT_RUNNING')) {
    //         return; // désactivé en test
    //     }
    //     header("Location: ". $this->url($url));
    //     exit;
    // }

    /**
     * Fournit une instance du modèle Entreprise.
     *
     * Méthode isolée pour faciliter le mock en test.
     *
     * @return Entreprise
     */
    protected function getEntrepriseModel()
    {
        return new Entreprise();
    }
}
