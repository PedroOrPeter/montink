<?php
session_start();
require_once __DIR__ . '/../models/Pedido.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php: input"), true);
if (!$data) {
    $data = $_POST;
}

if (!isset($data['cep'], $data['endereco'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos']);
    exit;
}

if (empty($_SESSION['carrinho'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Carrinho vazio']);
    exit;
}

function calcularResumo($carrinho)
{
    $subtotal = 0;
    foreach ($carrinho as $item) {
        $subtotal += $item['quantidade'] * $item['preco'];
    }

    if ($subtotal >= 52 && $subtotal <= 166.59) {
        $frete = 15;
    } elseif ($subtotal > 200) {
        $frete = 0;
    } else {
        $frete = 20;
    }

    $desconto = $_SESSION['cupom']['desconto'] ?? 0;

    return [
        'subtotal' => round($subtotal, 2),
        'frete' => $frete,
        'desconto' => $desconto,
        'total' => round($subtotal + $frete - $desconto, 2)
    ];
}

$resumo = calcularResumo($_SESSION['carrinho']);
$db = new Database();
$pdo = $db->conectar();
$pedidoModel = new Pedido($pdo);

$pedidoId = $pedidoModel->criar(
    array_merge($data, $resumo),
    $_SESSION['carrinho']
);
unset($_SESSION['carrinho'], $_SESSION['cupom']);
unset($_SESSION['erro_cupom']);
header('Location: ../../../montink/app/views/regras/pedidos.php');
exit;
