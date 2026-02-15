<?php
header('Content-Type: application/json');

$body = file_get_contents("php://input");
$data = json_decode($body, true);

file_put_contents('webhook_log.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

if (!isset($data['data'])) {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'no data']);
    exit;
}

$eventData = $data['data'];
$event = $data['event'] ?? '';

$nome = $eventData['pushName'] ?? $eventData['key']['remoteJid'] ?? 'Cliente';
$telefone = $eventData['key']['remoteJid'] ?? '';
$fromMe = $eventData['key']['fromMe'] ?? false;

if ($fromMe) {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'message from me']);
    exit;
}

$mensagem = null;
$tipoMensagem = 'texto';

if (isset($eventData['message'])) {
    $msg = $eventData['message'];
    
    if (isset($msg['conversation'])) {
        $mensagem = $msg['conversation'];
    } elseif (isset($msg['extendedTextMessage']['text'])) {
        $mensagem = $msg['extendedTextMessage']['text'];
    } elseif (isset($msg['imageMessage']['caption'])) {
        $mensagem = '📷 Imagem: ' . ($msg['imageMessage']['caption'] ?? 'sem legenda');
        $tipoMensagem = 'imagem';
    } elseif (isset($msg['videoMessage']['caption'])) {
        $mensagem = '🎥 Vídeo: ' . ($msg['videoMessage']['caption'] ?? 'sem legenda');
        $tipoMensagem = 'video';
    } elseif (isset($msg['audioMessage'])) {
        $mensagem = '🎵 Áudio';
        $tipoMensagem = 'audio';
    } elseif (isset($msg['documentMessage']['fileName'])) {
        $mensagem = '📄 Documento: ' . $msg['documentMessage']['fileName'];
        $tipoMensagem = 'documento';
    } elseif (isset($msg['locationMessage'])) {
        $mensagem = '📍 Localização compartilhada';
        $tipoMensagem = 'localizacao';
    } elseif (isset($msg['contactMessage'])) {
        $mensagem = '👤 Contato compartilhado';
        $tipoMensagem = 'contato';
    }
}

if (!$mensagem) {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'no message text']);
    exit;
}

$arquivo = 'mensagens.json';
$historico = file_exists($arquivo)
    ? json_decode(file_get_contents($arquivo), true)
    : [];

if (!is_array($historico)) {
    $historico = [];
}

$historico[] = [
    'nome' => htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'),
    'telefone' => htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'),
    'mensagem' => htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'),
    'tipo' => $tipoMensagem,
    'hora' => date('H:i'),
    'data' => date('Y-m-d'),
    'timestamp' => time(),
    'fromMe' => false
];

file_put_contents($arquivo, json_encode($historico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Message received']);