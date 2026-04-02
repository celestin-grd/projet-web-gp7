<?php

/**
 * Modèle pour la table "competence".
 *
 * Fournit les opérations CRUD via Model et une méthode de recherche paginée.
 */
class Competence extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'competence';

    /** @var string Clé primaire */
    protected string $primaryKey = 'id_competence';

}
