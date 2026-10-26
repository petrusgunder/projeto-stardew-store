<?php
session_start();
require_once "outras_paginas/conect.php";

// Consulta os produtos ativos ordenados pelos mais recentes
$sql = "SELECT id, nome, preco, imagem, estrelas FROM produtos WHERE ativo = 1 ORDER BY id DESC";
$result = $conn->query($sql);

$produtos_busca = [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial - Stardew Store</title>
    <link rel="icon" href="imagens/header/logo-title.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="menu-superior">
            <div class="imagem-header">
                <a href="index.php"><img src="imagens/header/Logo_site.png" alt="Stardew Store Logo"></a>
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
                <a href="outras_paginas/login.php" title="<?= isset($_SESSION['cliente_nome']) ? 'Minha Conta (' . htmlspecialchars($_SESSION['cliente_nome']) . ')' : 'Fazer Login' ?>">
                    <i class="bi bi-person-circle"></i>
                </a>
                <a href="outras_paginas/carrinho.php" title="Carrinho de Compras"><i class="bi bi-cart"></i></a>
            </div>
        </div>
        <hr id="barra-de-cima">

        <div class="menu-inferior">
            <div class="botoes">
                <a href="index.php">Ofertas</a>
                <a href="outras_paginas/sementes.html">Sementes</a>
                <a href="#">Animais</a>
                <a href="#">Roupas</a>
                <a href="#">Objetos</a>
                <a href="outras_paginas/vender_bau.php">Vender meu bau</a>
            </div>
        </div>

        <hr id="barra-de-baixo">
    </header>

    <div class="promos">
        <div class="promo-pai"><img src="imagens/promocoes/promo.png" alt="Promoção Principal"></div>
    
        <div class="promo-filho">
            <div class="card-promo">
                <a href="#"><img src="imagens/promocoes/galinha_azul.webp" alt="Galinha Azul"></a>
                <img class="selo-estrela" src="imagens/promocoes/estrela.png" alt="Estrela">
            </div>

            <div class="card-promo">
                <a href="#"><img src="imagens/promocoes/chirivia.png" alt="Chirivia"></a>
                <img class="selo-estrela" src="imagens/promocoes/estrela.png" alt="Estrela">
            </div>

            <div class="card-promo">
                <a href="#"><img src="imagens/promocoes/colar.png" alt="Colar"></a>
            </div>

            <div class="card-promo">
                <a href="#"><img src="imagens/promocoes/galinha.png" alt="Galinha"></a>
                <img class="selo-banner" src="imagens/promocoes/promo-buner.png" alt="Promoção">
            </div>
        </div>
    </div>
    
    <hr>

    <div class="outros-produtos">
        <h1>Produtos para você</h1>

        <div class="produtos">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($produto = $result->fetch_assoc()): ?>
                    <?php
                    $link_dinamico = "outras_paginas/produtos.php?id=" . (int)$produto['id'];
                    $preco_formatado = "R$ " . number_format($produto['preco'], 2, ',', '.');
                    $qtd_estrelas = (int)$produto['estrelas'];

                    $img_src = (strpos($produto['imagem'], '../') === 0) ? substr($produto['imagem'], 3) : $produto['imagem'];

                    $produtos_busca[] = [
                        'nome' => $produto['nome'],
                        'url'  => $link_dinamico,
                        'img'  => $img_src
                    ];
                    ?>
                    <div class="um-produto">
                        <a href="<?= htmlspecialchars($link_dinamico) ?>">
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                        </a>
                        <a class="nome-link" href="<?= htmlspecialchars($link_dinamico) ?>">
                            <?= htmlspecialchars($produto['nome']) ?> - <?= $preco_formatado ?>
                        </a>
                        <div class="estrelass">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $qtd_estrelas): ?>
                                    <img src="imagens/promocoes/estrela.png" alt="Estrela">
                                <?php else: ?>
                                    <img src="imagens/promocoes/estrela_apagada.png" alt="Estrela Apagada">
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; font-size: 1.5vw; padding: 20px;">
                    Nenhum produto cadastrado no momento.
                </p>
            <?php endif; ?>
        </div>
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
        const produtos = <?= json_encode($produtos_busca, JSON_UNESCAPED_UNICODE) ?>;

        const inputBusca = document.getElementById('busca');
        const containerResultados = document.getElementById('resultados');

        if (inputBusca && containerResultados) {
            inputBusca.addEventListener('input', (e) => {
              const termo = e.target.value.toLowerCase().trim();
              containerResultados.innerHTML = '';

              if (!termo) return;

              const filtrados = produtos.filter(produto => 
                produto.nome.toLowerCase().includes(termo)
              );

              filtrados.forEach(produto => {
                const link = document.createElement('a');
                link.href = produto.url;
                link.className = 'item-produto';
                link.innerHTML = `
                  <img src="${produto.img}" alt="${produto.nome}">
                  <span>${produto.nome}</span>
                `;
                containerResultados.appendChild(link);
              });
            });
        }
    </script>
</body>
</html>