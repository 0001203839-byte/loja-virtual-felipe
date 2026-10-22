<?php

use App\Exceptions\EstoqueInsuficienteException;
use App\Exceptions\PagamentoException;
use App\Exceptions\PedidoInvalidoException;
use App\Models\Cliente;
use App\Models\Endereco;
use App\Models\Pedido;
use App\Models\ProdutoDigital;
use App\Models\ProdutoFisico;
use App\Pagamentos\PagamentoPix;
use App\Services\PedidoService;

date_default_timezone_set("America/Sao_Paulo");

spl_autoload_register(function (string $classe) {
    $prefixo = "App\\";

    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }

    $arquivo = __DIR__ . "/src/" . str_replace("\\", "/", substr($classe, strlen($prefixo))) . ".php";

    if (file_exists($arquivo)) {
        require_once $arquivo;
    }
});

function teste(string $titulo, callable $acao): void
{
    echo PHP_EOL . ">> {$titulo}" . PHP_EOL;

    try {
        $acao();
        echo "Nenhum erro foi gerado." . PHP_EOL;
    } catch (EstoqueInsuficienteException $e) {
        echo "[Estoque] " . $e->getMessage() . PHP_EOL;
    } catch (PedidoInvalidoException $e) {
        echo "[Pedido] " . $e->getMessage() . PHP_EOL;
    } catch (PagamentoException $e) {
        echo "[Pagamento] " . $e->getMessage() . PHP_EOL;
    } catch (InvalidArgumentException $e) {
        echo "[Dados inválidos] " . $e->getMessage() . PHP_EOL;
    }
}

$endereco = new Endereco("Rua das Flores", "120", "Centro", "Belo Horizonte", "MG", "30110-000", "Apto 302");
$cliente = new Cliente(1, "Carlos Silva", "123.456.789-00", "carlos@email.com", "(31) 99999-0000", $endereco);

$teclado = new ProdutoFisico(1, "Teclado Mecânico", 350.00, 10, 1.2, 44, 4, 14);
$curso = new ProdutoDigital(2, "Curso PHP Orientado a Objetos", 199.90, 100, "MP4", 2048, "https://loja.com/download/curso-php");

$service = new PedidoService();

try {
    $pedido = new Pedido(1001, $cliente);
    $pedido->adicionarItem($teclado, 2);
    $pedido->adicionarItem($curso, 1);

    $service->processar($pedido, new PagamentoPix("carlos@email.com"));

    echo $pedido->exibirResumo();

    echo PHP_EOL . "Estoque atualizado:" . PHP_EOL;
    echo "{$teclado->getNome()}: {$teclado->getEstoque()}" . PHP_EOL;
    echo "{$curso->getNome()}: {$curso->getEstoque()}" . PHP_EOL;
} catch (Exception $e) {
    echo "Erro ao processar o pedido: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "========== TESTES ==========" . PHP_EOL;

teste("Teste 1: comprar quantidade maior que o estoque", function () use ($cliente, $teclado, $service) {
    $pedido = new Pedido(1002, $cliente);
    $pedido->adicionarItem($teclado, 50);
    $service->processar($pedido, new PagamentoPix("carlos@email.com"));
});

teste("Teste 2: adicionar quantidade zero", function () use ($cliente, $curso) {
    $pedido = new Pedido(1003, $cliente);
    $pedido->adicionarItem($curso, 0);
});

teste("Teste 3: criar produto com preço negativo", function () {
    new ProdutoFisico(3, "Mouse Gamer", -80.00, 5, 0.3);
});

teste("Teste 4: processar pedido sem produtos", function () use ($cliente, $service) {
    $pedido = new Pedido(1004, $cliente);
    $service->processar($pedido, new PagamentoPix("carlos@email.com"));
});

teste("Teste 5: adicionar produto em pedido concluído", function () use ($cliente, $curso, $teclado, $service) {
    $pedido = new Pedido(1005, $cliente);
    $pedido->adicionarItem($curso, 1);
    $service->processar($pedido, new PagamentoPix("carlos@email.com"));
    $service->finalizarPedido($pedido);
    echo "Status do pedido 1005: {$pedido->getStatus()}" . PHP_EOL;
    $pedido->adicionarItem($teclado, 1);
});

teste("Teste 6: pagar pedido cancelado", function () use ($cliente, $curso, $service) {
    $pedido = new Pedido(1006, $cliente);
    $pedido->adicionarItem($curso, 1);
    $pedido->cancelar();
    $service->processar($pedido, new PagamentoPix("carlos@email.com"));
});
