<?php
session_start();
require_once "conect.php";

$usuario_logado = isset($_SESSION['cliente_id']);

$mensagem_erro = "";
$mensagem_sucesso = "";

if (isset($_SESSION['sucesso_cadastro'])) {
    $mensagem_sucesso = $_SESSION['sucesso_cadastro'];
    unset($_SESSION['sucesso_cadastro']);
}

if (!$usuario_logado && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if (!$email || empty($senha)) {
        $mensagem_erro = "Por favor, informe um e-mail válido e a senha.";
    } else {
        $stmt = $conn->prepare("SELECT id, nome, email, senha_hash FROM clientes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $cliente = $result->fetch_assoc();

            if (password_verify($senha, $cliente['senha_hash'])) {
                session_regenerate_id(true);

                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                $_SESSION['cliente_email'] = $cliente['email'];

                $destinos_permitidos = ['carrinho.php', '../index.php'];
                $redirecionar_para = '../index.php';

                if (isset($_SESSION['redirect_after_auth']) && in_array($_SESSION['redirect_after_auth'], $destinos_permitidos)) {
                    $redirecionar_para = $_SESSION['redirect_after_auth'];
                    unset($_SESSION['redirect_after_auth']);
                }

                header("Location: " . $redirecionar_para);
                exit();
            } else {
                $mensagem_erro = "E-mail ou senha incorretos.";
            }
        } else {
            $mensagem_erro = "E-mail ou senha incorretos.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stardew Store</title>
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
                <a href="login.php"><i class="bi bi-person-circle"></i></a>
                <a href="carrinho.php"><i class="bi bi-cart"></i></a>
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

    <div class="corpo">
        <div class="login">
            <div class="formulario">
                <?php if ($usuario_logado): ?>
                    <h1 id="login-titulo">Você já está cadastrado e logado no sistema!</h1>
                    <p style="text-align: center; margin: 1.5vw 0; font-size: 1.2vw; color: #4a2e18;">
                        Sua conta já está autenticada (<?= htmlspecialchars($_SESSION['cliente_nome']) ?>). Você não precisa fazer login novamente.
                    </p>
                    <div class="botao" style="display: flex; flex-direction: column; gap: 10px; align-items: center;">
                        <a href="../index.php" class="btn" style="display: block; text-align: center; background-color: #f3aa65; padding: 0.8vw 1.5vw; text-decoration: none; color: black; border: 1.5px solid black; border-radius: 10px; font-size: 1.2vw;">Voltar para a Página Inicial</a>
                        <a href="carrinho.php" class="btn" style="display: block; text-align: center; background-color: #f7cca6; padding: 0.8vw 1.5vw; text-decoration: none; color: black; border: 1.5px solid black; border-radius: 10px; font-size: 1.2vw;">Ir para o Carrinho</a>
                        <a href="logout.php" style="color: red; font-size: 1vw; text-decoration: underline; margin-top: 5px;">Sair da Conta</a>
                    </div>
                <?php else: ?>
                    <h1 id="login-titulo">Faça login na sua conta</h1>

                    <?php if (!empty($mensagem_sucesso)): ?>
                        <div style="color: green; text-align: center; margin: 10px 0; font-size: 1.1vw;">
                            <?= htmlspecialchars($mensagem_sucesso) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($mensagem_erro)): ?>
                        <div id="erro-login" class="erro-mensagem" style="display: block; color: red; margin: 10px 0; text-align: center; font-size: 1.1vw;">
                            <?= htmlspecialchars($mensagem_erro) ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post">
                        <div class="campo">
                            <label for="email">E-mail: </label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="campo">
                            <label for="senha">Senha: </label>
                            <input type="password" id="senha" name="senha" required>
                        </div>

                        <div class="botao">
                            <button type="submit" id="btn-entrar">Entrar</button>
                        </div>
                    </form>

                    <a id="opcoes" href="cadastro.php">Não tem uma conta? Cadastre-se agora</a>
                <?php endif; ?>
            </div>
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
</body>
</html>