<?php

namespace App\Pagamentos;

use App\Exceptions\PagamentoException;
use App\Interfaces\PagamentoInterface;
class PagamentoBoleto implements PagamentoInterface {
    private int $diasVencimento;
    private string $linhaDigitavel = "";
    public function __construct(int $diasVencimento = 3) {
        $this->diasVencimento = $diasVencimento;
    }
    public function pagar(float $valor): string {
        if ($valor <= 0) {
            throw new PagamentoException("Valor inválido para geração do boleto.");
        }
        if ($this->diasVencimento <= 0) {
            throw new PagamentoException("O prazo de vencimento do boleto deve ser maior que zero.");
        }
        $vencimento = date("d/m/Y", strtotime("+{$this->diasVencimento} days"));
        $this->linhaDigitavel = "34191." . rand(10000, 99999) . " " . rand(10000, 99999) . "." . rand(100000, 999999);
        return "Pagamento realizado com sucesso. Boleto {$this->linhaDigitavel} com vencimento em {$vencimento}.";
    }
    public function getLinhaDigitavel(): string {
        return $this->linhaDigitavel;
    }
    public function getNome(): string {
        return "Boleto";
    }
}
