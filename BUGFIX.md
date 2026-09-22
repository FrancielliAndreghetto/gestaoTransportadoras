# BUGFIX.md

**Data da correção:** 21/09/2026
**Corrigido por:** Desenvolvedor(a)

---

## O que era o bug

No endpoint `POST /entregas` (`App\Controllers\EntregaController::store`, linhas 92-100), o sistema validava a existência da transportadora executando a consulta `SELECT id FROM transportadoras WHERE id = ?`. 

Essa consulta apenas verificava se a transportadora existia na tabela `transportadoras`, mas ignorava o campo `deleted_at`. Como o sistema utiliza soft delete para desativar transportadoras (preenchendo a coluna `deleted_at`), transportadoras inativas passavam na validação e permitiam o cadastro de novas entregas associadas a elas.

---

## Resposta para a Camila (Operações)

Oi Camila, tudo bem?

Analisamos o problema que você reportou sobre a **Logística Norte Ltda** e identificamos o que aconteceu:

Quando uma transportadora é desativada no sistema, o cadastro dela não é apagado (para mantermos o histórico de entregas antigas). O que estava acontecendo é que a tela de cadastro de entregas checava apenas se a transportadora existia no banco de dados, mas não conferia se o contrato dela ainda estava ativo. Por isso, o sistema permitiu cadastrar a entrega normalmente mesmo após a desativação.

**O que fizemos:**
Já corrigimos o sistema! Agora, sempre que alguém tentar cadastrar uma entrega, o sistema verifica se a transportadora selecionada está com o contrato ativo. Se estiver desativada, o cadastro é bloqueado imediatamente e o sistema exibe uma mensagem avisando que a transportadora está inativa.

**Sobre as entregas:**
- Entregas criadas **antes** da desativação da Logística Norte Ltda continuam normais e válidas (foram feitas quando o contrato ainda estava vigente).
- As entregas cadastradas **depois** da data de desativação foram aceitas pelo sistema devido a essa falha, mas pertencem a uma transportadora que não presta mais serviço.

**O que o time deve fazer:**
Por favor, filtrem as entregas vinculadas à Logística Norte Ltda cadastradas após a data de encerramento do contrato. Para essas entregas, será necessário ajustar o vínculo no sistema para a transportadora correta que efetivamente assumiu ou assumirá o transporte da carga.

Se precisar de qualquer ajuda para mapear ou ajustar essas entregas, é só avisar!

---

## Como reproduzir (antes da correção)

1. Obter o ID de uma transportadora desativada no sistema (por exemplo, a Logística Norte Ltda, ID 3, que possui o campo `deleted_at` preenchido).
2. Enviar uma requisição HTTP `POST /entregas` com payload contendo `id_transportadora = 3`:
   ```json
   {
     "id_transportadora": 3,
     "id_remetente": 1,
     "id_destinatario": 1,
     "data_prazo": "2026-03-01",
     "peso_kg": 10.5,
     "volumes": 2
   }
   ```
3. Observar que a API retornava HTTP `201 Created` e cadastravam a entrega normalmente vinculada à transportadora desativada.

---

## Como verificar que está corrigido

1. Tentar cadastrar uma entrega informando o ID de uma transportadora desativada (`POST /entregas` com `id_transportadora = 3`).
   - **Resultado esperado:** A API deve retornar status `422 Unprocessable Entity` com a resposta `{"erro": "Transportadora está inativa"}` e bloquear o cadastro.
2. Cadastrar uma entrega informando o ID de uma transportadora ativa (ex: `POST /entregas` com `id_transportadora = 1`).
   - **Resultado esperado:** A API deve retornar status `201 Created` com os dados da entrega criada com sucesso.
