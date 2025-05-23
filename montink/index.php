<?php
session_start();
require_once __DIR__ . '/admin/../config/database.php';

$db = new Database();
$pdo = $db->conectar();

$msg = '';
$produto = null;
$estoques = [];
$editando = false;

if (isset($_GET['editar'])) {
    $editando = true;
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM estoque WHERE produto_id = ?");
    $stmt->execute([$id]);
    $estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST['excluir_produto'])) {
    $produto_id = (int)$_POST['produto_id'];
    $pdo->prepare("DELETE FROM estoque WHERE produto_id = ?")->execute([$produto_id]);
    $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$produto_id]);
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = trim($_POST['nome'] ?? '');
    $preco = (float) ($_POST['preco'] ?? 0);
    $variacoes = $_POST['variacao'] ?? [];
    $estoquesForm = $_POST['estoque'] ?? [];
    $produto_id = (int) ($_POST['produto_id'] ?? 0);

    if ($nome && $preco > 0) {
        if ($produto_id) {
            $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, preco = ? WHERE id = ?");
            $stmt->execute([$nome, $preco, $produto_id]);

            $pdo->prepare("DELETE FROM estoque WHERE produto_id = ?")->execute([$produto_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco) VALUES (?, ?)");
            $stmt->execute([$nome, $preco]);
            $produto_id = $pdo->lastInsertId();
        }

        $temVariacao = false;
        foreach ($variacoes as $i => $var) {
            $var = trim($var);
            $qtd = (int) ($estoquesForm[$i] ?? 0);
            if ($var !== '') {
                $temVariacao = true;
                $stmt = $pdo->prepare("INSERT INTO estoque (produto_id, quantidade, variacao) VALUES (?, ?, ?)");
                $stmt->execute([$produto_id, $qtd, $var]);
            }
        }
        if (!$temVariacao) {
            $qtd = (int) ($_POST['estoque_unico'] ?? 0);
            $stmt = $pdo->prepare("INSERT INTO estoque (produto_id, quantidade) VALUES (?, ?)");
            $stmt->execute([$produto_id, $qtd]);
        }

        $msg = $editando ? "Produto atualizado com sucesso!" : "Produto cadastrado com sucesso!";

        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT * FROM estoque WHERE produto_id = ?");
        $stmt->execute([$produto_id]);
        $estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $editando = true;
    } else {
        $msg = "Preencha todos os campos obrigatórios.";
    }
}

if (isset($_POST['comprar'])) {
    $produto_id = (int)$_POST['produto_id'];
    $variacao = $_POST['compra_variacao'] ?? '';
    $qtd = (int)($_POST['compra_qtd'] ?? 1);

    if ($variacao !== '') {
        $stmt = $pdo->prepare("SELECT * FROM estoque WHERE produto_id = ? AND variacao = ?");
        $stmt->execute([$produto_id, $variacao]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM estoque WHERE produto_id = ? AND (variacao IS NULL OR variacao = '')");
        $stmt->execute([$produto_id]);
    }
    $estoque = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estoque && $estoque['quantidade'] >= $qtd) {
        $stmt = $pdo->prepare("UPDATE estoque SET quantidade = quantidade - ? WHERE id = ?");
        $stmt->execute([$qtd, $estoque['id']]);

        $_SESSION['carrinho'][] = [
            'produto_id' => $produto_id,
            'nome' => $_POST['produto_nome'],
            'preco' => (float)$_POST['produto_preco'],
            'variacao' => $variacao,
            'quantidade' => $qtd
        ];
        $msg = "Produto adicionado ao carrinho!";

        $stmt = $pdo->prepare("SELECT * FROM estoque WHERE produto_id = ?");
        $stmt->execute([$produto_id]);
        $estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $editando = true;
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $msg = "Estoque insuficiente!";
    }
}

if (isset($_POST['remover_carrinho'])) {
    $produto_id = $_POST['produto_id'];
    $variacao = $_POST['variacao'] ?? '';
    foreach ($_SESSION['carrinho'] as $key => $item) {
        if ($item['produto_id'] == $produto_id && $item['variacao'] == $variacao) {
            unset($_SESSION['carrinho'][$key]);
            break;
        }
    }
    $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
    header("Location: index.php");
    exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];
$total = 0;
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script>
    function addVariacao(nome = '', estoque = '') {
        const container = document.getElementById('variacoes');
        const div = document.createElement('div');
        div.className = 'row mb-2';
        div.innerHTML = `
            <div class="col">
                <input type="text" name="variacao[]" class="form-control" placeholder="Variação (ex: Tamanho M)" value="${nome}">
            </div>
            <div class="col">
                <input type="number" name="estoque[]" class="form-control" placeholder="Estoque" value="${estoque}">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger" onclick="this.parentNode.parentNode.remove()">Remover</button>
            </div>
        `;
        container.appendChild(div);
    }
    </script>
</head>
<body class="container mt-5">
<h2>Produtos Cadastrados</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Preço</th>
            <th>Variações/Estoque</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($produtos as $p):
        $stmt2 = $pdo->prepare("SELECT * FROM estoque WHERE produto_id = ?");
        $stmt2->execute([$p['id']]);
        $ests = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    ?>
        <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
            <td>
                <?php
                if ($ests) {
                    foreach ($ests as $e) {
                        echo htmlspecialchars($e['variacao'] ?: 'Sem variação') . ': ' . (int)$e['quantidade'] . '<br>';
                    }
                }
                ?>
            </td>
            <td class="d-flex gap-1">
                <a href="?editar=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                    <input type="hidden" name="excluir_produto" value="1">
                    <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2><?= $editando ? 'Editar Produto' : 'Cadastrar Produto' ?></h2>
<?php if (!empty($msg)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<form method="post" class="mb-4">
    <?php if ($editando): ?>
        <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label">Nome do Produto</label>
        <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($produto['nome'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Preço (R$)</label>
        <input type="number" step="0.01" name="preco" class="form-control" required value="<?= htmlspecialchars($produto['preco'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Variações <small>(opcional)</small></label>
        <div id="variacoes"></div>
        <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addVariacao()">Adicionar Variação</button>
    </div>
    <div class="mb-3">
        <label class="form-label">Estoque <small>(preencha aqui se não houver variações)</small></label>
        <input type="number" name="estoque_unico" class="form-control" value="<?php
            if ($editando && $estoques && count($estoques) === 1 && empty($estoques[0]['variacao'])) {
                echo (int)$estoques[0]['quantidade'];
            }
        ?>">
    </div>
        <div class="mb-3 d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-success"><?= $editando ? 'Atualizar' : 'Cadastrar' ?></button>
            <a href="app/views/carrinho/visualizar.php" class="btn btn-info">Ver Carrinho</a>
            <?php if ($editando): ?>
                <a href="index.php" class="btn btn-warning">Cadastrar novo produto</a>
            <?php endif; ?>
            <a href="app/views/regras/pedidos.php" class="btn btn-secondary">Ver Pedidos</a>
            <a href="app/views/regras/cupons.php" class="btn btn-secondary" style="background-color: #800080; border-color: #800080; color: #fff;">Ver Cupons</a>

        </div>
</form>

<?php if ($editando && $produto): ?>
    <h4>Comprar Produto</h4>
    <form method="post" class="mb-4 row g-2 align-items-end">
        <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
        <input type="hidden" name="produto_nome" value="<?= htmlspecialchars($produto['nome']) ?>">
        <input type="hidden" name="produto_preco" value="<?= htmlspecialchars($produto['preco']) ?>">
        <?php if ($estoques && count($estoques) > 1): ?>
            <div class="col-auto">
                <label class="form-label">Variação</label>
                <select name="compra_variacao" class="form-select">
                    <?php foreach ($estoques as $e): ?>
                        <option value="<?= htmlspecialchars($e['variacao']) ?>">
                            <?= htmlspecialchars($e['variacao']) ?> (<?= $e['quantidade'] ?> disponíveis)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-auto">
            <label class="form-label">Quantidade</label>
            <input type="number" name="compra_qtd" class="form-control" min="1" value="1">
        </div>
        <div class="col-auto">
            <button type="submit" name="comprar" class="btn btn-primary">Comprar</button>
        </div>
    </form>
<?php endif; ?>

<script>

<?php if ($editando && $estoques): ?>
    <?php foreach ($estoques as $e): ?>
        <?php if (!empty($e['variacao'])): ?>
            addVariacao("<?= htmlspecialchars($e['variacao']) ?>", "<?= (int)$e['quantidade'] ?>");
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</script>
</body>
</html>