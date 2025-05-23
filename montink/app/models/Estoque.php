<?php
class Estoque
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function criar($produto_id, $quantidade, $variacao = null)
    {
        $sql = "INSERT INTO estoque (produto_id, quantidade, variacao) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$produto_id, $quantidade, $variacao]);
    }

    public function atualizar($produto_id, $quantidade, $variacao = null)
    {
        $sql = "UPDATE estoque SET quantidade = ? WHERE produto_id = ? AND variacao = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantidade, $produto_id, $variacao]);
    }

    public function buscarPorProduto($produto_id)
    {
        $sql = "SELECT * FROM estoque WHERE produto_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$produto_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function decrementar($produto_id, $quantidade, $variacao = null)
    {
        $sql = "UPDATE estoque SET quantidade = quantidade - ? WHERE produto_id = ? AND variacao = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantidade, $produto_id, $variacao]);
    }
}
