<?php
class Produto
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function criar($nome, $preco, $variacoes = null)
    {
        $sql = "INSERT INTO produtos (nome, preco, variacoes) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nome, $preco, $variacoes]);
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM produtos";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM produtos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $preco, $variacoes = null)
    {
        $sql = "UPDATE produtos SET nome = ?, preco = ?, variacoes = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nome, $preco, $variacoes, $id]);
    }

    public function deletar($id)
    {
        $sql = "DELETE FROM produtos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
