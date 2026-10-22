<?php

namespace App\Models;

class Endereco
{
    private string $logradouro;
    private string $numero;
    private string $complemento;
    private string $bairro;
    private string $cidade;
    private string $estado;
    private string $cep;

    public function __construct(
        string $logradouro,
        string $numero,
        string $bairro,
        string $cidade,
        string $estado,
        string $cep,
        string $complemento = ""
    ) {
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cep = $cep;
        $this->complemento = $complemento;
    }

    public function getLogradouro(): string
    {
        return $this->logradouro;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getComplemento(): string
    {
        return $this->complemento;
    }

    public function getBairro(): string
    {
        return $this->bairro;
    }

    public function getCidade(): string
    {
        return $this->cidade;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getCep(): string
    {
        return $this->cep;
    }

    public function getEnderecoCompleto(): string
    {
        $texto = "{$this->logradouro}, {$this->numero}";

        if ($this->complemento !== "") {
            $texto .= " - {$this->complemento}";
        }

        return $texto . " - {$this->bairro}, {$this->cidade}/{$this->estado} - CEP {$this->cep}";
    }
}
