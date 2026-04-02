<?php

/**
 * Modèle pour la table "role".
 *
 * Fournit les opérations CRUD via Model et une méthode de recherche paginée.
 */
class Role extends Model
{
    /** @var string Nom de la table */
    protected string $table = 'role';

    /** @var string Clé primaire */
    protected string $primaryKey = 'id_role';

}
