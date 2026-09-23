<?php

namespace App\Controllers;

use App\Database;

class MotivosController
{
    public static function index(array $params): void
    {
        $db = Database::connection();

        $sql = 'SELECT * FROM motivos_nao_conformidade WHERE ativo = 1 ORDER BY codigo ASC';
        $rows = $db->query($sql)->fetchAll();

        json(array_map([self::class, 'format'], $rows));
    }

    private static function format(array $row): array
    {
        return [
            'id'        => (int) $row['id'],
            'codigo'    => $row['codigo'],
            'descricao' => $row['descricao'],
            'ativo'     => (bool) $row['ativo'],
        ];
    }
}
