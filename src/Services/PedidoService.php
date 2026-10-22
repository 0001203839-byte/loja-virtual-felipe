<?php

namespace App\Services;

use App\Exceptions\EstoqueInsuficienteException;
use App\Exceptions\PagamentoException;
use App\Exceptions\PedidoInvalidoException;
use App\Interfaces\PagamentoInterface;
use App\Models\Pedido;
class PedidoService {
    public function validarPedido(Pedido $pedido): void {
    if ($pedido->getStatus() === Pedido::CANCELADO) {
            throw new PagamentoException("Um pedido cancelado não pode ser pago.");
        }
    if ($pedido->getStatus() === Pedido::CONCLUIDO) {
            throw new PedidoInvalidoException("O pedido já foi concluído.");
        }
    if ($pedido->getStatus() !== Pedido::ABERTO) {
            throw new PedidoInvalidoException("O pedido não está em aberto (status atual: {$pedido->getStatus()}).");
        }
    if (!$pedido->possuiItens()) {
            throw new PedidoInvalidoException("O pedido precisa ter pelo menos um item para ser processado.");
        }
    }
    public function verificarEstoque(Pedido $pedido): void {
        foreach ($pedido->listarItens() as $item) {
        $produto = $item->getProduto();
        if ($item->getQuantidade() > $produto->getEstoque()) {
                throw new EstoqueInsuficienteException("Estoque insuficiente para {$produto->getNome()}. Disponível: {$produto->getEstoque()}, solicitado: {$item->getQuantidade()}.");
            }
        }
    }
    public function processarPagamento(Pedido $pedido, PagamentoInterface $pagamento): void {
        $pedido->alterarStatus(Pedido::AGUARDANDO_PAGAMENTO);
        try {
        $mensagem = $pagamento->pagar($pedido->calcularTotal());
        } catch (PagamentoException $e) {
         $pedido->alterarStatus(Pedido::ABERTO);
            throw $e;
        }
        $pedido->registrarPagamento($pagamento, $mensagem);
    }
    public function atualizarEstoque(Pedido $pedido): void {
        if ($pedido->getStatus() !== Pedido::PAGO) {
        throw new PedidoInvalidoException("O estoque só pode ser atualizado após a confirmação do pagamento.");
        }
        foreach ($pedido->listarItens() as $item) {
            $item->getProduto()->removerEstoque($item->getQuantidade());
        }
    }
    public function processar(Pedido $pedido, PagamentoInterface $pagamento): void {
    $this->validarPedido($pedido);
    $this->verificarEstoque($pedido);
    $this->processarPagamento($pedido, $pagamento);
    $this->atualizarEstoque($pedido);
    }
    public function finalizarPedido(Pedido $pedido): void {
        if ($pedido->getStatus() === Pedido::PAGO) {
            $pedido->alterarStatus(Pedido::ENVIADO);
        }
        if ($pedido->getStatus() !== Pedido::ENVIADO) {
            throw new PedidoInvalidoException("O pedido precisa estar pago antes de ser concluído.");
        }
        $pedido->alterarStatus(Pedido::CONCLUIDO);
    }
}
