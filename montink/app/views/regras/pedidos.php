<?php
require_once __DIR__ . '/../../../config/database.php';

$db = new Database();
$pdo = $db->conectar();
$stmt = $pdo->query("SELECT * FROM pedidos ORDER BY id DESC");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statuses = ['pendente', 'pago', 'cancelado', 'enviado', 'entregue'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h2>Painel de Pedidos</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Total</th>
                <th>Status</th>
                <th>Alterar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?= $pedido['id'] ?></td>
                    <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                    <td><?= $pedido['status'] ?></td>
                    <td>
                        <form action="atualizar_status.php" method="post" class="d-flex">
                            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                            <select name="status" class="form-select me-2">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status ?>" <?= $pedido['status'] === $status ? 'selected' : '' ?>>
                                        <?= ucfirst($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary">Atualizar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../../../index.php" class="btn btn-secondary mt-3">Voltar para a Loja</a>

</body>
</html>
