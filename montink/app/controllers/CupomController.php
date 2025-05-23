<?php
session_start();
require_once __DIR__ . '/../models/Cupom.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents("php: input"), true);

if (!isset($data['codigo'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Código do cupom ausente']);
    exit;
}

$db = new Database();
$pdo = $db->conectar();
$cupomModel = new Cupom($pdo);

$cupom = $cupomModel->buscarPorCodigo($data['codigo']);

$subtotal = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $subtotal += $item['quantidade'] * $item['preco'];
}

$desconto = $cupomModel->aplicarCupom($cupom, $subtotal);

if ($desconto > 0) {
    $_SESSION['cupom'] = [
        'codigo' => $cupom['codigo'],
        'desconto' => $desconto
    ];
    echo json_encode(['sucesso' => true, 'desconto' => $desconto]);
} else {
    http_response_code(400);
    echo json_encode(['erro' => 'Cupom inválido, vencido ou não aplicável ao valor do carrinho']);
}
