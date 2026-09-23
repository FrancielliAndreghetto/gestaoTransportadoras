<?php

namespace Tests\Feature;

class EntregaTransportadoraInativaTest extends FeatureTestCase
{
    public function testNaoPermiteCriarEntregaComTransportadoraInativa(): void
    {
        $transportadora = $this->db
            ->query('SELECT id FROM transportadoras WHERE deleted_at IS NOT NULL LIMIT 1')
            ->fetch();

        if (!$transportadora) {
            $this->markTestSkipped('Nenhuma transportadora inativa no banco.');
        }

        $remetente = (int) $this->db->query('SELECT id FROM remetentes LIMIT 1')->fetchColumn();
        $destinatario = (int) $this->db->query('SELECT id FROM destinatarios LIMIT 1')->fetchColumn();

        $response = $this->request('POST', '/entregas', [
            'id_transportadora' => (int) $transportadora['id'],
            'id_remetente'      => $remetente,
            'id_destinatario'   => $destinatario,
            'data_prazo'        => '2026-12-31',
            'peso_kg'           => 1.5,
            'volumes'           => 1,
        ]);

        $this->assertSame(422, $response['status']);
        $this->assertSame('Transportadora está inativa', $response['body']['erro']);
    }
}
