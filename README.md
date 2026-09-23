# Gestão de Transportadoras

API de um TMS em PHP 8.1. Este repositório parte do starter do teste e entrega o bugfix, o fluxo de não conformidades e os bônus.

**Stack:** PHP 8.1+ · PDO · MySQL 8 · [Phinx](https://phinx.org) · PHPUnit · Docker

---

## O que foi feito

- Correção do `POST /entregas` que aceitava transportadora desativada — detalhes em [BUGFIX.md](./BUGFIX.md)
- Tabelas `motivos_nao_conformidade` e `nao_conformidades` (FKs, unique em `codigo`, `ativo` com default 1)
- Seeder dos 6 motivos
- `GET /motivos-nao-conformidade` e `POST /entregas/{id}/nao-conformidades`
- Bônus: rastreamento público, listagem de NCs, Docker Compose e testes automatizados

---

## Como rodar

### Local

Requisitos: PHP 8.1+, Composer, MySQL 8.

```bash
cp .env.example .env
# edite DB_HOST, DB_NAME, DB_USER e DB_PASS

composer install
vendor/bin/phinx migrate
vendor/bin/phinx seed:run
php -S localhost:8000 public/index.php
```

API em http://localhost:8000

No Windows o Phinx é `vendor\bin\phinx`. Os `curl` abaixo funcionam no PowerShell com `curl.exe`.

### Docker

Não precisa de `.env` local. O Compose define `DB_HOST=db` e sobe MySQL interno.

```bash
docker compose up --build
```

Na primeira subida: migrate + seed. API em http://localhost:8000

```bash
docker compose down          # para os containers (mantém o volume do MySQL)
docker compose down -v       # também apaga o banco
```

### Testes

Banco já migrado e populado; o `.env` precisa apontar para ele.

```bash
composer test
```

Os testes de feature abrem transação e dão rollback — não deixam dado de teste no banco.

---

## Dados de seed

| Recurso | IDs úteis |
|---|---|
| Transportadoras ativas | `1`, `2` |
| Transportadora inativa | `3` (Logística Norte Ltda) |
| Remetentes | `1`, `2` |
| Destinatários | `1`, `2`, `3` |
| Entregas | `1` `BRD-2024-00001` (EM_TRANSITO) · `2` `BRD-2024-00002` (CRIADA) · `3` `BRD-2024-00003` (ENTREGUE) |
| Motivos de NC | `1`–`6` (`AVARIA_PRODUTO`, `NAO_ENTREGUE`, `ENDERECO_INCORRETO`, `RECUSADO`, `EXTRAVIO`, `OUTROS`) |

Fluxo de status:

```
CRIADA → COLETADA → EM_TRANSITO → SAIU_ENTREGA → ENTREGUE
                                               ↘ DEVOLVIDA
```

---

## Endpoints

```
GET    /transportadoras
POST   /transportadoras
GET    /transportadoras/{id}
PATCH  /transportadoras/{id}/desativar
PATCH  /transportadoras/{id}/reativar

GET    /entregas
POST   /entregas
GET    /entregas/{id}
PATCH  /entregas/{id}/status

GET    /motivos-nao-conformidade
POST   /entregas/{id}/nao-conformidades
GET    /entregas/{id}/nao-conformidades

GET    /rastreamento/{codigo}
```

### Status HTTP

| Código | Quando |
|---|---|
| `200` | Listagem ou consulta ok |
| `201` | Recurso criado |
| `404` | Entrega, motivo ou rota não existe |
| `422` | Validação: campo obrigatório, transportadora inativa, transição de status inválida |

---

## Exemplos de requisição

Base: `http://localhost:8000`

### Listar motivos ativos

```bash
curl.exe http://localhost:8000/motivos-nao-conformidade
```

`200` — só motivos com `ativo = 1`:

```json
[
  {
    "id": 1,
    "codigo": "AVARIA_PRODUTO",
    "descricao": "Produto com avaria ou dano",
    "ativo": true
  }
]
```

### Registrar não conformidade

`id_motivo` é obrigatório. Entrega e motivo precisam existir. `descricao` é opcional (observação da ocorrência — **não** precisa ser igual à descrição do motivo).

```bash
curl.exe -X POST http://localhost:8000/entregas/1/nao-conformidades -H "Content-Type: application/json" -d "{\"id_motivo\": 1, \"descricao\": \"Caixa amassada na coleta\"}"
```

`201`:

```json
{
  "id": 1,
  "id_entrega": 1,
  "id_motivo": 1,
  "descricao": "Caixa amassada na coleta",
  "created_at": "2026-09-23 02:03:12"
}
```

Erros: `422` sem `id_motivo` · `404` se a entrega ou o motivo não existirem.

### Listar NCs de uma entrega

```bash
curl.exe http://localhost:8000/entregas/1/nao-conformidades
```

```json
[
  {
    "id": 1,
    "id_entrega": 1,
    "id_motivo": 1,
    "descricao": "Caixa amassada na coleta",
    "created_at": "2026-09-23 02:03:12",
    "motivo": {
      "codigo": "AVARIA_PRODUTO",
      "descricao": "Produto com avaria ou dano"
    }
  }
]
```

### Rastreamento público

```bash
curl.exe http://localhost:8000/rastreamento/BRD-2024-00001
```

```json
{
  "codigo": "BRD-2024-00001",
  "status": "EM_TRANSITO",
  "data_prazo": "2024-12-20",
  "transportadora": "Transportes Rápido Ltda",
  "destinatario": {
    "nome": "João da Silva",
    "cidade": "Porto Alegre",
    "uf": "RS"
  },
  "rastreamento": [
    {
      "status": "CRIADA",
      "descricao": "Entrega cadastrada no sistema",
      "cidade": "São Paulo",
      "uf": "SP",
      "data": "2026-09-22 03:14:08"
    }
  ]
}
```

Código inexistente → `404`.

### Criar entrega (transportadora ativa)

```bash
curl.exe -X POST http://localhost:8000/entregas -H "Content-Type: application/json" -d "{\"id_transportadora\": 1, \"id_remetente\": 1, \"id_destinatario\": 1, \"data_prazo\": \"2026-12-31\", \"peso_kg\": 10.5, \"volumes\": 2}"
```

`201` com os dados da entrega.

### Transportadora inativa (bug corrigido)

A transportadora `3` está desativada. O cadastro é recusado:

```bash
curl.exe -X POST http://localhost:8000/entregas -H "Content-Type: application/json" -d "{\"id_transportadora\": 3, \"id_remetente\": 1, \"id_destinatario\": 1, \"data_prazo\": \"2026-12-31\", \"peso_kg\": 10.5, \"volumes\": 2}"
```

`422`: `{"erro": "Transportadora está inativa"}`

Passo a passo da correção e texto para o time de operações: [BUGFIX.md](./BUGFIX.md).

### Avançar status

A entrega `2` está `CRIADA`. Próximo status válido: `COLETADA`.

```bash
curl.exe -X PATCH http://localhost:8000/entregas/2/status -H "Content-Type: application/json" -d "{\"status\": \"COLETADA\", \"descricao\": \"Carga coletada\", \"cidade\": \"Sao Paulo\", \"uf\": \"SP\"}"
```

`CRIADA` → `ENTREGUE` (pulo) retorna `422`.

---

## Decisões técnicas

**Duas `descricao`.** Em `motivos_nao_conformidade` é o texto fixo do catálogo (`VARCHAR(150)`). Em `nao_conformidades` é observação livre daquela ocorrência (`VARCHAR(500)`, opcional). O vínculo é só o `id_motivo`.

**Migrations.** `codigo` tem `UNIQUE KEY` (sem índice extra). `nao_conformidades` tem FK para `entregas.id` e `motivos_nao_conformidade.id`.

**Rastreamento público.** `GET /rastreamento/{codigo}` devolve status, prazo, transportadora, destinatário e histórico. Não expõe id interno, remetente, peso nem volumes.

**Lista de NCs.** O GET inclui o motivo (`codigo` e `descricao`) para não exigir uma segunda chamada.

**Config de banco.** `config/database.php` lê primeiro variáveis de ambiente e depois o `.env`. No Docker o host é o serviço `db`.

**Docker.** A porta 3306 do MySQL não é publicada no host (evita conflito com MySQL local). Seed só roda se `transportadoras` estiver vazia.

**Testes.** PHPUnit 10. `json()` / `body()` e `registerRoutes()` foram extraídos para o teste despachar o router em processo. Em `TESTING`, `json()` lança `JsonResponseException` no lugar de `exit`.

---

## Autora

Francielli Andreghetto — [GitHub](https://github.com/FrancielliAndreghetto)
