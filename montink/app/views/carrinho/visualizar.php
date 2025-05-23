<?php
session_start();

function formatarPreco($valor) {
  return 'R$ ' . number_format($valor, 2, ',', '.');
}

function limparSessao($chave) {
  if (!isset($_SESSION[$chave])) return null;
  $mensagem = $_SESSION[$chave];
  unset($_SESSION[$chave]);
  return $mensagem;
}

function html($texto) {
  return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_item'])) {
  $key = (int)$_POST['remover_item'];
  if (isset($_SESSION['carrinho'][$key])) {
    unset($_SESSION['carrinho'][$key]);
    $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
  }
  header("Location: visualizar.php");
  exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];
$frete = $_SESSION['frete'] ?? 0;
$cupom = $_SESSION['cupom']['desconto'] ?? 0;

$subtotal = array_reduce($carrinho, fn($total, $item) => $total + $item['preco'] * $item['quantidade'], 0);
$_SESSION['subtotal'] = $subtotal;
$total = max(0, $subtotal - $cupom) + $frete;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Carrinho</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h2 class="mb-4">Carrinho de Compras</h2>

  <?php if (empty($carrinho)): ?>
    <div class="alert alert-info">Seu carrinho está vazio.</div>
    <a href="../../../index.php" class="btn btn-secondary mt-3">Voltar para a Loja</a>
  <?php else: ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Preço</th>
          <th>Quantidade</th>
          <th>Total</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($carrinho as $key => $item): ?>
          <tr>
            <td><?= html($item['nome']) ?></td>
            <td><?= formatarPreco($item['preco']) ?></td>
            <td><?= $item['quantidade'] ?></td>
            <td><?= formatarPreco($item['preco'] * $item['quantidade']) ?></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="remover_item" value="<?= $key ?>">
                <button type="submit" class="btn btn-danger btn-sm">Remover</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <p><strong>Subtotal:</strong> <?= formatarPreco($subtotal) ?></p>

    <form action="calcular_frete.php" method="post" class="mb-3">
      <label for="cep" class="form-label">Calcular frete (CEP):</label>
      <div class="input-group">
        <input type="text" name="cep" id="cep" class="form-control" placeholder="00000-000" required>
        <button class="btn btn-outline-primary" type="submit">Calcular</button>
      </div>
    </form>

    <?php if ($erroCep = limparSessao('erro_cep')): ?>
      <div class="alert alert-danger"><?= html($erroCep) ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['endereco'])): ?>
      <p><strong>Endereço:</strong> <?= html($_SESSION['endereco']) ?></p>
    <?php endif; ?>

    <?php if ($frete > 0): ?>
      <p><strong>Frete:</strong> <?= formatarPreco($frete) ?></p>
    <?php endif; ?>

    <form action="aplicar_cupom.php" method="post" class="mb-3">
      <label for="cupom" class="form-label">Cupom de desconto:</label>
      <div class="input-group">
        <input type="text" name="cupom" id="cupom" class="form-control" placeholder="Digite o cupom" required>
        <button class="btn btn-outline-success" type="submit">Aplicar</button>
      </div>
    </form>

    <?php if ($erroCupom = limparSessao('erro_cupom')): ?>
      <div class="alert alert-danger"><?= html($erroCupom) ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cupom'])): ?>
      <div class="alert alert-success">
        Cupom <strong><?= html($_SESSION['cupom']['codigo']) ?></strong> aplicado! Desconto de <?= formatarPreco($cupom) ?>
      </div>
    <?php endif; ?>

    <h4>Total a pagar: <?= formatarPreco($total) ?></h4>

    <form action="../../../app/controllers/FinalizarPedidoController.php" method="post">
      <input type="hidden" name="finalizar_pedido" value="1">
      <input type="hidden" name="cep" value="<?= html($_SESSION['cep'] ?? '') ?>">
      <input type="hidden" name="endereco" value="<?= html($_SESSION['endereco'] ?? '') ?>">
      <button type="submit" class="btn btn-success mt-3">Finalizar Pedido</button>
      <a href="../../../index.php" class="btn btn-secondary mt-3">Voltar para a Loja</a>
    </form>
  <?php endif; ?>
</body>
</html>
