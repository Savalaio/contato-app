<?php
header('Content-Type: application/json; charset=utf-8');

/* =========================
   FUNÇÕES BÁSICAS
========================= */

function carregarConfig() {
    $arquivo = __DIR__ . '/config.json';
    if (!file_exists($arquivo)) return false;
    return json_decode(file_get_contents($arquivo), true);
}

function resposta($array) {
    echo json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/* =========================
   ENVIAR MENSAGEM
========================= */

function enviarMensagem() {
    $config = carregarConfig();
    if (!$config) {
        resposta(['success' => false, 'error' => 'Config.json não encontrado']);
    }

    $telefone = $_POST['telefone'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';

    if (!$telefone || !$mensagem) {
        resposta(['success' => false, 'error' => 'Telefone ou mensagem vazios']);
    }

    // Garante formato correto
    if (!str_contains($telefone, '@')) {
        $telefone = preg_replace('/[^0-9]/', '', $telefone) . '@s.whatsapp.net';
    }

    $url = rtrim($config['evolution_url'], '/') .
           '/message/sendText/' . $config['evolution_instance'];

    $payload = [
        'number' => $telefone,
        'text'   => $mensagem
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . $config['evolution_apikey']
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    resposta([
        'success'  => ($httpCode === 200 || $httpCode === 201),
        'status'   => $httpCode,
        'response' => json_decode($response, true)
    ]);
}

/* =========================
   LISTAR CONVERSAS (JSON)
========================= */

function listarConversas() {
    $arquivo = __DIR__ . '/mensagens.json';
    if (!file_exists($arquivo)) {
        resposta(['success' => true, 'conversas' => []]);
    }

    $dados = json_decode(file_get_contents($arquivo), true);
    resposta(['success' => true, 'conversas' => $dados]);
}

/* =========================
   CHECK WEBHOOK (REAL)
========================= */

function checkWebhook() {
    $config = carregarConfig();
    if (!$config) {
        resposta(['success' => false, 'error' => 'Config inválida']);
    }

    $url = rtrim($config['evolution_url'], '/') .
           '/webhook/find/' . $config['evolution_instance'];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . $config['evolution_apikey']
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    resposta([
        'success'  => ($httpCode === 200),
        'status'   => $httpCode,
        'response' => json_decode($response, true)
    ]);
}

/* =========================
   ROTEADOR PRINCIPAL
========================= */

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'enviar':
        enviarMensagem();
        break;

    case 'listar':
        listarConversas();
        break;

    case 'check_webhook':
        checkWebhook();
        break;

    default:
        resposta([
            'success' => false,
            'error'   => 'Invalid action',
            'actions' => ['enviar', 'listar', 'check_webhook']
        ]);
}

