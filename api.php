<?php
header('Content-Type: application/json; charset=utf-8');

// =============================
// Configurações básicas
// =============================
$baseDir = __DIR__;
$logWebhook = $baseDir . '/webhook_log.json';
$logMensagens = $baseDir . '/mensagens.json';

// Garante que os arquivos existem
if (!file_exists($logWebhook)) {
    file_put_contents($logWebhook, json_encode([]));
}
if (!file_exists($logMensagens)) {
    file_put_contents($logMensagens, json_encode([]));
}

// =============================
// Lê a action
// =============================
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode([
        'error' => 'No action provided'
    ]);
    exit;
}

// =============================
// Roteamento
// =============================
switch ($action) {

    // =========================
    // TESTE DE API
    // =========================
    case 'ping':
        echo json_encode([
            'status' => 'ok',
            'message' => 'API online 🚀',
            'time' => date('Y-m-d H:i:s')
        ]);
        break;

    // =========================
    // CHECK WEBHOOK
    // =========================
    case 'check_webhook':
        echo json_encode([
            'status' => 'ok',
            'message' => 'Webhook ativo e pronto para receber dados'
        ]);
        break;

    // =========================
    // LISTAR WEBHOOKS RECEBIDOS
    // =========================
    case 'list_webhooks':
        $data = json_decode(file_get_contents($logWebhook), true);
        echo json_encode([
            'total' => count($data),
            'items' => $data
        ]);
        break;

    // =========================
    // LISTAR MENSAGENS
    // =========================
    case 'list_mensagens':
        $data = json_decode(file_get_contents($logMensagens), true);
        echo json_encode([
            'total' => count($data),
            'items' => $data
        ]);
        break;

    // =========================
    // ACTION INVÁLIDA
    // =========================
    default:
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid action',
            'received_action' => $action
        ]);
        break;
}

