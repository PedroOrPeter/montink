<?php
require_once __DIR__ . '/../../config/Database.php';

class Pedido {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

public function criar($dados, $itens)
{
    $this->db->beginTransaction();

    $produtosJson = json_encode($itens);
    var_dump($produtosJson);  
    $sql = "INSERT INTO pedidos (produtos, subtotal, frete, total, endereco, status, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        $produtosJson,
        $dados['subtotal'],
        $dados['frete'],
        $dados['total'],
        $dados['endereco'],
        'pendente'
    ]);

    $pedidoId = $this->db->lastInsertId();

    $this->db->commit();

    return $pedidoId;
}
    public function listar() {
        $stmt = $this->db->query("SELECT * FROM pedidos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function deletar($id) {
        $this->db->prepare("DELETE FROM pedido_produto WHERE pedido_id = ?")->execute([$id]);
        return $this->db->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$id]);
    }
}