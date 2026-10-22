<?php

namespace App\Pagamentos;

use App\Exceptions\PagamentoException;
use App\Interfaces\PagamentoInterface;

class PagamentoPix implements PagamentoInterface
{
    private string $chave;
    private string $codigoTransacao = "";

    public function __construct(string $chave)
    {
        $this->chave = $chave;
    }

    public function pagar(float $valor): string
    {
        if ($valor <= 0) {
            throw new PagamentoException("Valor inválido para pagamento via PIX.");
        }

        if (trim($this->chave) === "") {
            throw new PagamentoException("Chave PIX não informada.");
        }

        $this->codigoTransacao = "PIX" . date("YmdHis") . rand(1000, 9999);

        return "Pagamento realizado com sucesso.";
    }

    public function getCodigoTransacao(): string
    {
        return $this->codigoTransacao;
    }

    public function getNome(): string
    {
        return "PIX";
    }
}
