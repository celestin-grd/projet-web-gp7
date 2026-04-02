<?php

/**
 * Classe de validation des données.
 *
 * Permet de valider les entrées utilisateur selon des règles simples.
 * Exemple d'utilisation :
 *
 * $validator = new Validator();
 * $valid = $validator->validate($_POST, [
 *     'nom' => ['required', 'alpha'],
 *     'email' => ['required', 'email']
 * ]);
 * if (!$valid) {
 *     $errors = $validator->errors();
 * }
 */
class Validator
{
    /** @var array Liste des erreurs par champ */
    private array $errors = [];

    /**
     * Valide un tableau de données selon les règles fournies.
     *
     * @param array $data   Données à valider (ex: $_POST)
     * @param array $rules  Tableau associatif de règles par champ
     *                      Exemple : ['nom' => ['required', 'alpha']]
     * @return bool True si toutes les règles sont respectées, false sinon
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = []; // Réinitialisation à chaque validation

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';

            foreach ($fieldRules as $rule) {
                switch ($rule) {
                    case 'required':
                        if (trim($value) === '') {
                            $this->errors[$field][] = "($field) Champ requis";
                        }
                        break;

                    case 'email':
                        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field][] = "($field) email: Email invalide";
                        }
                        break;

                    case 'phone':
                        if ($value !== '' && !preg_match('/^\+?[0-9\s\-]{10,20}$/u', $value)) {
                            $this->errors[$field][] = "($field) phone: Caractères invalides";
                        }
                        break;

                    case 'alpha':
                        if ($value !== '' && !preg_match('/^[\p{L}0-9\s\-\.\'&]{2,100}$/u', $value)) {
                            $this->errors[$field][] = "($field) alpha: Caractères invalides";
                        }
                        break;

                    case 'alpha_prime':
                        if ($value !== '' && !preg_match('/^[\p{L}0-9\s]{2,100}$/u', $value)) {
                            $this->errors[$field][] = "($field) alpha: Caractères invalides";
                        }
                        break;

                    case 'txt':
                        if ($value !== '' && !preg_match('/^[\p{L}0-9\s\p{P}]{2,1000}$/u', $value)) {
                            $this->errors[$field][] = "($field) text: Caractères invalides";
                        }
                        break;

                    case 'integer':
                        if ($value !== '' && !preg_match('/^-?[0-9]+$/u', $value)) {
                            $this->errors[$field][] = "($field) integer: Caractères invalides";
                        }
                        break;

                    case 'integerpositif':
                        if ($value !== '' && !preg_match('/^[0-9]+$/u', $value)) {
                            $this->errors[$field][] = "($field) integer: Caractères invalides";
                        }
                        break;

                    case 'float':
                        if ($value !== '' && !preg_match('/^-?[0-9]+(\.[0-9]+)?$/u', $value)) {
                            $this->errors[$field][] = "($field) float: Caractères invalides";
                        }
                        break;

                    case 'date':
                        if ($value !== '' && !preg_match('/^(((\d\d)(([02468][048])|([13579][26]))-02-29)|(((\d\d)(\d\d)))-((((0\d)|(1[0-2]))-((0\d)|(1\d)|(2[0-8])))|((((0[13578])|(1[02]))-31)|(((0[1,3-9])|(1[0-2]))-(29|30)))))$/u', $value)) {
                            $this->errors[$field][] = "($field) date: Caractères invalides";
                        }
                        break;

                    default:
                        // Possibilité d'ajouter d'autres règles ou lancer une exception
                        throw new InvalidArgumentException("Règle de validation inconnue : $rule");
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Retourne les erreurs de validation.
     *
     * @return array Tableau associatif [champ => [messages d'erreur]]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Vérifie si un champ spécifique est valide.
     *
     * @param string $field Nom du champ
     * @return bool True si aucune erreur sur ce champ
     */
    public function isValidField(string $field): bool
    {
        return empty($this->errors[$field]);
    }

    /**
     * Ajoute une règle de validation personnalisée dynamique.
     *
     * @param string $field Champ à valider
     * @param callable $callback Fonction de validation qui retourne true si valide, false sinon
     * @param string $message Message d'erreur en cas d'échec
     */
    public function addCustomRule(string $field, callable $callback, string $message): void
    {
        $value = $_POST[$field] ?? '';
        if (!$callback($value)) {
            $this->errors[$field][] = $message;
        }
    }

    /**
     * Vérifie si un tableau contient des valeurs integer supérieures à 0
     *
     * @param array $data Nom du tableau
     * @return bool True si vrai, false sinon
     */
    public function containsIntGreaterThan0(array $data): bool
    {
        foreach ($data as $value) {
            if (filter_var($value, FILTER_VALIDATE_INT) === false || $value <= 0) {
                return false;
            }
        }
        return true;
    }
}
