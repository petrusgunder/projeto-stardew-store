<?php
session_start();
require_once "conect.php";

$mensagem_erro = "";
$mensagem_alerta = "";

if (isset($_SESSION['mensagem_auth'])) {
    $mensagem_alerta = $_SESSION['mensagem_auth'];
    unset($_SESSION['mensagem_auth']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome          = trim($_POST['nome'] ?? '');
    $email         = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $cpf           = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $dt_nascimento = trim($_POST['dt-nascimento'] ?? '');
    $senha         = $_POST['senha'] ?? '';

    if (empty($nome) || !$email || empty($cpf) || empty($dt_nascimento) || empty($senha)) {
        $mensagem_erro = "Por favor, preencha todos os campos corretamente.";
    } elseif (strlen($cpf) !== 11) {
        $mensagem_erro = "O CPF deve conter exatamente 11 dígitos numéricos.";
    } elseif (strlen($senha) < 6) {
        $mensagem_erro = "A senha deve conter no mínimo 6 caracteres.";
    } else {
        $stmt_check = $conn->prepare("SELECT id, email, cpf FROM clientes WHERE email = ? OR cpf = ? LIMIT 1");
        $stmt_check->bind_param("ss", $email, $cpf);
        $stmt_check->execute();
        $res = $stmt_check->get_result();

        if ($res->num_rows > 0) {
            $user_exist = $res->fetch_assoc();
            if ($user_exist['email'] === $email) {
                $mensagem_erro = "O e-mail informado já está cadastrado.";
            } else {
                $mensagem_erro = "O CPF informado já está cadastrado.";
            }
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare("INSERT INTO clientes (nome, email, cpf, data_nascimento, senha_hash) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $nome, $email, $cpf, $dt_nascimento, $senha_hash);

            if ($stmt_insert->execute()) {
                $_SESSION['sucesso_cadastro'] = "Cadastro realizado com sucesso! Faça seu login para continuar.";
                header("Location: login.php");
                exit();
            } else {
                $mensagem_erro = "Erro interno ao salvar os dados. Tente novamente.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar-se - Stardew Store</title>
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
                    <input type="text" id="busca" name="busca" placeholder="buscar produto" autocomplete="off">
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
                <h1 id="login-titulo">Criar conta</h1>

                <?php if (!empty($mensagem_alerta)): ?>
                    <div style="background-color: #fff8e7; border: 1.5px solid #b85c37; padding: 10px; border-radius: 8px; color: #4a2e18; margin-bottom: 15px; text-align: center; font-size: 1vw;">
                        <?= htmlspecialchars($mensagem_alerta) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($mensagem_erro)): ?>
                    <div id="alerta-cadastro" style="display: block; color: red; margin: 10px 0; text-align: center; font-size: 1.1vw;">
                        <?= htmlspecialchars($mensagem_erro) ?>
                    </div>
                <?php endif; ?>

                <form action="cadastro.php" method="post">
                    <div class="campo">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                    </div>

                    <div class="campo">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="campo">
                        <label for="cpf">CPF:</label>
                        <input type="text" id="cpf" name="cpf" maxlength="11" placeholder="Somente números" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" required>
                    </div>

                    <div class="campo">
                        <label for="dt-nascimento">Data Nasc.:</label>
                        <input type="date" id="dt-nascimento" name="dt-nascimento" value="<?= htmlspecialchars($_POST['dt-nascimento'] ?? '') ?>" required>
                    </div>

                    <div class="campo">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="botao">
                        <button type="submit">Registrar</button>
                    </div>
                </form>

                <a id="opcoes" href="login.php">Já é cadastrado? clique aqui</a>
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