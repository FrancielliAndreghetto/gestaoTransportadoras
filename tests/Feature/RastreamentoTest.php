<?php

namespace Tests\Feature;

class RastreamentoTest extends FeatureTestCase
{
    public function testRetornaHistoricoPeloCodigo(): void
    {
        $row = $this->db->query('SELECT codigo FROM entregas ORDER BY id ASC LIMIT 1')->fetch();
        if (!$row) {
            $this->markTestSkipped('Nenhuma entrega no banco. Rode as seeds.');
        }

        $response = $this->request('GET', '/rastreamento/' . $row['codigo']);

        $this->assertSame(200, $response['status']);
        $this->assertSame($row['codigo'], $response['body']['codigo']);
        $this->assertArrayHasKey('status', $response['body']);
        $this->assertArrayHasKey('rastreamento', $response['body']);
        $this->assertNotEmpty($response['body']['rastreamento']);
    }

    public function testCodigoInexistente(): void
    {
        $response = $this->request('GET', '/rastreamento/BRD-1999-99999');

        $this->assertSame(404, $response['status']);
        $this->assertSame('Entrega não encontrada', $response['body']['erro']);
    }
}
