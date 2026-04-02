<?php

class Datanormalizer
{
    public static function normalizeWithSchema(array $data, array $schema): array
    {
        $retour = [];
        foreach ($schema as $field => $type) {

            $value = $data[$field] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            switch ($type) {
                case 'int':
                    $value = (int) $value;
                    break;

                case 'bool':
                    $value = !empty($value) ? 1 : 0;
                    break;

                case 'string':
                    $value = $value ?: null;
                    break;
            }

            $retour[$field] = $value;
        }

        return $retour;
    }
}
