<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/seguranca.php';

header('Content-Type: application/json');

$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if ($auth !== 'Bearer ' . WEBHOOK_SECRET) { // O token está no arquivo de segurança em utils, só verificar
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php: input'), true);

if (!isset($data['pedido_id'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

$pedidoId = (int) $data['pedido_id'];
$status = $data['status'];

$permitidos = ['pago', 'cancelado', 'enviado', 'entregue'];

if (!in_array($status, $permitidos)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Status inválido']);
    exit;
}

$db = new Database();
$pdo = $db->conectar();
$stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
$stmt->execute([$status, $pedidoId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Status atualizado']);
} else {
    echo json_encode(['erro' => 'Pedido não encontrado']);
}
