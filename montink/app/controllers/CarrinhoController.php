<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php';

header('Content-Type: application/json');

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['resumo'])) {
            echo json_encode(calcularResumo($_SESSION['carrinho']));
        } else {
            echo json_encode($_SESSION['carrinho']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php: input"), true);

        if (!isset($data['produto_id'], $data['nome'], $data['quantidade'], $data['preco'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados incompletos.']);
            exit;
        }

        $key = $data['produto_id'] . '-' . ($data['variacao'] ?? '');
        $_SESSION['carrinho'][$key] = [
            'produto_id' => $data['produto_id'],
            'nome' => $data['nome'],
            'variacao' => $data['variacao'] ?? '',
            'quantidade' => $data['quantidade'],
            'preco' => $data['preco']
        ];

        echo json_encode(['sucesso' => true]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php: input"), true);
        $key = $data['produto_id'] . '-' . ($data['variacao'] ?? '');

        if (isset($_SESSION['carrinho'][$key])) {
            unset($_SESSION['carrinho'][$key]);
            echo json_encode(['sucesso' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Item não encontrado.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não suportado']);
        break;
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

    $desconto = 0;
    if (isset($_SESSION['cupom'])) {
        $desconto = $_SESSION['cupom']['desconto'] ?? 0;
    }

    return [
        'subtotal' => round($subtotal, 2),
        'frete' => $frete,
        'desconto' => $desconto,
        'total' => round($subtotal + $frete - $desconto, 2)
    ];
}
