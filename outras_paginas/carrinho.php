<?php
session_start();
require_once "conect.php";

// Exige autenticação de acordo com as regras de acesso do projeto
if (!isset($_SESSION['cliente_id'])) {
    $_SESSION['mensagem_auth'] = "Para acessar seu carrinho de compras, você precisa estar autenticado. Crie sua conta ou faça login para continuar suas compras com segurança.";
    $_SESSION['redirect_after_auth'] = "carrinho.php";
    header("Location: cadastro.php");
    exit();
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Processamento de Ações do Carrinho (Adicionar, Atualizar, Remover)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar') {
        $prod_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        $qtd     = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT) ?? 1;

        if ($prod_id && $qtd > 0) {
            $stmt = $conn->prepare("SELECT id, nome, preco, imagem FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1");
            $stmt->bind_param("i", $prod_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                if (isset($_SESSION['carrinho'][$prod_id])) {
                    $_SESSION['carrinho'][$prod_id] += $qtd;
                } else {
                    $_SESSION['carrinho'][$prod_id] = $qtd;
                }
            }
            $stmt->close();
        }
        header("Location: carrinho.php");
        exit();

    } elseif ($acao === 'atualizar') {
        $prod_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        $qtd     = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

        if ($prod_id) {
            if ($qtd > 0) {
                $_SESSION['carrinho'][$prod_id] = $qtd;
            } else {
                unset($_SESSION['carrinho'][$prod_id]);
            }
        }
        header("Location: carrinho.php");
        exit();

    } elseif ($acao === 'remover') {
        $prod_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
        if ($prod_id && isset($_SESSION['carrinho'][$prod_id])) {
            unset($_SESSION['carrinho'][$prod_id]);
        }
        header("Location: carrinho.php");
        exit();
    }
}

// Carregamento dos itens legítimos do carrinho direto do Banco de Dados
$itens_carrinho = [];
$subtotal_produtos = 0.0;

if (!empty($_SESSION['carrinho'])) {
    $ids = array_keys($_SESSION['carrinho']);
    $in_clause = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("SELECT id, nome, preco, imagem FROM produtos WHERE id IN ($in_clause) AND ativo = 1");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $quantidade = (int)$_SESSION['carrinho'][$id];
        $preco_unitario = (float)$row['preco'];
        $subtotal_item = $preco_unitario * $quantidade;

        $subtotal_produtos += $subtotal_item;

        $img_src = $row['imagem'];
        if (strpos($img_src, '../') !== 0) {
            $img_src = '../' . ltrim($img_src, '/');
        }

        $itens_carrinho[] = [
            'id'             => $id,
            'nome'           => $row['nome'],
            'preco_unitario' => $preco_unitario,
            'quantidade'     => $quantidade,
            'subtotal'       => $subtotal_item,
            'imagem'         => $img_src
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de compras - Stardew Store</title>
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
                <a href="login.php" title="Minha Conta (<?= htmlspecialchars($_SESSION['cliente_nome']) ?>)"><i class="bi bi-person-circle"></i></a>
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
                <a href="#">Vender meu bau</a>
            </div>
        </div>

        <hr id="barra-de-baixo">
    </header>

    <div class="chasi">
        <?php if (!empty($itens_carrinho)): ?>
            <?php foreach ($itens_carrinho as $item): ?>
                <div class="compra" data-preco="<?= $item['preco_unitario'] ?>">
                    <div class="esquerda">
                        <div class="img">
                            <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                        </div>
                        <div class="dados">
                            <h1><?= htmlspecialchars($item['nome']) ?></h1>
                            <p>Preço unitário: R$: <?= number_format($item['preco_unitario'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                    <div class="finalizar" style="display: flex; align-items: center; gap: 1vw;">
                        <form action="carrinho.php" method="post" style="display: inline-flex; align-items: center; gap: 0.5vw;">
                            <input type="hidden" name="acao" value="atualizar">
                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                            <label>Qtd: 
                                <input type="number" name="quantidade" class="qtd-prod" value="<?= $item['quantidade'] ?>" min="1" max="99" style="width: 60px;" onchange="this.form.submit()">
                            </label>
                        </form>

                        <p class="subtotal-item">R$: <?= number_format($item['subtotal'], 2, ',', '.') ?></p>

                        <form action="carrinho.php" method="post" style="display: inline;">
                            <input type="hidden" name="acao" value="remover">
                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                            <button type="submit" style="background-color: #ff6b6b; color: white; border: 1px solid black; padding: 4px 8px; border-radius: 5px; cursor: pointer;" title="Remover item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Painel de Fechamento -->
            <div class="finalizar" style="width: 100%;">
                <div class="cep">
                    <label>CEP:</label>
                    <input type="text" id="input-cep" placeholder="00000-000" maxlength="9">
                    <button type="button" id="btn-cep">Calcular Frete</button>
                </div>
                <p id="msg-cep" style="margin-left: 60vw; font-size: 1vw; color: red;"></p>

                <div class="cep" style="margin-top: 1vw;">
                    <label>Cupom:</label>
                    <input type="text" id="input-cupom" placeholder="Digite seu cupom">
                    <button type="button" id="btn-cupom">Aplicar</button>
                </div>
                <p id="msg-cupom" style="margin-left: 60vw; font-size: 1vw; color: green;"></p>

                <p class="frete" id="exibe-subtotal" style="font-size: 1.5vw; color: #4a2e18; margin-left: auto; margin-right: 3vw;">Subtotal Produtos: R$: <?= number_format($subtotal_produtos, 2, ',', '.') ?></p>
                <p class="frete" id="exibe-frete">Frete: R$: 0,00</p>
                <p class="total" id="exibe-total" data-subtotal="<?= $subtotal_produtos ?>">Total: R$: <?= number_format($subtotal_produtos, 2, ',', '.') ?></p>
            </div>

            <div class="botao" style="width: 100%;">
                <button type="button" onclick="alert('Compra finalizada com sucesso! Seus itens foram enviados para o inventário.')">Finalizar compra</button>
            </div>

        <?php else: ?>
            <div style="text-align: center; width: 100%; padding: 5vw 2vw;">
                <h1 style="font-size: 2.2vw; color: #4a2e18; margin-bottom: 1vw;">Seu carrinho está vazio!</h1>
                <p style="font-size: 1.2vw; margin-bottom: 2vw;">Adicione produtos incríveis para começar suas compras na fazenda.</p>
                <a href="../index.php" class="btn" style="display: inline-block; background-color: #f3aa65; padding: 0.8vw 1.5vw; text-decoration: none; color: black; border: 1.5px solid black; border-radius: 10px; font-size: 1.2vw;">Explorar Produtos</a>
            </div>
        <?php endif; ?>
    </div>

    <hr>

    <footer>
        <div class="menu-inferior">
            <div class="botoes-rodape">
                <a id="Contato" href="#">Contato</a>
                <a id="Sobre" href="#">Sobre nós</a>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        let valorFrete = 0;
        let descontoPorcentagem = 0;
        let temFreteGratisCupom = false;

        const cepsValidos = ['88495', '88780', '88490'];
        const totalEl = document.getElementById('exibe-total');
        if (!totalEl) return;

        const subtotalBase = parseFloat(totalEl.getAttribute('data-subtotal')) || 0;

        function recalcular() {
            let freteFinal = temFreteGratisCupom ? 0 : valorFrete;
            let valorDesconto = subtotalBase * descontoPorcentagem;
            let totalGeral = (subtotalBase - valorDesconto) + freteFinal;

            if (totalGeral < 0) totalGeral = 0;

            document.getElementById('exibe-frete').textContent = `Frete: R$: ${freteFinal.toFixed(2).replace('.', ',')}`;
            document.getElementById('exibe-total').textContent = `Total: R$: ${totalGeral.toFixed(2).replace('.', ',')}`;
        }

        const btnCep = document.getElementById('btn-cep');
        if (btnCep) {
            btnCep.addEventListener('click', () => {
                const cepVal = document.getElementById('input-cep').value.replace(/\D/g, '');
                const msgCep = document.getElementById('msg-cep');

                const eValido = cepsValidos.some(prefixo => cepVal.startsWith(prefixo));

                if (eValido) {
                    valorFrete = 15.50;
                    msgCep.style.color = 'green';
                    msgCep.textContent = 'CEP atende Garopaba, Imbituba ou Paulo Lopes!';
                } else {
                    valorFrete = 0;
                    msgCep.style.color = 'red';
                    msgCep.textContent = 'CEP indisponível. Aceitamos apenas Garopaba, Imbituba e Paulo Lopes.';
                }
                recalcular();
            });
        }

        const btnCupom = document.getElementById('btn-cupom');
        if (btnCupom) {
            btnCupom.addEventListener('click', () => {
                const cupom = document.getElementById('input-cupom').value.trim().toUpperCase();
                const msgCupom = document.getElementById('msg-cupom');

                descontoPorcentagem = 0;
                temFreteGratisCupom = false;

                if (cupom === 'DESCONTO10') {
                    descontoPorcentagem = 0.10;
                    msgCupom.style.color = 'green';
                    msgCupom.textContent = 'Cupom de 10% de desconto aplicado!';
                } else if (cupom === 'FRETEGRATIS') {
                    temFreteGratisCupom = true;
                    msgCupom.style.color = 'green';
                    msgCupom.textContent = 'Cupom de Frete Grátis aplicado!';
                } else {
                    msgCupom.style.color = 'red';
                    msgCupom.textContent = 'Cupom inválido.';
                }
                recalcular();
            });
        }
    });
    </script>
</body>
</html>