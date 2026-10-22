<?php

namespace App\Interfaces;

interface PagamentoInterface
{
    public function pagar(float $valor): string;

    public function getNome(): string;
}
