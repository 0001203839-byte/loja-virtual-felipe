<?php

namespace App\Models;

use App\Exceptions\EstoqueInsuficienteException;
use InvalidArgumentException;

abstract class Produto
{
    private int $codigo;
    private string $nome;
    private float $preco;
    private int $estoque;

    public function __construct(int $codigo, string $nome, float $preco, int $estoque)
    {
        if ($preco < 0) {
            throw new InvalidArgumentException("O preço do produto não pode ser negativo.");
        }

        if ($estoque < 0) {
            throw new InvalidArgumentException("O estoque do produto não pode ser negativo.");
        }

        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->preco = $preco;
        $this->estoque = $estoque;
    }

    public function getCodigo(): int
    {
        return $this->codigo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function getEstoque(): int
    {
        return $this->estoque;
    }

    public function alterarPreco(float $novoPreco): void
    {
        if ($novoPreco < 0) {
            throw new InvalidArgumentException("O preço do produto não pode ser negativo.");
        }

        $this->preco = $novoPreco;
    }

    public function adicionarEstoque(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException("A quantidade adicionada deve ser maior que zero.");
        }

        $this->estoque += $quantidade;
    }

    public function removerEstoque(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException("A quantidade removida deve ser maior que zero.");
        }

        if ($quantidade > $this->estoque) {
            throw new EstoqueInsuficienteException(
                "Estoque insuficiente para o produto {$this->nome}. Disponível: {$this->estoque}, solicitado: {$quantidade}."
            );
        }

        $this->estoque -= $quantidade;
    }

    public function calcularFrete(): float
    {
        return 0.0;
    }

    abstract public function calcularPrecoFinal(): float;

    abstract public function getTipo(): string;
}
