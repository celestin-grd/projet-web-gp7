<?php

/**
 * Modèle pour la table "etudiant".
 *
 * Fournit les opérations CRUD via Model et une méthode de recherche paginée.
 */
class Etudiant extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'etudiant';

    /** @var string Clé primaire */
    protected string $primaryKey = 'id_ident';

    public function getEtudiantsSelectionnes(): array
    {
        $db = Database::getInstance();

        // Requête pour récupérer la liste des étudiants
        $sql = "
            SELECT 
                i.id_ident,
                i.nom,
                i.prenom
            FROM etudiant       e
            INNER JOIN ident    i ON (e.id_ident_etudiant = i.id_ident)
            WHERE i.valide = true
            and e.id_ident = :id_ident
            ORDER BY nom, prenom
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id_ident' => Auth::user()['id']]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
