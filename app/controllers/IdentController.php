<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Datanormalizer.php';

/**
 * Contrôleur de gestion des identifiants.
 *
 * Gère les opérations CRUD ainsi que la recherche avec pagination :
 * - Création
 * - Recherche (avec filtres et pagination)
 * - Modification
 * - Suppression
 */
class IdentController extends Controller
{
    /**
     * Création d'un identifiant.
     *
     * - En GET : affiche le formulaire de création.
     * - En POST : valide les données puis crée un identifiant.
     *   - En cas d'erreur : réaffiche le formulaire avec les erreurs.
     *   - En cas de succès : redirige vers la page de recherche.
     *
     * @return void
     */
    public function create()
    {
        $roleModel = new Role();
        $roles = $roleModel->findBy([['id_role', Auth::roleId(), '>=' ]], '', []);
        $filters = [
            'nom'       => null,
            'prenom'    => null,
            'email'     => null,
            'passwd'    => null,
            'id_role'   => -1,
            'valide'    => true,
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            // Données filtrées pour pré-remplissage en cas d'erreur
            $filters = [
                'nom'       => $_POST['nom'] ?? null,
                'prenom'    => $_POST['prenom'] ?? null,
                'email'     => $_POST['email'] ?? null,
                'passwd'    => $_POST['passwd'] ?? null,
                'id_role'   => $_POST['id_role'] ?? null,
                'valide'    => isset($_POST['valide']) ? true : false,
            ];

            // Validation des données
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'nom'       => ['required', 'alpha'],
                'prenom'    => ['required', 'alpha'],
                'id_role'   => ['required', 'integerpositif'],
                'email'     => ['required', 'email'],
                'passwd'    => ['required', 'alpha'],
            ]);

            // Retour formulaire avec erreurs
            if (!$valid) {
                return $this->render('ident/create', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'filters'       => $filters,
                    'roles'         => $roles,
                ]);
            }
            $filters['passwd'] = password_hash($_POST['passwd'], PASSWORD_DEFAULT);

            // Création en base
            $ident = $this->getIdentModel();
            $ident->create($filters);

            // Redirection après succès
            $this->redirect('/ident/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('ident/create', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'roles'         => $roles,
            'filters'       => $filters
        ]);
    }

    /**
     * Recherche d'identifiant avec filtres et pagination.
     *
     * - En GET : affiche la page de recherche vide.
     * - En POST : applique les filtres, valide les entrées et retourne les résultats paginés.
     *
     * @return void
     */
    public function recherche()
    {
        $roleModel = new Role();
        $roles = $roleModel->findBy([['id_role', Auth::roleId(), '>=' ]], '', []);
        $allroles = $roleModel->findBy([], '', []);
        $rols = [];
        foreach ($allroles as $rol) {
            $rols[$rol['id_role']] = $rol['role'];
        }
        $filters = [
            'nom'       => null,
            'prenom'    => null,
            'email'     => null,
            'passwd'    => null,
            'id_role'   => -1,
            'valide'    => true,
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
                'prenom'        => $_POST['prenom'] ?? null,
                'email'         => $_POST['email'] ?? null,
                'id_role'       => $_POST['id_role'] ?? -1,
                'valide'        => isset($_POST['valide']) ? true : false,
            ];
            $validator = new Validator();
            $valid = true;

            // Validation conditionnelle des champs
            if (!empty($_POST['nom'])) {
                $valid = $validator->validate($_POST, ['nom' => ['required', 'alpha']]);
            }

            if ($valid && !empty($_POST['prenom'])) {
                $valid = $validator->validate($_POST, [
                    'prenom' => ['required', 'alpha'],
                    'email' => ['required', 'email'],
                    'id_role' => ['required', 'integer'],
                ]);
            }

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('ident/recherche', [
                    'csrf_token' => $_SESSION['csrf_token'] ?? '',
                    'errors'    => $validator->errors(),
                    'filters'   => $filters,
                    'roles'     => $roles,
                    'results'   => [],
                    'page'      => 1,
                    'totalPages' => 0,
                    'pagination' => [],
                ]);
            }

            // Exécution de la recherche
            $ident = $this->getIdentModel();
            $data = $ident->search($filters, $page, $perPage);

            $results = $data['results'];
            $total = $data['total'];

            // Calcul du nombre total de pages
            $totalPages = ceil($total / $perPage);

            $wishlistModel = new Wishlist();
            $postuleModel = new PostuleModel();
            // Dans le cas ou un role est identique à celui qui consulte
            // Alors possibilite de modifier/supprimer que sa propre fiche
            foreach ($results as &$item) {
                if ($item['id_role'] == Auth::user()['id_role']
                        &&  $item['id_ident'] != Auth::user()['id']
                ) {
                    $item['not_me'] = 1;
                } else {
                    $item['not_me'] = 0;
                }
                $item['nom_role'] = $rols[$item['id_role']];
                $item['buttons'] = [
                    'edit'     => View::button([
                                                'permission' => 'ident_modify',
                                                'url'        => '/ident/modify/'.$item['id_ident'],
                                                'class'      => 'edit',
                                                'icon'       => '✏',
                                                'title'      => 'Modifier',
                                            ], false),                  // retourne string
                    'delete'   => View::button([
                                                'permission' => 'ident_delete',
                                                'url'        => '/ident/delete/'.$item['id_ident'],
                                                'class'      => 'delete',
                                                'icon'       => '🗑',
                                                'title'      => 'Supprimer',
                                                'attributes' => [
                                                    'onclick' => "return confirm('Confirmer la suppression ?');"
                                                ]
                                            ], false),                 // retourne string
                ];

                $wishlist = $wishlistModel->getWishlist($item['id_ident']);
                $item['wishlist_nb'] = count($wishlist);

                $postule = $postuleModel->getCandidatures([
                    'attributes'    => [
                                        'p.*'                 ,
                                        'o.titre'             ,
                                        'o.description'       ,
                                        'o.base_remuneration' ,
                                        'o.date_offre'        ,
                                        'e.nom'
                                    ],
                    'criteria'      => [
                                        ['p.id_ident', $item['id_ident']   , '='],
                                    ],
                    'order'         => 'p.date_postule ASC',
                    'limit_offset'  => [],
                ]);
                $item['postule_nb'] = count($postule);
            }
            unset($item); // important

            // Rendu des résultats
            return $this->render('ident/recherche', [
                'csrf_token' => $_SESSION['csrf_token'] ?? '',
                'errors'    => [],
                'filters'   => $filters,
                'roles'     => $roles,
                'results'   => $results,
                'page'      => $page,
                'totalPages' => $totalPages,
                'pagination' => View::buildPagination($page, $totalPages),
            ]);
        }

        // Affichage initial (GET)
        $this->render('ident/recherche', [
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
            'errors'    => [],
            'filters'   => $filters,
            'roles'     => $roles,
            'results'   => [],
            'page'      => 1,
            'totalPages' => 0,
            'pagination' => [],
         ]);
    }

    /**
     * Modification d'un identifiant existant.
     *
     * @param int $id Identifiant
     *
     * - Vérifie l'existence de l'identifiant
     * - En GET : affiche le formulaire pré-rempli
     * - En POST : valide puis met à jour les données
     *
     * @return void
     * @throws \Exception Si l'entreprise est introuvable (en environnement de test)
     */
    public function modify($id)
    {
        $identModel = $this->getIdentModel();
        $old_ident = $identModel->findById($id);

        if ($old_ident['id_role'] < Auth::roleId()) {
            // Interdiction de modifier une fiche dont le role est < à moi même (escalade de droit)
            $this->redirect('/ident/recherche');
        }
        if ($old_ident['id_role'] > Auth::roleId()) {
            // Possibilité de modifoer des fiche dont le role est > à moi (subalternes)
        } elseif ($old_ident['id_role'] == Auth::roleId()) {
            if ((int)($id) !== (int)($_SESSION['user']['id'])) {
                // Interdiction de modifier une fiche possédant le même role que moi
                $this->redirect('/ident/recherche');
            }
        }


        $etudiants = $identModel->getEtudiants();

        $etudiantModel = new Etudiant();
        $etudiantsSelectionnes = $etudiantModel->getEtudiantsSelectionnes();

        $wishlistModel = new Wishlist();
        $wishlist = $wishlistModel->getWishlist($id);

        $postuleModel = new PostuleModel();
        $postule = $postuleModel->getCandidatures([
            'attributes'    => [
                                'p.*'                 ,
                                'o.titre'             ,
                                'o.description'       ,
                                'o.base_remuneration' ,
                                'o.date_offre'        ,
                                'e.nom'
                            ],
            'criteria'      => [
                                ['p.id_ident', $id   , '='],
                            ],
            'order'         => 'p.date_postule ASC',
            'limit_offset'  => [],
        ]);


        foreach ($postule as &$post) {
            $id_offre = $post['id_offre'];
            $post['buttons'] = [
                'edit'     => View::button([
                                            'permission' => 'postule_modify',
                                            'url'        => '/postule/modify/'.$id_offre,
                                            'class'      => 'edit',
                                            'icon'       => '✏',
                                            'title'      => 'Modifier',
                                        ], false),                  // retourne string
                'delete'   => View::button([
                                            'permission' => 'postule_delete',
                                            'url'        => '/postule/delete/'.$id_offre,
                                            'class'      => 'delete',
                                            'icon'       => '🗑',
                                            'title'      => 'Supprimer',
                                            'attributes' => [
                                                'onclick' => "return confirm('Confirmer la suppression ?');"
                                            ]
                                        ], false),                 // retourne string
            ];

        }
        unset($post);


        $roleModel = new Role();
        $roles = $roleModel->findBy([['id_role', Auth::roleId(), '>=' ]], '', []);
        // Vérification existence
        if (!$old_ident) {
            if (defined('PHPUNIT_RUNNING')) {
                throw new \Exception("Identifiant introuvable");
            }
            http_response_code(404);
            die("Identifiant introuvable");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            $students = [];
            switch ($_POST['form_type'] ?? '') {

                case 'update_profile':
                    // traitement infos
                    // Données modifiées
                    $ident = [
                        'nom'       => $_POST['nom']        ?? null,
                        'prenom'    => $_POST['prenom']     ?? null,
                        'email'     => $_POST['email']      ?? null,
                        'id_role'   => $_POST['id_role']    ?? null,
                        'valide'    => (bool) $_POST['valide'],
                    ];

                    // Validation
                    $validator = new Validator();
                    $valid = $validator->validate($_POST, [
                        'nom'       => ['required', 'alpha'],
                        'prenom'    => ['required', 'alpha'],
                        'id_role'   => ['required', 'integerpositif'],
                        'email'     => ['required', 'email'],
                    ]);

                    // Retour avec erreurs
                    if (!$valid) {
                        return $this->render('ident/modify', [
                            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                            'errors'        => $validator->errors(),
                            'ident'         => $ident,
                            'roles'         => $roles,
                            'wishlist'      => $wishlist,
                            'postule'       => $postule,
                            'etudiants'     => $etudiants,
                            'etu_selected'  => $etudiantsSelectionnes,
                        ]);
                    }

                    // Verification s'il y a eu une modif ou pas
                    $schema = [
                        'nom'           => 'string',
                        'prenom'        => 'string',
                        'email'         => 'string',
                        'id_role'       => 'int',
                        'valide'        => 'bool',
                    ];
                    $cleanPost  = Datanormalizer::normalizeWithSchema($ident, $schema);
                    $cleanDb    = Datanormalizer::normalizeWithSchema($old_ident, $schema);
                    if ($cleanPost !== $cleanDb) {
                        // il y a une différence entre la data en sgbd et la data du formulaire
                        $ident['valide_id_ident'] = $_SESSION['user']['id'];
                        $ident['valide_lastupdate'] = (new DateTime())->format('Y-m-d H:i:s');
                    }

                    // Nettoyage des données avec update
                    foreach ($ident as $key => $value) {
                        if ($value === '') {
                            $ident[$key] = null;
                        }
                    }

                    if (in_array((int)($ident['id_role']), PILOTE)) {
                        // Je suis un pilote -> gestion des étudiants à gérer... (cf tableau $_POST['etudiants'])
                        if (! $validator->containsIntGreaterThan0($_POST['etudiants'])) {
                            return $this->render('ident/modify', [
                                'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                                'errors'        => [ ['id_etudiant invalide' ] ],
                                'ident'         => $ident,
                                'roles'         => $roles,
                                'wishlist'      => $wishlist,
                                'postule'       => $postule,
                                'etudiants'     => $etudiants,
                                'etu_selected'  => $etudiantsSelectionnes,
                            ]);
                        }
                        foreach ($_POST['etudiants'] as $etu) {
                            $students[] = [$id, (int)($etu)];
                        }
                    }
                    break;

                case 'update_password':
                    // traitement password
                    // Données modifiées
                    $ident = [
                        'passwd'       => $_POST['passwd'] ?? null,
                    ];

                    // Validation
                    $validator = new Validator();
                    $valid = $validator->validate($_POST, [
                        'passwd'       => ['required', 'alpha'],
                    ]);

                    // Retour avec erreurs
                    if (!$valid) {
                        return $this->render('ident/modify', [
                            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                            'errors'        => $validator->errors(),
                            'ident'         => $ident,
                            'roles'         => $roles,
                            'wishlist'      => $wishlist,
                            'postule'       => $postule,
                            'etudiants'     => $etudiants,
                            'etu_selected'  => $etudiantsSelectionnes,
                        ]);
                    }
                    $ident['passwd'] = password_hash($_POST['passwd'], PASSWORD_DEFAULT);
                    break;

                default:
                    http_response_code(400);
                    die('Formulaire inconnu');
            }



            // Mise à jour
            $identModel->update($id, $ident);


            // Ajout des étudiants en gestion
            if ($students) {
                $etudiantModel->deleteWithCriteria([
                    [ 'id_ident', $id, '=' ],
                ]);                                                                     // suppression des anciennes associations
                $etudiantModel->insert(['id_ident', 'id_ident_etudiant'], $students);   // positionnement des nouvelles
            }

            // Redirection après succès
            $this->redirect('/ident/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('ident/modify', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'ident'         => $old_ident,
            'errors'        => [],
            'roles'         => $roles,
            'wishlist'      => $wishlist,
            'postule'       => $postule,
            'etudiants'     => $etudiants,
            'etu_selected'  => $etudiantsSelectionnes,
        ]);
    }

    /**
     * Suppression d'un identifiant.
     *
     * @param int $id Identifiant
     *
     * Supprime l'entité puis redirige vers la liste.
     *
     * @return void
     */
    public function delete($id)
    {
        $identModel = $this->getIdentModel();

        $ident = [
            'valide_id_ident'   => $_SESSION['user']['id'],
            'valide'            => false,
            'valide_lastupdate' => (new DateTime())->format('Y-m-d H:i:s'),
        ];
        // Mise à jour
        $identModel->update($id, $ident);


        $this->redirect('/ident/recherche');
    }


    /**
     * Fournit une instance du modèle Entreprise.
     *
     * Méthode isolée pour faciliter le mock en test.
     *
     * @return Ident
     */
    protected function getIdentModel()
    {
        return new Ident();
    }
}
