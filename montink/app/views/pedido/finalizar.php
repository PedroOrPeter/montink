<?php
session_start();

$carrinho = $_SESSION['carrinho'] ?? [];
$subtotal = $_SESSION['subtotal'] ?? 0;
$frete = $_SESSION['frete'] ?? 0;
$endereco = $_SESSION['endereco'] ?? 'Não informado';

$total = $subtotal + $frete;

if (isset($_SESSION['cupom'])) {
    $total -= $_SESSION['cupom']['desconto'];
    if ($total < 0) $total = 0;
}

session_destroy();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Pedido Finalizado</title>
  <link href="https: cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

  <div class="alert alert-success">
    <h4>Pedido finalizado com sucesso!</h4>
    <p>Enviaremos para: <strong><?= htmlspecialchars($endereco) ?></strong></p>
    <p>Total pago: <strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></p>
  </div>

  <a href="../../../" class="btn btn-primary">Voltar à loja</a>

</body>
</html>