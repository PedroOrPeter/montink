<?php
require_once __DIR__ . '/../../../config/database.php';

$db = new Database();
$pdo = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim($_POST['codigo']));
    $desconto = (float) $_POST['valor'];
    $valor_minimo = (float) $_POST['minimo'];
    $validade = $_POST['validade'];

    if (!$codigo || !$desconto || !$validade) {
        die('Dados inválidos.');
    }

    $stmt = $pdo->prepare("INSERT INTO cupons (codigo, desconto, minimo, validade) 
                            VALUES (?, ?, ?, ?)");
    $stmt->execute([$codigo, $desconto, $valor_minimo, $validade]);

    header('Location: cupons.php');
    exit;
}
