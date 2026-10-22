<?php

namespace App\Pagamentos;

use App\Exceptions\PagamentoException;
use App\Interfaces\PagamentoInterface;

class PagamentoCartao implements PagamentoInterface
{
    private string $numeroCartao;
    private string $titular;
    private int $parcelas;

    public function __construct(string $numeroCartao, string $titular, int $parcelas = 1)
    {
        $this->numeroCartao = preg_replace("/\D/", "", $numeroCartao);
        $this->titular = $titular;
        $this->parcelas = $parcelas;
    }

    public function pagar(float $valor): string
    {
        if ($valor <= 0) {
            throw new PagamentoException("Valor inválido para pagamento no cartão.");
        }

        if (strlen($this->numeroCartao) !== 16) {
            throw new PagamentoException("Número do cartão inválido.");
        }

        if ($this->parcelas < 1 || $this->parcelas > 12) {
            throw new PagamentoException("O parcelamento deve ser entre 1 e 12 vezes.");
        }

        $valorParcela = number_format($valor / $this->parcelas, 2, ",", ".");
        $final = substr($this->numeroCartao, -4);

        return "Pagamento realizado com sucesso. Cartão final {$final} em {$this->parcelas}x de R$ {$valorParcela}.";
    }

    public function getNome(): string
    {
        return "Cartão de Crédito";
    }
}
