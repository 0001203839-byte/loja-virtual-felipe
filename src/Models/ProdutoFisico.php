<?php

namespace App\Models;

use InvalidArgumentException;

class ProdutoFisico extends Produto
{
    // Regra do frete: valor fixo de R$ 10,00 + R$ 5,00 por kg, cobrado por unidade
    private const FRETE_BASE = 10.00;
    private const FRETE_POR_KG = 5.00;

    private float $peso;
    private float $largura;
    private float $altura;
    private float $profundidade;

    public function __construct(
        int $codigo,
        string $nome,
        float $preco,
        int $estoque,
        float $peso,
        float $largura = 0,
        float $altura = 0,
        float $profundidade = 0
    ) {
        parent::__construct($codigo, $nome, $preco, $estoque);

        if ($peso <= 0) {
            throw new InvalidArgumentException("O peso do produto físico deve ser maior que zero.");
        }

        $this->peso = $peso;
        $this->largura = $largura;
        $this->altura = $altura;
        $this->profundidade = $profundidade;
    }

    public function getPeso(): float
    {
        return $this->peso;
    }

    public function getLargura(): float
    {
        return $this->largura;
    }

    public function getAltura(): float
    {
        return $this->altura;
    }

    public function getProfundidade(): float
    {
        return $this->profundidade;
    }

    public function calcularFrete(): float
    {
        return round(self::FRETE_BASE + ($this->peso * self::FRETE_POR_KG), 2);
    }

    public function calcularPrecoFinal(): float
    {
        return $this->getPreco() + $this->calcularFrete();
    }

    public function getTipo(): string
    {
        return "Físico";
    }
}
