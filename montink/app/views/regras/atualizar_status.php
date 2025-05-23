<?php
require_once __DIR__ . '/../../../config/database.php';

$db = new Database();
$pdo = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $status = $_POST['status'];

    $permitidos = ['pendente', 'pago', 'cancelado', 'enviado', 'entregue'];
    if (!in_array($status, $permitidos)) {
        die('Status inválido');
    }

    if ($status === 'cancelado') {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    header('Location: pedidos.php');
    exit;
}