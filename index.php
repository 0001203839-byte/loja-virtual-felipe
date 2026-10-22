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

spl_autoload_register(function ($classe) {
    $classe = str_replace("App\\", "", $classe);
    $arquivo = __DIR__ . "/src/" . str_replace("\\", "/", $classe) . ".php";
    if (file_exists($arquivo)) {
        require_once $arquivo;
    }
});

$endereco = new Endereco("Rua dos Ricos", "120", "Centro", "Belo Horizonte", "MG", "30110-000", "Apto 302");
$cliente = new Cliente(1, "Felipe Souza", "123.456.789-00", "felipe@email.com", "(31) 99999-0000", $endereco);
$teclado = new ProdutoFisico(1, "Teclado Mecânico", 350.00, 10, 1.2, 44, 4, 14);
$curso = new ProdutoDigital(2, "Curso PHP Orientado a Objetos", 199.90, 100, "MP4", 2048, "https://loja.com/download/curso-php");

$service = new PedidoService();
try {
    $pedido = new Pedido(1001, $cliente);
    $pedido->adicionarItem($teclado, 2);
    $pedido->adicionarItem($curso, 1);
    $service->processar($pedido, new PagamentoPix("felipe@email.com"));

    echo $pedido->exibirResumo();
    echo PHP_EOL . "Estoque atualizado:" . PHP_EOL;
    echo $teclado->getNome() . ": " . $teclado->getEstoque() . PHP_EOL;
    echo $curso->getNome() . ": " . $curso->getEstoque() . PHP_EOL;
} catch (Exception $e) {
    echo "Erro ao processar o pedido: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "========== TESTES ==========" . PHP_EOL;

echo PHP_EOL . ">> Teste 1: comprar quantidade maior que o estoque" . PHP_EOL;
try {
    $pedido2 = new Pedido(1002, $cliente);
    $pedido2->adicionarItem($teclado, 50);
    $service->processar($pedido2, new PagamentoPix("felipe@email.com"));
} catch (EstoqueInsuficienteException $e) {
    echo "[Estoque] " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL . ">> Teste 2: adicionar quantidade zero" . PHP_EOL;
try {
  $pedido3 = new Pedido(1003, $cliente);
  $pedido3->adicionarItem($curso, 0);
} catch (PedidoInvalidoException $e) {
    echo "[Pedido] " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . ">> Teste 3: criar produto com preço negativo" . PHP_EOL;
try {
    $mouse = new ProdutoFisico(3, "Mouse Gamer", -80.00, 5, 0.3);
}
catch (InvalidArgumentException $e) {
    echo "[Dados inválidos] " . $e->getMessage() . PHP_EOL;
}


echo PHP_EOL . ">> Teste 4: processar pedido sem produtos" . PHP_EOL;
try {
    $pedido4 = new Pedido(1004, $cliente);
    $service->processar($pedido4, new PagamentoPix("felipe@email.com"));
} catch (PedidoInvalidoException $e) {
    echo "[Pedido] " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL . ">> Teste 5: adicionar produto em pedido concluído" . PHP_EOL;
try {
    $pedido5 = new Pedido(1005, $cliente);
    $pedido5->adicionarItem($curso, 1);
    $service->processar($pedido5, new PagamentoPix("felipe@email.com"));
    $service->finalizarPedido($pedido5);
    echo "Status do pedido 1005: " . $pedido5->getStatus() . PHP_EOL;
    $pedido5->adicionarItem($teclado,1);
} catch (PedidoInvalidoException $e) {
    echo "[Pedido] " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . ">> Teste 6: pagar pedido cancelado" . PHP_EOL;
try {
    $pedido6 = new Pedido(1006, $cliente);
    $pedido6->adicionarItem($curso, 1);
    $pedido6->cancelar();
    $service->processar($pedido6, new PagamentoPix("felipe@email.com"));
} catch (PagamentoException $e) {
    echo "[Pagamento] ".$e->getMessage() . PHP_EOL;
}
