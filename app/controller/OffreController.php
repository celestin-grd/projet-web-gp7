<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Datanormalizer.php';

/**
 * Contrôleur de gestion des offres.
 *
 * Gère les opérations CRUD ainsi que la recherche avec pagination :
 * - Création
 * - Recherche (avec filtres et pagination)
 * - Modification
 * - Suppression
 */
class OffreController extends Controller
{
    /**
     * Création d'une offre.
     *
     * - En GET : affiche le formulaire de création.
     * - En POST : valide les données puis crée une offre.
     *   - En cas d'erreur : réaffiche le formulaire avec les erreurs.
     *   - En cas de succès : redirige vers la page de recherche.
     *
     * @return void
     */
    public function create()
    {
        $entrepriseModel = new Entreprise();
        $entreprises = $entrepriseModel->findBy([['valide', true, '=' ]], 'nom ASC', []);
        $competenceModel = new Competence();
        $competences = $competenceModel->findAll();
        $filters = [
            'id_entreprise'     => null,
            'titre'             => null,
            'description'       => null,
            'competences'       => [],
            'base_remuneration' => 0,
            'date_offre'        => (new DateTime())->format('Y-m-d'),
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
            // Données filtrées pour pré-remplissage en cas d'erreur
            $filters = [
                'id_entreprise'         => $_POST['id_entreprise']      ?? null,
                'titre'                 => $_POST['titre']              ?? null,
                'description'           => $_POST['description']        ?? null,
                // 'competences'           => $_POST['competences']        ?? [],
                'base_remuneration'     => $_POST['base_remuneration']  ?? null,
                'date_offre'            => $_POST['date_offre']         ?? (new DateTime())->format('Y-m-d'),
                'valide'                => isset($_POST['valide']) ? true : false,
            ];

            // Validation des données
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'id_entreprise'     => ['required', 'integerpositif'],
                'titre'             => ['required', 'alpha'],
                'description'       => ['required', 'txt'],
                'base_remuneration' => ['required', 'integer'],
                'date_offre'        => ['required', 'date'],
            ]);

            // Retour formulaire avec erreurs
            if (!$valid) {
                return $this->render('offre/create', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'filters'       => $filters,
                    'entreprises'   => $entreprises,
                    'competences'   => $competences,
                ]);
            }

            // Création en base
            $offre = $this->getOffre();
            $id_offre = $offre->create($filters);

            // TODO : vérifier que le tableau $_POST['competences'] contient des integer supérieurs à 0
            if (! $validator->containsIntGreaterThan0($_POST['competences'])) {
                return $this->render('offre/create', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => [ ['id_competence invalide' ] ],
                    'filters'       => $filters,
                    'entreprises'   => $entreprises,
                    'competences'   => $competences,
                ]);
            }
            // Ajout des competences requises pour cette offre
            $offreHasCompetence = new OffreHasCompetence();
            $offreHasCompetence->syncCompetences((int)($id_offre), $_POST['competences']);

            // // Redirection après succès
            $this->redirect('/offre/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('offre/create', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'filters'       => $filters,
            'entreprises'   => $entreprises,
            'competences'   => $competences, 'filters' => $filters ]);
    }

    /**
     * Recherche d'offre avec filtres et pagination.
     *
     * - En GET : affiche la page de recherche vide.
     * - En POST : applique les filtres, valide les entrées et retourne les résultats paginés.
     *
     * @return void
     */
    public function recherche()
    {
        $entrepriseModel = new Entreprise();
        $entreprises = $entrepriseModel->findBy([['valide', true, '=' ]], 'nom ASC', []);

        $competenceModel = new Competence();
        $competences = $competenceModel->findAll();
        $filters = [
            'id_entreprise'     => null,
            'titre'             => null,
            'description'       => null,
            'competences'       => [],
            'base_remuneration' => 0,
            'date_offre'        => null,
            'valide'            => true,
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
                'id_entreprise'     => $_POST['id_entreprise']      ?? null,
                'titre'             => $_POST['titre']              ?? null,
                'description'       => $_POST['description']        ?? null,
                'competences'       => $_POST['competences']        ?? [],
                'base_remuneration' => $_POST['base_remuneration']  ?? null,
                'date_offre'        => $_POST['date_offre']         ?? null,
                'valide'            => isset($_POST['valide']) ? true : false,
            ];
            $validator = new Validator();
            $valid = true;

            // Validation conditionnelle des champs
            if (!empty($_POST['titre'])) {
                $valid = $validator->validate($_POST, ['titre' => ['required', 'alpha']]);
            }

            if ($valid && !empty($_POST['description'])) {
                $valid = $validator->validate($_POST, [
                    'description'       => [ 'txt'],
                    'base_remuneration' => [ 'integer'],
                    'date_offre'        => [ 'date'],
                    'id_entreprise'     => [ 'integerpositif'],
                ]);
            }

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('offre/recherche', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'filters'       => $filters,
                    'entreprises'   => $entreprises,
                    'competences'   => $competences,
                    'results'       => [],
                    'page'          => 1,
                    'totalPages'    => 0,
                    'pagination'    => [],
                ]);
            }

            // Exécution de la recherche
            $offre = $this->getOffre();
            $data = $offre->search($filters, $page, $perPage);

            $results = $data['results'];
            $total = $data['total'];

            // Calcul du nombre total de pages
            $totalPages = ceil($total / $perPage);


            // On regarde si l'offres qui vont s'afficher sont présentes dans la wishlist du user courant
            $wishlistModel = new Wishlist();
            foreach ($results as &$item) {
                $exists = $wishlistModel->existsBy([['id_offre', $item['id_offre'], '=' ], ['id_ident', $_SESSION['user']['id'], '=']]);
                $item['in_wishlist'] = (int)($exists) ?? 0;
            }
            unset($item); // important


            // On regarde si l'offres qui vont s'afficher sont présentes dans les candidatures du user courant
            $postuleModel = new PostuleModel();
            foreach ($results as &$item) {
                $exists = $postuleModel->existsBy([
                    ['id_offre', $item['id_offre'], '=' ],
                    ['id_ident', $_SESSION['user']['id'], '='],
                    ['valide', true, '=' ],
                ]);
                $item['in_postule'] = (int)($exists) ?? 0;

                $nb_candidatures = $postuleModel->count([
                    ['id_offre', $item['id_offre'], '=' ],
                    ['valide', true, '=' ],
                ]);
                $item['nb_candidatures'] = (int)($nb_candidatures) ?? 0;

                $item['buttons'] = [
                    'postule'   => View::button([
                                                'permission' => 'postule_create',
                                                'url'        => '#',
                                                'class'      => 'apply',
                                                'icon'       => '✉',
                                                'title'      => 'Postuler',
                                                'attributes' => [
                                                    'data-id' => $item['id_offre'],
                                                ]
                                            ], false),
                    'wishlist'  => View::button([
                                                'permission' => 'wishlist_create',
                                                'url'        => '#',
                                                'class'      => 'wishlist',
                                                'icon'       => $item['in_wishlist'] ? '★' : '☆',
                                                'title'      => $item['in_wishlist']
                                                    ? 'Retirer de la wishlist'
                                                    : 'Ajouter à la wishlist',
                                                'attributes' => [
                                                    'data-id' => $item['id_offre'],
                                                    'data-inwishlist' => $item['in_wishlist']
                                                ]
                                            ], false),
                    'edit'      => View::button([
                                                'permission' => 'offre_modify',
                                                'url'        => '/offre/modify/'.$item['id_offre'],
                                                'class'      => 'edit',
                                                'icon'       => '✏',
                                                'title'      => 'Modifier',
                                            ], false),                  // retourne string
                    'delete'    => View::button([
                                                'permission' => 'offre_delete',
                                                'url'        => '/offre/delete/'.$item['id_offre'],
                                                'class'      => 'delete',
                                                'icon'       => '🗑',
                                                'title'      => 'Supprimer',
                                                'attributes' => [
                                                    'onclick' => "return confirm('Confirmer la suppression ?');"
                                                ]
                                            ], false),                 // retourne string

                ];

            }
            unset($item); // important

            // Rendu des résultats
            return $this->render('offre/recherche', [
                'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                'errors'        => [],
                'filters'       => $filters,
                'entreprises'   => $entreprises,
                'competences'   => $competences,
                'results'       => $results,
                'page'          => $page,
                'totalPages'    => $totalPages,
                'pagination'    => View::buildPagination($page, $totalPages),
            ]);
        }

        // Affichage initial (GET)
        $this->render('offre/recherche', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'filters'       => $filters,
            'entreprises'   => $entreprises,
            'competences'   => $competences,
            'results'       => [],
            'page'          => 1,
            'totalPages'    => 0,
            'pagination'    => [],
        ]);
    }

    /**
     * Modification d'une offre existant.
     *
     * @param int $id ID de l'Offre
     *
     * - Vérifie l'existence de l'offre
     * - En GET : affiche le formulaire pré-rempli
     * - En POST : valide puis met à jour les données
     *
     * @return void
     * @throws \Exception Si l'entreprise est introuvable (en environnement de test)
     */
    public function modify($id)
    {
        $offreModel = $this->getOffre();
        $old_offre = $offreModel->findById($id);

        $offreHasCompetence = new OffreHasCompetence();
        $selectedCompetences = $offreHasCompetence->getCompetences($id);

        $entrepriseModel = new Entreprise();
        $entreprises = $entrepriseModel->findBy([['valide', true, '=' ]], 'nom ASC', []);
        $competenceModel = new Competence();
        $competences = $competenceModel->findAll();

        // Vérification existence
        if (!$old_offre) {
            if (defined('PHPUNIT_RUNNING')) {
                throw new \Exception("Offre introuvable");
            }
            http_response_code(404);
            die("Offre introuvable");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }


            // traitement infos
            // Données modifiées
            // Données filtrées pour pré-remplissage en cas d'erreur
            $offre = [
                'id_entreprise'     => $_POST['id_entreprise']      ?? null,
                'titre'             => $_POST['titre']              ?? null,
                'description'       => $_POST['description']        ?? null,
                // 'competences'       => $_POST['competences']        ?? [],
                'base_remuneration' => $_POST['base_remuneration']  ?? null,
                'date_offre'        => $_POST['date_offre']         ?? (new DateTime())->format('Y-m-d'),
                'valide'            => (bool) $_POST['valide'],
            ];

            // Validation des données
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'id_entreprise'     => ['required', 'integer'],
                'titre'             => ['required', 'alpha'],
                'description'       => ['required', 'txt'],
                'base_remuneration' => ['required', 'integer'],
                'date_offre'        => ['required', 'date'],
            ]);

            // Retour formulaire avec erreurs
            if (!$valid) {
                return $this->render('offre/modify', [
                    'csrf_token'            => $_SESSION['csrf_token'] ?? '',
                    'errors'                => $validator->errors(),
                    'offre'                 => $offre,
                    'entreprises'           => $entreprises,
                    'competences'           => $competences,
                    'selectedCompetences'   => $selectedCompetences,
                ]);
            }

            // Verification s'il y a eu une modif ou pas
            $schema = [
                'titre'             => 'string',
                'description'       => 'string',
                'base_remuneration' => 'int',
                'date_offre'        => 'int',
                'valide'            => 'bool',
            ];
            $cleanPost  = Datanormalizer::normalizeWithSchema($offre, $schema);
            $cleanDb    = Datanormalizer::normalizeWithSchema($old_offre, $schema);
            if ($cleanPost !== $cleanDb) {
                // il y a une différence entre la data en sgbd et la data du formulaire
                $offre['valide_id_ident'] = $_SESSION['user']['id'];
                $offre['valide_lastupdate'] = (new DateTime())->format('Y-m-d H:i:s');
            }

            // Nettoyage des données avec update
            foreach ($offre as $key => $value) {
                if ($value === '') {
                    $offre[$key] = null;
                }
            }

            // Mise à jour
            $offreModel->update($id, $offre);

            // TODO : vérifier que le tableau $_POST['competences'] contient des integer supérieurs à 0
            if (! $validator->containsIntGreaterThan0($_POST['competences'])) {
                return $this->render('offre/modify', [
                    'csrf_token'            => $_SESSION['csrf_token'] ?? '',
                    'errors'                => [ [ 'id_competence invalide' ] ],
                    'offre'                 => $offre,
                    'entreprises'           => $entreprises,
                    'competences'           => $competences,
                    'selectedCompetences'   => $selectedCompetences,
                ]);
            }

            $offreHasCompetence->syncCompetences((int)($id), $_POST['competences']);

            // Redirection après succès
            $this->redirect('/offre/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('offre/modify', [
            'csrf_token'            => $_SESSION['csrf_token'] ?? '',
            'offre'                 => $old_offre,
            'errors'                => [],
            'entreprises'           => $entreprises,
            'competences'           => $competences,
            'selectedCompetences'   => $selectedCompetences,
        ]);
    }

    /**
     * Suppression d'une offre.
     *
     * @param int $id Offre
     *
     * Supprime l'entité puis redirige vers la liste.
     *
     * @return void
     */
    public function delete($id)
    {
        $offreModel = $this->getOffre();

        $offre = [
            'valide_id_ident'   => $_SESSION['user']['id'],
            'valide'            => false,
            'valide_lastupdate' => (new DateTime())->format('Y-m-d H:i:s'),
        ];
        // Mise à jour
        $offreModel->update($id, $offre);


        $this->redirect('/offre/recherche');
    }

    /**
     * Affiche les offres d'une entreprise existante.
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
    public function show($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }
        }
        // Gestion de la pagination
        $page = $_POST['page'] ?? 1;
        $page = max(1, (int)$page);
        $perPage = ITEM_PER_PAGES;
        $offset = ($page - 1) * $perPage;
        $limit_offset['limit']    = (int)($perPage);
        $limit_offset['offset']   = (int)($offset);
        // Exécution de la recherche
        $offreModel = new Offre();
        $offres = $offreModel->getOffres([
            'attributes'    => [
                                'o.id_offre'
                            ],
            'criteria'      => [
                                ['e.id_entreprise', $id   , '='],
                            ],
            'order'         => '',
            'limit_offset'  => [],
        ]);
        $total = count($offres);

        $results = $offreModel->getOffres([
            'attributes'    => [
                                'o.id_offre'            ,
                                'o.titre'               ,
                                'o.description'         ,
                                'o.date_offre'          ,
                                'o.base_remuneration'   ,
                                'e.nom'
                            ],
            'criteria'      => [
                                ['e.id_entreprise', $id   , '='],
                            ],
            'order'         => 'date_offre ASC',
            'limit_offset'  => $limit_offset,
        ]);
        $postuleModel = new PostuleModel();
        foreach ($results as &$item) {
            $candidatures = $postuleModel->getCandidatures([
                'attributes'    => [
                                    'i.id_ident'
                                ],
                'criteria'      => [
                                    ['o.id_offre', $item['id_offre']   , '='],
                                ],
                'order'         => '',
                'limit_offset'  => [],
            ]);
            $item['nb_candidatures'] = count($candidatures);
        }
        unset($item);

        // Calcul du nombre total de pages
        $totalPages = ceil($total / $perPage);

        // Affichage formulaire (GET)
        return $this->render('offre/show', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'results'       => $results,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'pagination'    => View::buildPagination($page, $totalPages),
        ]);
    }





    /**
     * Fournit une instance du modèle Offre.
     *
     * Méthode isolée pour faciliter le mock en test.
     *
     * @return Offre
     */
    protected function getOffre()
    {
        return new Offre();
    }
}
