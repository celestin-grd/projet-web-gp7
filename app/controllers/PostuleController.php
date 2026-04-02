<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Datanormalizer.php';


/**
 * Contrôleur de gestion des candidatures.
 *
 * Gère les opérations CRUD ainsi que la recherche avec pagination :
 * - Création
 * - Recherche (avec filtres et pagination)
 * - Modification
 * - Suppression
 */
class PostuleController extends Controller
{
    /**
     * Création d'une candidature.
     *
     * - En GET : affiche le formulaire de création.
     * - En POST : valide les données puis crée une candidature.
     *   - En cas d'erreur : réaffiche le formulaire avec les erreurs.
     *   - En cas de succès : redirige vers la page de recherche.
     *
     * @return void
     */
    public function create(int $id)
    {
        $offreModel = new Offre();
        $offre = $offreModel->getOffres([
            'attributes'    => [
                                'o.titre'             ,
                                'o.description'       ,
                                'o.base_remuneration' ,
                                'o.date_offre'        ,
                                'e.nom'
                            ],
            'criteria'      => [
                                ['o.id_offre', $id   , '='],
                            ],
            'order'         => '',
            'limit_offset'  => [],
        ]);
        $filters = [
            'id_ident'              => $_SESSION['user']['id'],
            'id_offre'              => $id,
            'cv'                    => null,
            'lettre_motivation'     => null,
            'date_postule'          => (new DateTime())->format('Y-m-d  H:i:s'),
            'valide'                => true,
            'valide_id_ident'       => $_SESSION['user']['id'],
            'valide_lastupdate'     => (new DateTime())->format('Y-m-d H:i:s'),
            'file_lm'               => bin2hex(random_bytes(16)) . '.pdf',
            'file_cv'               => bin2hex(random_bytes(16)) . '.pdf',
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }

            // Données filtrées pour pré-remplissage en cas d'erreur
            # id_ident|id_offre|cv|lettre_motivation|date_postule|file_lm|file_cv|valide|valide_id_ident|valide_lastupdate
            $filters = [
                'id_ident'              => $_SESSION['user']['id'],
                'id_offre'              => $id,
                'cv'                    => $_FILES['file_cv']['name'] ?? null,
                'lettre_motivation'     => $_FILES['file_lm']['name'] ?? null,
                'date_postule'          => (new DateTime())->format('Y-m-d  H:i:s'),
                'valide'                => true,
                'valide_id_ident'       => $_SESSION['user']['id'],
                'valide_lastupdate'     => (new DateTime())->format('Y-m-d H:i:s'),
                'file_lm'               => bin2hex(random_bytes(16)) . '.pdf',
                'file_cv'               => bin2hex(random_bytes(16)) . '.pdf',
            ];


            $userDir = UPLOAD_DIR . '/' . $_SESSION['user']['id'] . '/';

            // 🔧 créer le dossier si inexistant
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            foreach ($_FILES as $inputName => $fileObj) {

                if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur upload fichier : $inputName");
                }

                $tmpPath = $_FILES[$inputName]['tmp_name'];
                // 🔒 Vérification MIME (IMPORTANT)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmpPath);

                if ($mime !== 'application/pdf') {
                    throw new Exception("Le fichier $inputName doit être un PDF");
                }

                // 📄 chemin final
                $destination = $userDir . $filters[$inputName];

                if (!move_uploaded_file($tmpPath, $destination)) {
                    throw new Exception("Impossible de déplacer le fichier $inputName vers $destination");
                }

                // 💾 chemin relatif pour BDD
                $filters[$inputName] = "/uploads/" . $_SESSION['user']['id'] . "/" . $filters[$inputName];
            }

            // Création en base
            $postuleModel = new PostuleModel();

            // Possibilité que le candidat ait déjà postulé par le passé -> on l'update
            if ($postuleModel->existsBy([
                                            ['id_offre', $id, '=' ],
                                            ['id_ident', $_SESSION['user']['id'], '='],
                                        ])) {
                $postuleModel->updateWithCriteria(
                    $filters,
                    [
                                                        ['id_offre', $id, '=' ],
                                                        ['id_ident', $_SESSION['user']['id'], '='],
                                                    ]
                );
            } else {
                // Aucun enregistrement existe -> creation
                $k = array_keys($filters);
                $v = [];
                $v[] = array_values($filters);
                $postuleModel->insert($k, $v);
            }

            // Suppression de la wishlist
            $wishlistModel = new Wishlist();
            $wishlistModel->deleteWithCriteria([
                ['id_offre' , $id                       , "=" ],
                ['id_ident' , $_SESSION['user']['id']   , '=' ],
            ]);

            // Redirection après succès
            $this->redirect('/ident/modify/'.$_SESSION['user']['id']);
        }

        // Affichage formulaire (GET)
        $this->render('postule/create', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'offre'         => $offre[0],
            'filters'       => $filters
        ]);
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
            'date_offre'        => (new DateTime())->format('Y-m-d'),
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
                'date_offre'        => $_POST['date_offre']         ?? (new DateTime())->format('Y-m-d'),
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
                    'description'       => ['required', 'txt'],
                    'base_remuneration' => ['required', 'integer'],
                    'date_offre'        => ['required', 'date'],
                    'id_entreprise'     => ['required', 'integer'],
                ]);
            }

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('offre/recherche', [
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


            // Rendu des résultats
            return $this->render('offre/recherche', [
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
        $offreModel = new Offre();
        $offre = $offreModel->getOffres([
            'attributes'    => [
                                'o.titre'             ,
                                'o.description'       ,
                                'o.base_remuneration' ,
                                'o.date_offre'        ,
                                'e.nom'
                            ],
            'criteria'      => [
                                ['o.id_offre', $id   , '='],
                            ],
            'order'         => '',
            'limit_offset'  => [],
        ]);
        $postuleModel = new PostuleModel();
        $filters = $postuleModel->getCandidatures([
            'attributes'    => [
                                'p.*'                 ,
                                'o.titre'             ,
                                'o.description'       ,
                                'o.base_remuneration' ,
                                'o.date_offre'        ,
                                'e.nom'
                            ],
            'criteria'      => [
                                ['o.id_offre', $id                  , '='],
                                ['p.id_ident', Auth::user()['id']   , '='],
                            ],
            'order'         => 'p.date_postule ASC',
            'limit_offset'  => [],
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            View::Dumper($_POST['csrf_token']);
            View::Dumper($_SESSION['csrf_token']);
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token invalide");
            }

            // Données filtrées pour pré-remplissage en cas d'erreur
            # id_ident|id_offre|cv|lettre_motivation|date_postule|file_lm|file_cv|valide|valide_id_ident|valide_lastupdate
            $filters = [
                'cv'                    => $_FILES['file_cv']['name'] ?? null,
                'lettre_motivation'     => $_FILES['file_lm']['name'] ?? null,
                'valide'                => true,
                'valide_id_ident'       => $_SESSION['user']['id'],
                'valide_lastupdate'     => (new DateTime())->format('Y-m-d H:i:s'),
                'file_lm'               => bin2hex(random_bytes(16)) . '.pdf',
                'file_cv'               => bin2hex(random_bytes(16)) . '.pdf',
            ];

            $userDir = UPLOAD_DIR . '/' . $_SESSION['user']['id'] . '/';

            // 🔧 créer le dossier si inexistant
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            foreach ($_FILES as $inputName => $fileObj) {

                if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur upload fichier : $inputName");
                }

                $tmpPath = $_FILES[$inputName]['tmp_name'];
                // 🔒 Vérification MIME (IMPORTANT)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmpPath);

                if ($mime !== 'application/pdf') {
                    throw new Exception("Le fichier $inputName doit être un PDF");
                }

                // 📄 chemin final
                $destination = $userDir . $filters[$inputName];

                if (!move_uploaded_file($tmpPath, $destination)) {
                    throw new Exception("Impossible de déplacer le fichier $inputName vers $destination");
                }

                // 💾 chemin relatif pour BDD
                $filters[$inputName] = "/uploads/" . $_SESSION['user']['id'] . "/" . $filters[$inputName];
            }

            // Mise à jour en base
            $postule = new PostuleModel();
            $postule->updateWithCriteria($filters, [ ['id_offre', $id, '='], ['id_ident', $_SESSION['user']['id'], '='] ]);

            // Redirection après succès
            $this->redirect('/ident/modify/'.$_SESSION['user']['id']);
        }

        $filters[0]['file_cv'] = basename($filters[0]['file_cv']);
        $filters[0]['file_lm'] = basename($filters[0]['file_lm']);
        // Affichage formulaire (GET)
        $this->render('postule/modify', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'errors'        => [],
            'offre'         => $offre[0],
            'filters'       => $filters[0]
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
        $datas = [
            'valide_id_ident'   => $_SESSION['user']['id'],
            'valide'            => false,
            'valide_lastupdate' => (new DateTime())->format('Y-m-d H:i:s'),
        ];
        // Mise à jour
        $postule = new PostuleModel();
        $postule->updateWithCriteria($datas, [ ['id_offre', $id, '='], ['id_ident', $_SESSION['user']['id'], '='] ]);

        $this->redirect('/ident/modify/'.$_SESSION['user']['id']);
    }

    public function download($filename)
    {
        // 🔒 vérification utilisateur connecté
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            exit;
        }

        $filePath = UPLOAD_DIR."/".Auth::user()['id']."/$filename";

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit("Fichier introuvable");
        }

        // 📄 headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    /**
     * Affiche les candidatures d'une entreprise existante.
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
        $postuleModel = new PostuleModel();
        $candidatures = $postuleModel->getCandidatures([
            'attributes'    => [
                                'i.id_ident'
                            ],
            'criteria'      => [
                                ['o.id_offre', $id   , '='],
                            ],
            'order'         => '',
            'limit_offset'  => [],
        ]);
        $total = count($candidatures);

        $results = $postuleModel->getCandidatures([
            'attributes'    => [
                                'o.id_offre'                                        ,
                                'i.id_ident'                                        ,
                                'i.id_role'                                         ,
                                'e.id_entreprise'                                   ,
                                'o.titre'                                           ,
                                'o.description'                                     ,
                                'o.date_offre'                                      ,
                                'o.base_remuneration'                               ,
                                'e.nom                  AS entreprise_nom'          ,
                                'e.description          AS entreprise_description'  ,
                                'i.nom                  AS candidat_nom'            ,
                                'i.prenom               AS candidat_prenom'         ,
                                'i.email                AS candidat_email'          ,
                                'p.date_postule'
                            ],
            'criteria'      => [
                                ['o.id_offre', $id   , '='],
                            ],
            'order'         => 'p.date_postule ASC',
            'limit_offset'  => $limit_offset,
        ]);
        foreach ($results as &$item) {
            $go = true;
            if ($item['id_role'] > Auth::roleId()) {
                // Possibilité de modifoer des fiche dont le role est > à moi (subalternes)
            } elseif ($item['id_role'] == Auth::roleId()) {
                if ((int)($item['id_ident']) !== (int)($_SESSION['user']['id'])) {
                    // Interdiction de modifier une fiche possédant le même role que moi
                    $go = false;
                }
            }
            if ($go) {
                $item['buttons'] = [
                    'edit'     => View::button([
                        'permission' => 'ident_modify',
                        'url'        => '/ident/modify/'.$item['id_ident'],
                        'class'      => 'edit',
                        'icon'       => '✏',
                        'title'      => 'Modifier',
                    ], false),                  // retourne string
                    'postule'     => View::button([
                        'permission' => 'postule_modify',
                        'url'        => '/postule/modify/'.$item['id_offre'],
                        'class'      => 'edit',
                        'icon'       => '✉',
                        'title'      => 'Modifier',
                    ], false),                  // retourne string
                ];
            } else {
                $item['buttons'] = [ 'edit' => '' ];
            }
        }
        unset($item);
        // Calcul du nombre total de pages
        $totalPages = ceil($total / $perPage);

        // Affichage formulaire (GET)
        return $this->render('postule/show', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'results'       => $results,
            'meta'          => [
                'id_offre'          => $id,
                'entreprise'        => $results[0]['entreprise_nom'],
                'titre'             => $results[0]['titre'],
                'description'       => $results[0]['description'],
                'date_offre'        => $results[0]['date_offre'],
                'base_remuneration' => $results[0]['base_remuneration'],
            ],
            'page'          => $page,
            'totalPages'    => $totalPages,
            'pagination'    => View::buildPagination($page, $totalPages),
        ]);
    }


}
