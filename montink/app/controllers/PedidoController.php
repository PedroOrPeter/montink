<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Pedido.php';

$db = new Database();
$pdo = $db->conectar();
$pedido = new Pedido($pdo);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            echo json_encode($pedido->buscarPorId($_GET['id']));
        } else {
            echo json_encode($pedido->listar());
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents("php: input"), true);
        $idPedido = $pedido->criar($dados);
        echo json_encode(["status" => "criado", "id" => $idPedido]);
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents("php: input"), true);
        if (isset($dados['id'], $dados['status'])) {
            $pedido->atualizarStatus($dados['id'], $dados['status']);
            echo json_encode(["status" => "atualizado"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $pedido->deletar($_GET['id']);
            echo json_encode(["status" => "removido"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["erro" => "Método não permitido"]);
}