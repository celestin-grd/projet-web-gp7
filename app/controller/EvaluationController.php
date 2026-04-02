<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Datanormalizer.php';

/**
 * Contrôleur de gestion des evaluations.
 *
 * Gère les opérations CRUD ainsi que la recherche avec pagination :
 * - Création
 * - Recherche (avec filtres et pagination)
 * - Modification
 * - Suppression
 */
class EvaluationController extends Controller
{
    /**
     * Création d'une evaluation.
     *
     * - En GET : affiche le formulaire de création.
     * - En POST : valide les données puis crée une entreprise.
     *   - En cas d'erreur : réaffiche le formulaire avec les erreurs.
     *   - En cas de succès : redirige vers la page de recherche.
     *
     * @return void
     */



    /**
     * Evaluation d'une entreprise existante.
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
    public function create($id)
    {
        $entrepriseModel = $this->getEntrepriseModel();
        $old_entreprise = $entrepriseModel->findById($id);

        $evaluationModel = $this->getEvaluationModel();
        $evaluation = $evaluationModel->findBy([ ['id_entreprise', $id, '='], ['id_ident', $_SESSION['user']['id'], '='] ], "", [ 'limit' => 1 ]);
        $old_entreprise['evaluation']       = $evaluation[0]['note'] ?? '';
        $old_entreprise['commentaire']      = $evaluation[0]['commentaire'] ?? '';
        $old_entreprise['date_evaluation']  = $evaluation[0]['date_evaluation'] ?? '';


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
            // Validation
            $validator = new Validator();
            $valid = $validator->validate($_POST, [
                'commentaire'   => ['required', 'txt'],
            ]);

            // Retour avec erreurs
            if (!$valid) {
                return $this->render('evaluation/create', [
                    'csrf_token'    => $_SESSION['csrf_token'] ?? '',
                    'errors'        => $validator->errors(),
                    'entreprise'    => $entreprise,
                ]);
            }
            // Mettre a jour la notation
            if (isset($_POST['evaluation'])
                    &&  (int)($_POST['evaluation']) > 0
                    &&  (int)($_POST['evaluation']) <= 5) {

                $evaluationModel->evaluate($id, (int)($_POST['evaluation']), $_POST['commentaire']);
            }

            // Redirection après succès
            $this->doRedirect('/entreprise/recherche');
        }

        // Affichage formulaire (GET)
        $this->render('evaluation/create', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'entreprise'    => $old_entreprise,
            'errors'        => [],
        ]);
    }


    /**
     * Affiche les evaluations d'une entreprise existante.
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

        // Exécution de la recherche
        $evaluationModel = $this->getEvaluationModel();
        $r = $evaluationModel->moyenne($id);
        $note = [];
        $note['eval_moyenne']    = $r['moyenne'];
        $note['eval_nbre']       = $r['nbre'];
        $note['eval_stars']      = floor($r['moyenne']);

        $data = $evaluationModel->search($id, $page, $perPage);

        $results = $data['results'];
        $total = $data['total'];

        // Calcul du nombre total de pages
        $totalPages = ceil($total / $perPage);

        // Affichage formulaire (GET)
        return $this->render('evaluation/show', [
            'csrf_token'    => $_SESSION['csrf_token'] ?? '',
            'results'       => $results,
            'note'          => $note,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'pagination'    => View::buildPagination($page, $totalPages),
        ]);
    }


    protected function getEntrepriseModel()
    {
        return new Entreprise();
    }

    protected function getEvaluationModel()
    {
        return new Evaluation();
    }

    protected function doRedirect(string $url): void
    {
        $this->redirect($url);
    }
}
