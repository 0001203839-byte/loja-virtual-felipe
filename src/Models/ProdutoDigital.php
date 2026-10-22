<?php

namespace App\Models;

class ProdutoDigital extends Produto
{
    private float $tamanhoArquivo;
    private string $formato;
    private string $linkDownload;

    public function __construct(
        int $codigo,
        string $nome,
        float $preco,
        int $estoque,
        string $formato,
        float $tamanhoArquivo = 0,
        string $linkDownload = ""
    ) {
        parent::__construct($codigo, $nome, $preco, $estoque);

        $this->formato = $formato;
        $this->tamanhoArquivo = $tamanhoArquivo;
        $this->linkDownload = $linkDownload;
    }

    public function getTamanhoArquivo(): float
    {
        return $this->tamanhoArquivo;
    }

    public function getFormato(): string
    {
        return $this->formato;
    }

    public function getLinkDownload(): string
    {
        return $this->linkDownload;
    }

    public function calcularPrecoFinal(): float
    {
        return $this->getPreco();
    }

    public function getTipo(): string
    {
        return "Digital";
    }
}
