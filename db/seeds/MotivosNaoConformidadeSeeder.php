<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class MotivosNaoConformidadeSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('motivos__nao_conformidade')->insert([
            ['id' => 1, 'codigo' => 'AVARIA_PRODUTO', 'descricao' => 'Produto com avaria ou dano'],
            ['id' => 2, 'codigo' => 'NAO_ENTREGUE', 'descricao' => 'Destinatário ausente'],
            ['id' => 3, 'codigo' => 'ENDERECO_INCORRETO', 'descricao' => 'Endereço incorreto ou não localizado'],
            ['id' => 4, 'codigo' => 'RECUSADO', 'descricao' => 'Recusado pelo destinatário'],
            ['id' => 5, 'codigo' => 'EXTRAVIO', 'descricao' => 'Produto extraviado'],
            ['id' => 6, 'codigo' => 'OUTROS', 'descricao' => 'Outros motivos'],
           
        ])->saveData();
    }
}
