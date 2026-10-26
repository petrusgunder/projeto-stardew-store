<?php
session_start();
require_once "conect.php";

$id_produto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$produto = null;

if ($id_produto) {
    $stmt = $conn->prepare("SELECT id, nome, preco, imagem, estrelas, COALESCE(descricao, '') AS descricao FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1");
    $stmt->bind_param("i", $id_produto);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $produto = $res->fetch_assoc();
    }
    $stmt->close();
}

$mensagem_sucesso = "";
if (isset($_GET['status']) && $_GET['status'] === 'adicionado') {
    $mensagem_sucesso = "Produto adicionado ao carrinho com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $produto ? htmlspecialchars($produto['nome']) : 'Produto Não Encontrado' ?> - Stardew Store</title>
    <link rel="icon" href="../imagens/header/logo-title.png">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="menu-superior">
            <div class="imagem-header">
                <a href="../index.php"><img src="../imagens/header/Logo_site.png" alt="Stardew Store Logo"></a>
            </div>

            <div class="barra-pesquisa-header">
                <form onsubmit="event.preventDefault();">
                    <input type="text" id="busca" placeholder="buscar produto" autocomplete="off">
                    <label for="busca">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </label>
                </form>
                <div id="resultados" class="autocomplete-box"></div>
            </div>

            <div class="icons-header">
                <a href="login.php" title="<?= isset($_SESSION['cliente_nome']) ? 'Minha Conta (' . htmlspecialchars($_SESSION['cliente_nome']) . ')' : 'Fazer Login' ?>">
                    <i class="bi bi-person-circle"></i>
                </a>
                <a href="carrinho.php" title="Carrinho de Compras"><i class="bi bi-cart"></i></a>
            </div>
        </div>

        <hr id="barra-de-cima">

        <div class="menu-inferior">
            <div class="botoes">
                <a href="../index.php">Ofertas</a>
                <a href="sementes.html">Sementes</a>
                <a href="#">Animais</a>
                <a href="#">Roupas</a>
                <a href="#">Objetos</a>
                <a href="vender_bau.php">Vender meu bau</a>
            </div>
        </div>

        <hr id="barra-de-baixo">
    </header>

    <?php if ($produto): ?>
        <?php
        $img_src = $produto['imagem'];
        if (strpos($img_src, '../') !== 0) {
            $img_src = '../' . ltrim($img_src, '/');
        }
        $preco_formatado = "R$: " . number_format($produto['preco'], 2, ',', '.');
        $preco_pix = "R$: " . number_format($produto['preco'] * 0.95, 2, ',', '.');
        $preco_parcela = "R$: " . number_format($produto['preco'] / 3, 2, ',', '.');
        $qtd_estrelas = (int)$produto['estrelas'];
        ?>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div style="background-color: #d4edda; color: #155724; border: 1.5px solid #c3e6cb; padding: 12px; text-align: center; font-size: 1.2vw; margin: 1vw 2vw; border-radius: 8px;">
                <?= htmlspecialchars($mensagem_sucesso) ?>
                <a href="carrinho.php" style="margin-left: 15px; color: #155724; font-weight: bold; text-decoration: underline;">Ir para o Carrinho</a>
            </div>
        <?php endif; ?>

        <div class="produto-unico">
            <div class="coluna-esquerda">
                <div class="conteudo-principal">
                    <div class="img-produto">
                        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                    </div>
                    <div class="textos">
                        <p id="titulo"><?= htmlspecialchars($produto['nome']) ?></p>
                        <br>
                        <p id="subtitulo">Preço unitário: <?= $preco_formatado ?></p>
                        <p id="pix"><?= $preco_pix ?> no PIX (5% de desconto)</p>
                        <p id="parcela">3x de <?= $preco_parcela ?> sem juros</p>
                        <p id="avaliacao">Avaliação do produto:</p>
                        <div class="estrelass" id="primeira">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $qtd_estrelas): ?>
                                    <img src="../imagens/promocoes/estrela.png" alt="Estrela">
                                <?php else: ?>
                                    <img src="../imagens/promocoes/estrela_apagada.png" alt="Estrela Apagada">
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="descricao">
                    <h1>Descrição do Produto</h1>
                    <p><?= !empty($produto['descricao']) ? nl2br(htmlspecialchars($produto['descricao'])) : 'Produto de altíssima qualidade direto da Vila dos Tucanos. Garantia de procedência Stardew Store.' ?></p>
                </div>
            </div>

            <div id="compra">
                <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                <p>Vendido por: <a id="vendedor" href="#">Pierre's General Store</a></p>
                <p id="entrega">Entrega garantida pela Stardew Express</p>
                <p id="frete">Frete Grátis em compras acima de R$ 100</p>
                <p id="pagamento">Pagamento seguro via PIX, Cartão ou Boleto</p>

                <form class="formulario" action="carrinho.php" method="post">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" value="<?= (int)$produto['id'] ?>">

                    <div style="margin: 1.5vw 0; text-align: center;">
                        <label for="quantidade">Quantidade:</label>
                        <input type="number" id="qinput" name="quantidade" value="1" min="1" max="99" style="width: 70px; text-align: center;" required>
                    </div>

                    <div class="botao" style="display: flex; flex-direction: column; gap: 10px;">
                        <button type="submit" id="add-car">Adicionar ao Carrinho</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 5vw 2vw;">
            <h1 style="font-size: 2.5vw; color: #b85c37;">Produto não encontrado!</h1>
            <p style="font-size: 1.2vw; margin: 1.5vw 0;">O produto que você procurou não existe ou foi removido do nosso catálogo.</p>
            <a href="../index.php" class="btn" style="display: inline-block; background-color: #f3aa65; padding: 0.8vw 1.5vw; text-decoration: none; color: black; border: 1.5px solid black; border-radius: 10px; font-size: 1.2vw;">Voltar para as Ofertas</a>
        </div>
    <?php endif; ?>

    <hr>

    <footer>
        <div class="menu-inferior">
            <div class="botoes-rodape">
                <a id="Contato" href="#">Contato</a>
                <a id="Sobre" href="#">Sobre nós</a>
            </div>
        </div>
    </footer>
</body>
</html>