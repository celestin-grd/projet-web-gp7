<?php

/**
 * Modèle pour la table "role".
 *
 * Fournit les opérations CRUD via Model et une méthode de recherche paginée.
 */
class OffreHasCompetence extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'offre_has_competence';

    /** @var string Clé primaire */
    protected string $primaryKey = 'id_offre';



    public function syncCompetences(int $id_offre, array $ids_competence): bool
    {
        // D'abord on supprime toutes les compétences requises pour cette offre si elles existaient
        $offreHasCompetenceModel = $this->getOffreHasCompetenceModel();
        $offreHasCompetenceModel->delete($id_offre);

        // On prepare les couples id_offre / id_competence
        $rows = [];
        foreach ($ids_competence as $id_competence) {
            $rows[] = [$id_offre, $id_competence];
        }

        // On insère les couples
        return $offreHasCompetenceModel->insert(['id_offre', 'id_competence'], $rows);
    }

    public function getCompetences(int $id): array
    {
        $db = Database::getInstance();

        // Requête pour récupérer toutes les competences d'une offre
        $sql = "
            SELECT 
                ohc.id_offre,
                ohc.id_competence,
                c.competence
            FROM offre_has_competence ohc
            JOIN competence c using (id_competence)
            WHERE ohc.id_offre = :id
            ORDER BY c.competence
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $rows = $stmt->fetchAll();
        $competences = [];

        foreach ($rows as $row) {
            $competences[] = [
                'id_competence' => $row['id_competence'],
                'competence'    => $row['competence']
            ];
        }

        return $competences;

    }


    /**
     * Fournit une instance du modèle OffreHasCompetence.
     *
     * Méthode isolée pour faciliter le mock en test.
     *
     * @return OffreHasCompetence
     */
    protected function getOffreHasCompetenceModel()
    {
        return new OffreHasCompetence();
    }
}
