<?php

namespace App\Models;

class Cliente
{
    private int $codigo;
    private string $nome;
    private string $cpf;
    private string $email;
    private string $telefone;
    private array $enderecos = [];

    public function __construct(
        int $codigo,
        string $nome,
        string $cpf,
        string $email,
        string $telefone,
        Endereco $endereco
    ) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->enderecos[] = $endereco;
    }

    public function getCodigo(): int
    {
        return $this->codigo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): void
    {
        $this->telefone = $telefone;
    }

    public function adicionarEndereco(Endereco $endereco): void
    {
        $this->enderecos[] = $endereco;
    }

    public function getEnderecos(): array
    {
        return $this->enderecos;
    }

    public function getEnderecoPrincipal(): Endereco
    {
        return $this->enderecos[0];
    }
}
