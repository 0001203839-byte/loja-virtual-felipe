<?php

namespace App\Models;

use App\Exceptions\PedidoInvalidoException;
use App\Interfaces\PagamentoInterface;
use DateTime;

class Pedido {
    public const ABERTO = "Aberto";
    public const AGUARDANDO_PAGAMENTO = "Aguardando Pagamento";
    public const PAGO = "Pago";
    public const ENVIADO = "Enviado";
    public const CONCLUIDO = "Concluído";
    public const CANCELADO = "Cancelado";
    private const TRANSICOES = [
        self::ABERTO => [self::AGUARDANDO_PAGAMENTO, self::CANCELADO],
        self::AGUARDANDO_PAGAMENTO => [self::PAGO, self::ABERTO, self::CANCELADO],
        self::PAGO => [self::ENVIADO],
        self::ENVIADO => [self::CONCLUIDO],
        self::CONCLUIDO => [],
        self::CANCELADO => [],
    ];
    private int $numero;
    private Cliente $cliente;
    private DateTime $data;
    private array $itens = [];
    private string $status;
    private float $valorTotal = 0;
    private ?PagamentoInterface $formaPagamento = null;
    private string $mensagemPagamento = "";
    public function __construct(int $numero, Cliente $cliente) {
        $this->numero = $numero;
        $this->cliente = $cliente;
        $this->data = new DateTime();
        $this->status = self::ABERTO;
    }
    public function getNumero(): int {
        return $this->numero;
    }
    public function getCliente(): Cliente {
        return $this->cliente;
    }
    public function getData(): DateTime {
        return $this->data;
    }
    public function getStatus(): string {
        return $this->status;
    }
    public function getValorTotal(): float {
        return $this->valorTotal;
    }
    public function getFormaPagamento(): ?PagamentoInterface {
        return $this->formaPagamento;
    }
    public function adicionarItem(Produto $produto, int $quantidade): void {
        if ($this->status === self::CONCLUIDO || $this->status === self::CANCELADO) {
            throw new PedidoInvalidoException("Não é possível adicionar itens a um pedido com status {$this->status}.");
        }
        if ($this->status !== self::ABERTO) {
            throw new PedidoInvalidoException("O pedido já está em processamento e não aceita novos itens.");
        }
        $codigo = $produto->getCodigo();
        if (isset($this->itens[$codigo])) {
            $quantidade += $this->itens[$codigo]->getQuantidade();
        }
        $this->itens[$codigo] = new ItemPedido($produto, $quantidade);
        $this->calcularTotal();
    }
    public function removerItem(int $codigoProduto): void {
        if ($this->status !== self::ABERTO) {
            throw new PedidoInvalidoException("Só é possível remover itens de um pedido em aberto.");
        }
        if (!isset($this->itens[$codigoProduto])) {
            throw new PedidoInvalidoException("O produto {$codigoProduto} não está no pedido.");
        }
        unset($this->itens[$codigoProduto]);
        $this->calcularTotal();
    }
    public function listarItens(): array {
        return array_values($this->itens);
    }
    public function possuiItens(): bool {
        return count($this->itens) > 0;
    }
    public function calcularSubtotal(): float {
        $soma = 0;
        foreach ($this->itens as $item) {
            $soma += $item->getSubtotal();
        }
        return round($soma, 2);
    }
    public function calcularFrete(): float {
        $soma = 0;
        foreach ($this->itens as $item) {
            $soma += $item->getFrete();
        }
        return round($soma, 2);
    }
    public function calcularTotal(): float {
        $soma = 0;
        foreach ($this->itens as $item) {
            $soma += $item->getTotalComFrete();
        }
        $this->valorTotal = round($soma, 2);
        return $this->valorTotal;
    }
    public function alterarStatus(string $novoStatus): void {
        if (!array_key_exists($novoStatus, self::TRANSICOES)) {
            throw new PedidoInvalidoException("Status inválido: {$novoStatus}.");
        }
        if (!in_array($novoStatus, self::TRANSICOES[$this->status], true)) {
            throw new PedidoInvalidoException("Não é permitido mudar o pedido de {$this->status} para {$novoStatus}.");
        }
        $this->status = $novoStatus;
    }
    public function cancelar(): void {
        $this->alterarStatus(self::CANCELADO);
    }
    public function registrarPagamento(PagamentoInterface $pagamento, string $mensagem): void {
        $this->alterarStatus(self::PAGO);
        $this->formaPagamento = $pagamento;
        $this->mensagemPagamento = $mensagem;
    }
    public function exibirResumo(): string {
        $linha = str_repeat("=", 42);
        $traco = str_repeat("-", 42);
        $texto = $linha . PHP_EOL;
        $texto .= "PEDIDO Nº {$this->numero}" . PHP_EOL;
        $texto .= $linha . PHP_EOL . PHP_EOL;
        $texto .= "Cliente: {$this->cliente->getNome()}" . PHP_EOL . PHP_EOL;
        $texto .= $traco . PHP_EOL;
        $texto .= "ITENS" . PHP_EOL;
        $texto .= $traco . PHP_EOL . PHP_EOL;
        foreach ($this->itens as $item) {
            $texto .= "Produto: {$item->getProduto()->getNome()}" . PHP_EOL;
            $texto .= "Quantidade: {$item->getQuantidade()}" . PHP_EOL;
            $texto .= "Valor unitário: " . $this->moeda($item->getPrecoUnitario()) . PHP_EOL;
            $texto .= "Subtotal: " . $this->moeda($item->getSubtotal()) . PHP_EOL . PHP_EOL;
        }
        $pagamento = $this->formaPagamento ? $this->formaPagamento->getNome() : "Não informado";
        $texto .= $traco . PHP_EOL;
        $texto .= "Frete: " . $this->moeda($this->calcularFrete()) . PHP_EOL;
        $texto .= "Valor Total: " . $this->moeda($this->calcularTotal()) . PHP_EOL;
        $texto .= "Forma de pagamento: {$pagamento}" . PHP_EOL;
        $texto .= "Status: {$this->status}" . PHP_EOL;
        if ($this->mensagemPagamento !== "") {
            $texto .= $this->mensagemPagamento . PHP_EOL;
        }
        $texto .= $linha . PHP_EOL;
        return $texto;
    }
    private function moeda(float $valor): string {
        return "R$ " . number_format($valor, 2, ",", ".");
    }
}
