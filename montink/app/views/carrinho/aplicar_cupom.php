<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim($_POST['cupom'] ?? ''));
    $subtotal = $_SESSION['subtotal'] ?? 0;

    $db = new Database();
    $pdo = $db->conectar();

    $stmt = $pdo->prepare("SELECT * FROM cupons WHERE codigo = ? AND validade >= CURDATE()");
    $stmt->execute([$codigo]);
    $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupom) {
        $_SESSION['erro_cupom'] = 'Cupom inválido ou expirado.';
    } elseif ($subtotal < $cupom['minimo']) {
        $_SESSION['erro_cupom'] = 'Valor mínimo para usar este cupom: R$ ' . number_format($cupom['minimo'], 2, ',', '.');
    } else {
        $_SESSION['cupom'] = [
            'codigo' => $cupom['codigo'],
            'desconto' => $cupom['desconto']
        ];
    }
}

header('Location: visualizar.php');
exit;