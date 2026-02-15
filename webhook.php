<?php
$body = file_get_contents("php://input");
$data = json_decode($body, true);

$nome = $data['data']['pushName'] ?? 'Cliente';

$mensagem =
    $data['data']['message']['conversation']
    ?? $data['data']['message']['extendedTextMessage']['text']
    ?? null;

if (!$mensagem) {
    http_response_code(200);
    exit;
}

$arquivo = 'mensagens.json';
$historico = file_exists($arquivo)
    ? json_decode(file_get_contents($arquivo), true)
    : [];

$historico[] = [
    'nome' => $nome,
    'mensagem' => $mensagem,
    'hora' => date('H:i')
];

file_put_contents($arquivo, json_encode($historico, JSON_PRETTY_PRINT));

http_response_code(200);