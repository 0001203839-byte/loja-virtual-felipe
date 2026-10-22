# Loja POO
Atividade prática de PHP com Programação Orientada a Objetos.
Aluno: Felipe
## Objetivo
Fazer um sistema de loja virtual em PHP puro usando POO, simulando uma compra do começo ao fim: cadastro dos produtos e do cliente, montagem do pedido, pagamento e baixa no estoque.
## Descrição
O sistema tem produtos físicos e digitais, cliente com endereço, pedido com vários itens e três formas de pagamento (PIX, cartão e boleto).
Separei as responsabilidades em pastas. As regras de processamento do pedido ficam no `PedidoService`, e o `index.php` só monta o cenário de teste e mostra o resultado na tela.
## Estrutura das pastas
```
loja-poo/
├── src/
│   ├── Models/
│   │   ├── Produto.php
│   │   ├── ProdutoFisico.php
│   │   ├── ProdutoDigital.php
│   │   ├── Cliente.php
│   │   ├── Endereco.php
│   │   ├── Pedido.php
│   │   └── ItemPedido.php
│   ├── Interfaces/
│   │   └── PagamentoInterface.php
│   ├── Pagamentos/
│   │   ├── PagamentoPix.php
│   │   ├── PagamentoCartao.php
│   │   └── PagamentoBoleto.php
│   ├── Services/
│   │   └── PedidoService.php
│   └── Exceptions/
│       ├── EstoqueInsuficienteException.php
│       ├── PedidoInvalidoException.php
│       └── PagamentoException.php
├── docs/
│   ├── diagrama-classes.png
│   └── saida-execucao.txt
├── diagrama-classes.mermaid
├── index.php
└── README.md
```
## Classes
- `Produto`: classe abstrata com código, nome, preço e estoque.
- `ProdutoFisico`: herda de Produto, tem peso e medidas e cobra frete.
- `ProdutoDigital`: herda de Produto, tem formato, tamanho e link de download. Não cobra frete.
- `Cliente`: dados do cliente e a lista de endereços.
- `Endereco`: endereço do cliente.
- `ItemPedido`: guarda o produto, a quantidade e calcula o subtotal.
- `Pedido`: itens, status, valor total e o resumo do pedido.
- `PagamentoInterface`: interface que toda forma de pagamento implementa.
- `PagamentoPix`, `PagamentoCartao` e `PagamentoBoleto`: as formas de pagamento.
- `PedidoService`: valida o pedido, confere o estoque, processa o pagamento, baixa o estoque e finaliza.
- `EstoqueInsuficienteException`, `PedidoInvalidoException` e `PagamentoException`: exceções criadas para o projeto.
## Conceitos de POO
**Encapsulamento**
Deixei todos os atributos como `private`. Para ler uso os getters, e para alterar só por métodos que validam antes, como `alterarPreco()`, `adicionarEstoque()` e `removerEstoque()`. O CPF do cliente não tem setter, então não dá para mudar depois de criado. O subtotal do item é calculado dentro da própria classe, então não tem como passar ele na mão. O status do pedido só muda pelo `alterarStatus()`, que bloqueia mudanças que não fazem sentido, por exemplo ir de Aberto direto para Concluído.
**Herança**
`ProdutoFisico` e `ProdutoDigital` herdam de `Produto`. As três exceções herdam de `Exception`.
**Abstração**
`Produto` é abstrata porque não existe um produto "genérico" na loja, ele sempre vai ser físico ou digital. Ela junta o que é comum a todos e obriga as classes filhas a implementar o `calcularPrecoFinal()`.
**Polimorfismo**
O `calcularPrecoFinal()` funciona diferente em cada produto: o físico soma o frete e o digital devolve só o preço. O pedido chama o mesmo método sem precisar saber qual é o tipo do produto.
Com o pagamento é igual. O `PedidoService` recebe qualquer objeto que implemente `PagamentoInterface` e chama `pagar()`, e cada forma de pagamento faz isso do seu jeito.
**Composição**
- Cliente → Endereco: o cliente já é criado com um endereço obrigatório e pode ter outros.
- Pedido → ItemPedido: os itens são criados pelo próprio pedido no `adicionarItem()` e só existem dentro dele.
- ItemPedido → Produto: cada item guarda o produto vendido e o preço unitário do momento da compra.
**Interface**
A `PagamentoInterface` obriga toda forma de pagamento a ter `pagar()` e `getNome()`. Assim o service não depende de PIX, cartão ou boleto especificamente. Se precisar de uma forma de pagamento nova, é só criar outra classe implementando a interface, sem mexer no resto.
**Namespaces e autoload**
Usei os namespaces `App\Models`, `App\Services`, `App\Pagamentos`, `App\Interfaces` e `App\Exceptions`. O autoload é feito com `spl_autoload_register()` no `index.php`, que transforma o namespace `App\` no caminho da pasta `src/`.
## Regras de negócio
1. Produto não pode ter preço negativo.
2. Estoque não pode ficar negativo.
3. Item tem que ter quantidade maior que zero.
4. Pedido precisa ter pelo menos um item para ser processado.
5. Pedido cancelado não pode ser pago.
6. Pedido concluído não aceita novos itens.
7. O pedido só pode ser concluído depois de pago.
8. O estoque só é baixado depois que o pagamento é confirmado.
9. Não dá para vender mais do que tem no estoque.
10. Produto digital não tem frete.
Regra do frete que usei: R$ 10,00 fixo + R$ 5,00 por kg, cobrado por unidade. O teclado tem 1,2 kg, então fica 10 + 6 = R$ 16,00 por unidade. Como no pedido são 2 teclados, o frete total ficou R$ 32,00.
## Como executar
Precisa do PHP 7.4 ou mais novo (eu rodei pelo terminal do Laragon).
Dentro da pasta do projeto:
```
php index.php
```
Para salvar a saída num arquivo:
```
php index.php > saida.txt
```
## Testes
1. Comprar mais do que tem no estoque (50 teclados com 8 disponíveis): `EstoqueInsuficienteException`.
2. Adicionar item com quantidade zero: `PedidoInvalidoException`.
3. Criar produto com preço negativo: `InvalidArgumentException`.
4. Processar pedido sem nenhum item: `PedidoInvalidoException`.
5. Adicionar produto num pedido já concluído: `PedidoInvalidoException`.
6. Pagar um pedido cancelado: `PagamentoException`.
Todos os erros são tratados com try/catch, então o sistema mostra a mensagem e continua rodando. A saída completa da execução está em `docs/saida-execucao.txt`.