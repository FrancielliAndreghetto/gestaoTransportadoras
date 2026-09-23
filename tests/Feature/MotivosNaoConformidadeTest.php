<?php

namespace Tests\Feature;

class MotivosNaoConformidadeTest extends FeatureTestCase
{
    public function testListaApenasMotivosAtivos(): void
    {
        $this->db->prepare('
            INSERT INTO motivos_nao_conformidade (codigo, descricao, ativo)
            VALUES (?, ?, 0)
        ')->execute(['TESTE_INATIVO', 'Motivo inativo de teste']);

        $response = $this->request('GET', '/motivos-nao-conformidade');

        $this->assertSame(200, $response['status']);
        $this->assertNotEmpty($response['body']);

        foreach ($response['body'] as $motivo) {
            $this->assertTrue($motivo['ativo']);
            $this->assertNotSame('TESTE_INATIVO', $motivo['codigo']);
        }
    }
}
