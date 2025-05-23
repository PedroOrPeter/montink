<?php
session_start();

$cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');

if (strlen($cep) !== 8) {
    $_SESSION['erro_cep'] = 'CEP inválido.';
    header('Location: visualizar.php');
    exit;
}

$url = "https://viacep.com.br/ws/{$cep}/json/";
$response = file_get_contents($url);

if (!$response) {
    $_SESSION['erro_cep'] = 'Erro ao consultar o CEP.';
    header('Location: visualizar.php');
    exit;
}

$data = json_decode($response, true);

if (isset($data['erro'])) {
    $_SESSION['erro_cep'] = 'CEP não encontrado.';
    header('Location: visualizar.php');
    exit;
}

$subtotal = $_SESSION['subtotal'] ?? 0;
$frete = 20.00;

if ($subtotal >= 52.00 && $subtotal <= 166.59) {
    $frete = 15.00;
} elseif ($subtotal > 200.00) {
    $frete = 0.00;
}

$_SESSION['frete'] = $frete;
$_SESSION['cep'] = $cep;
$_SESSION['endereco'] = "{$data['logradouro']}, {$data['bairro']}, {$data['localidade']} - {$data['uf']}";

header('Location: visualizar.php');
exit;
