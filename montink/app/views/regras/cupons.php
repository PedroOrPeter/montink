<?php
require_once __DIR__ . '/../../../config/database.php';

$db = new Database();
$pdo = $db->conectar();
$cupons = $pdo->query("SELECT * FROM cupons ORDER BY validade DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel de Cupons</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h2>Gerenciar Cupons</h2>

  <form action="salvar_cupom.php" method="post" class="mb-4 row g-3">
    <div class="col-md-2">
      <input type="text" name="codigo" class="form-control" placeholder="Código" required>
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="valor" class="form-control" placeholder="Desconto (R$)" required>
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="minimo" class="form-control" placeholder="Valor Mínimo" required>
    </div>
    <div class="col-md-2">
      <input type="date" name="validade" class="form-control" required>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-success">Salvar Cupom</button>
    </div>
  </form>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Código</th>
        <th>Desconto (R$)</th>
        <th>Valor Mínimo</th>
        <th>Validade</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cupons as $cupom): ?>
        <tr>
          <td><?= $cupom['id'] ?></td>
          <td><?= htmlspecialchars($cupom['codigo']) ?></td>
          <td><?= number_format($cupom['desconto'], 2, ',', '.') ?></td>
          <td><?= number_format($cupom['minimo'], 2, ',', '.') ?></td>
          <td><?= date('d/m/Y', strtotime($cupom['validade'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <a href="../../../index.php" class="btn btn-secondary mt-3">Voltar para a Loja</a>

</body>
</html>
