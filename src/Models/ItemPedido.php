<?php

namespace App\Models;

use App\Exceptions\PedidoInvalidoException;

class ItemPedido {
    private Produto $produto;
    private int $quantidade;
    private float $precoUnitario;
    private float $subtotal;
    public function __construct(Produto $produto, int $quantidade) {
        if ($quantidade <= 0) {
            throw new PedidoInvalidoException("A quantidade do item deve ser maior que zero.");
        }
        $this->produto = $produto;
        $this->quantidade = $quantidade;
        $this->precoUnitario = $produto->getPreco();
        $this->calcularSubtotal();
    }
    private function calcularSubtotal(): void {
        $this->subtotal = $this->precoUnitario * $this->quantidade;
    }
    public function getProduto(): Produto {
        return $this->produto;
    }
    public function getQuantidade(): int {
        return $this->quantidade;
    }
    public function getPrecoUnitario(): float {
        return $this->precoUnitario;
    }
    public function getSubtotal(): float {
        return $this->subtotal;
    }
    public function getFrete(): float {
        return $this->produto->calcularFrete() * $this->quantidade;
    }
    public function getTotalComFrete(): float {
        return $this->produto->calcularPrecoFinal() * $this->quantidade;
    }
}
