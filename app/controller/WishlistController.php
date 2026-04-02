<?php

/**
 * Contrôleur de wishlist
 *
 * Gère les fonctionnalités liées à la gestion de la wishliste
 */
class WishlistController extends Controller
{
    /**
     * Appel Ajax pour ajouter une offre dans la wishliste
     *
     * - En POST :
     *
     * @return void
     */
    public function create()
    {
        // 🔥 TOUJOURS en premier
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        } else {

            // CSRF
            if ((string)($_POST['csrf_token'] ?? '') !== (string)($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['error' => 'CSRF invalide']);
                return;
            }

            $wishlistModel = new Wishlist();
            if ((int)($_POST['value']) === 1) {
                $rows = [
                    [ $_POST['id_offre'], $_SESSION['user']['id']   , (new DateTime())->format('Y-m-d H:i:s')]
                ];
                // On insère les data
                $wishlistModel->insert(['id_offre', 'id_ident', 'date_wishlist'], $rows);
            } else {
                $rows = [
                    [ 'id_offre', $_POST['id_offre']        , '='],
                    [ 'id_ident', $_SESSION['user']['id']   , '='],
                ];
                // On supprime les data
                $wishlistModel->deleteWithCriteria($rows);
            }
            echo json_encode(['status' => 'added']);
        }
    }

}
