<?php

namespace Tests\Feature;

class NaoConformidadeTest extends FeatureTestCase
{
    public function testIdMotivoObrigatorio(): void
    {
        $entregaId = $this->entregaId();
        $response  = $this->request('POST', "/entregas/{$entregaId}/nao-conformidades", [
            'descricao' => 'Sem motivo',
        ]);

        $this->assertSame(422, $response['status']);
        $this->assertSame('Campo obrigatório: id_motivo', $response['body']['erro']);
    }

    public function testEntregaInexistente(): void
    {
        $response = $this->request('POST', '/entregas/999999/nao-conformidades', [
            'id_motivo' => 1,
        ]);

        $this->assertSame(404, $response['status']);
        $this->assertSame('Entrega não encontrada', $response['body']['erro']);
    }

    public function testMotivoInexistente(): void
    {
        $entregaId = $this->entregaId();
        $response  = $this->request('POST', "/entregas/{$entregaId}/nao-conformidades", [
            'id_motivo' => 999999,
        ]);

        $this->assertSame(404, $response['status']);
        $this->assertSame('Motivo não encontrado', $response['body']['erro']);
    }

    public function testRegistraEListaNaoConformidade(): void
    {
        $entregaId = $this->entregaId();
        $motivoId  = $this->motivoId();

        $created = $this->request('POST', "/entregas/{$entregaId}/nao-conformidades", [
            'id_motivo' => $motivoId,
            'descricao' => 'Caixa amassada no teste',
        ]);

        $this->assertSame(201, $created['status']);
        $this->assertSame($entregaId, $created['body']['id_entrega']);
        $this->assertSame($motivoId, $created['body']['id_motivo']);
        $this->assertSame('Caixa amassada no teste', $created['body']['descricao']);

        $list = $this->request('GET', "/entregas/{$entregaId}/nao-conformidades");

        $this->assertSame(200, $list['status']);
        $this->assertNotEmpty($list['body']);
        $this->assertSame('Caixa amassada no teste', end($list['body'])['descricao']);
    }

    public function testListaEntregaInexistente(): void
    {
        $response = $this->request('GET', '/entregas/999999/nao-conformidades');

        $this->assertSame(404, $response['status']);
        $this->assertSame('Entrega não encontrada', $response['body']['erro']);
    }

    private function entregaId(): int
    {
        $id = $this->db->query('SELECT id FROM entregas ORDER BY id ASC LIMIT 1')->fetchColumn();
        if (!$id) {
            $this->markTestSkipped('Nenhuma entrega no banco. Rode as seeds.');
        }

        return (int) $id;
    }

    private function motivoId(): int
    {
        $id = $this->db->query('SELECT id FROM motivos_nao_conformidade WHERE ativo = 1 LIMIT 1')->fetchColumn();
        if (!$id) {
            $this->markTestSkipped('Nenhum motivo ativo no banco. Rode as seeds.');
        }

        return (int) $id;
    }
}
