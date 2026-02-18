<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$telefone = $data['telefone'] ?? '';
$mensagem = $data['mensagem'] ?? '';

if (!$telefone || !$mensagem) {
    echo json_encode([
        'success' => false,
        'error' => 'Telefone ou mensagem ausente'
    ]);
    exit;
}

// ⚠️ AJUSTE A PORTA E A INSTÂNCIA
$url = "http://192.64.83.190:8080/message/sendText/Controle_11";

$payload = [
    "number" => $telefone,
    "text"   => $mensagem
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "apikey: SUA_API_KEY_EVOLUTION"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'success' => $httpCode === 200,
    'http_code' => $httpCode,
    'response' => json_decode($response, true)
]);
