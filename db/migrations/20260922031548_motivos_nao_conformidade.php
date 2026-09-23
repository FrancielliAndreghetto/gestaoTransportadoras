<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MotivosNaoConformidade extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE motivos__nao_conformidade (
                id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                codigo    VARCHAR(30) NOT NULL,
                descricao VARCHAR(150) NOT NULL,
                ativo  TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY uq_motivosnaoconfirmidade_codigo  (codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS motivos__nao_conformidade');
    }
}
