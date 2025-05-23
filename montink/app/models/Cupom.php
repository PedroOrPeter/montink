<?php
require_once __DIR__ . '/../config/database.php';

class Cupom
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function buscarPorCodigo($codigo)
    {
        $stmt = $this->db->prepare("SELECT * FROM cupons WHERE codigo = ? LIMIT 1");
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function aplicarCupom($cupom, $subtotal)
    {
        if (!$cupom) return 0;

        $hoje = date('Y-m-d');

        if ($subtotal < $cupom['minimo']) return 0;
        if ($hoje > $cupom['validade']) return 0;

        if ($cupom['tipo'] === 'fixo') {
            return $cupom['valor'];
        } elseif ($cupom['tipo'] === 'percentual') {
            return round(($cupom['valor'] / 100) * $subtotal, 2);
        }

        return 0;
    }
}
