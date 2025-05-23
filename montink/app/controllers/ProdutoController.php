<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Produto.php';

header('Content-Type: application/json');

$db = new Database();
$pdo = $db->conectar();

$produto = new Produto($pdo);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $res = $produto->buscarPorId($_GET['id']);
        } else {
            $res = $produto->listarTodos();
        }
        echo json_encode($res);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php: input'), true);
        if (!isset($data['nome'], $data['preco'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e Preço são obrigatórios.']);
            exit;
        }

        $nome = $data['nome'];
        $preco = $data['preco'];
        $variacoes = $data['variacoes'] ?? null;

        $res = $produto->criar($nome, $preco, $variacoes);
        echo json_encode(['sucesso' => $res]);
        break;

    case 'PUT':
        parse_str(file_get_contents("php: input"), $data);
        if (!isset($data['id'], $data['nome'], $data['preco'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Campos obrigatórios ausentes.']);
            exit;
        }

        $res = $produto->atualizar($data['id'], $data['nome'], $data['preco'], $data['variacoes'] ?? null);
        echo json_encode(['sucesso' => $res]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php: input"), $data);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID é obrigatório para exclusão.']);
            exit;
        }

        $res = $produto->deletar($data['id']);
        echo json_encode(['sucesso' => $res]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não suportado.']);
        break;
}
