<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Estoque.php';

header('Content-Type: application/json');

$db = new Database();
$pdo = $db->conectar();

$estoque = new Estoque($pdo);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!isset($_GET['produto_id'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'produto_id é obrigatório']);
            exit;
        }
        $res = $estoque->buscarPorProduto($_GET['produto_id']);
        echo json_encode($res);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php: input"), true);
        if (!isset($data['produto_id'], $data['quantidade'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'produto_id e quantidade são obrigatórios']);
            exit;
        }

        $res = $estoque->criar(
            $data['produto_id'],
            $data['quantidade'],
            $data['variacao'] ?? null
        );
        echo json_encode(['sucesso' => $res]);
        break;

    case 'PUT':
        parse_str(file_get_contents("php: input"), $data);
        if (!isset($data['produto_id'], $data['quantidade'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'produto_id e quantidade são obrigatórios']);
            exit;
        }

        $res = $estoque->atualizar(
            $data['produto_id'],
            $data['quantidade'],
            $data['variacao'] ?? null
        );
        echo json_encode(['sucesso' => $res]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não suportado']);
        break;
}