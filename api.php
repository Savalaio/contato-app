<?php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? null;
    if ($action === 'conversas') {
        obterConversas();
    } elseif ($action === 'check_webhook') {
        checkWebhook();
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
} elseif ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? null;
    
    if ($action === 'enviar') {
        enviarMensagem($data);
    } elseif ($action === 'setup_webhook') {
        setupWebhook($data);
    } else {
        echo json_encode(['error' => 'Invalid action', 'received_action' => $action]);
    }
} else {
    echo json_encode(['error' => 'Invalid method']);
}

function obterConversas() {
    $arquivo = 'mensagens.json';
    
    if (!file_exists($arquivo)) {
        echo json_encode([]);
        return;
    }
    
    $mensagens = json_decode(file_get_contents($arquivo), true);
    
    if (!is_array($mensagens) || empty($mensagens)) {
        echo json_encode([]);
        return;
    }
    
    $conversas = [];
    
    foreach ($mensagens as $msg) {
        $telefone = $msg['telefone'] ?? 'desconhecido';
        $fromMe = $msg['fromMe'] ?? false;
        
        if (!isset($conversas[$telefone])) {
            $conversas[$telefone] = [
                'nome' => $msg['nome'] ?? 'Cliente',
                'telefone' => $telefone,
                'mensagens' => []
            ];
        }
        
        $conversas[$telefone]['mensagens'][] = [
            'mensagem' => $msg['mensagem'],
            'hora' => $msg['hora'],
            'fromMe' => $fromMe,
            'tipo' => $msg['tipo'] ?? 'texto'
        ];
    }
    
    echo json_encode($conversas, JSON_UNESCAPED_UNICODE);
}

function enviarMensagem($data) {
    $telefone = $data['telefone'] ?? null;
    $mensagem = $data['mensagem'] ?? null;
    
    if (!$telefone || !$mensagem) {
        echo json_encode(['success' => false, 'error' => 'Telefone e mensagem são obrigatórios']);
        return;
    }
    
    $config = carregarConfig();
    
    if (!$config || !isset($config['evolution_url']) || !isset($config['evolution_apikey']) || !isset($config['evolution_instance'])) {
        echo json_encode(['success' => false, 'error' => 'Configuração do Evolution API não encontrada. Configure em config.json']);
        return;
    }
    
    $numero = preg_replace('/[^0-9]/', '', $telefone);
    if (!strpos($numero, '@')) {
        $numero = $numero . '@s.whatsapp.net';
    }
    
    $payload = [
        'number' => $numero,
        'text' => $mensagem
    ];
    
    $url = rtrim($config['evolution_url'], '/') . '/message/sendText/' . $config['evolution_instance'];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $config['evolution_apikey']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        salvarMensagemEnviada($telefone, $mensagem, $data['nome'] ?? 'Cliente');
        echo json_encode(['success' => true, 'response' => json_decode($response, true)]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao enviar mensagem', 'code' => $httpCode, 'response' => $response]);
    }
}

function salvarMensagemEnviada($telefone, $mensagem, $nome) {
    $arquivo = 'mensagens.json';
    $historico = file_exists($arquivo)
        ? json_decode(file_get_contents($arquivo), true)
        : [];
    
    if (!is_array($historico)) {
        $historico = [];
    }
    
    $historico[] = [
        'nome' => $nome,
        'telefone' => $telefone,
        'mensagem' => $mensagem,
        'tipo' => 'texto',
        'hora' => date('H:i'),
        'data' => date('Y-m-d'),
        'timestamp' => time(),
        'fromMe' => true
    ];
    
    file_put_contents($arquivo, json_encode($historico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function carregarConfig() {
    $arquivo = 'config.json';
    
    if (!file_exists($arquivo)) {
        return null;
    }
    
    return json_decode(file_get_contents($arquivo), true);
}

function checkWebhook() {
    $config = carregarConfig();
    
    if (!$config) {
        echo json_encode(['error' => 'Configuração não encontrada']);
        return;
    }
    
    $url = rtrim($config['evolution_url'], '/') . '/webhook/find/' . $config['evolution_instance'];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $config['evolution_apikey']]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo json_encode([
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ]);
}

function setupWebhook($data) {
    $config = carregarConfig();
    
    if (!$config) {
        echo json_encode(['success' => false, 'error' => 'Configuração não encontrada']);
        return;
    }
    
    $webhookUrl = $data['webhook_url'] ?? 'https://contato.realizador.com.br/webhook.php';
    
    $payload = [
        'url' => $webhookUrl,
        'webhook_by_events' => false,
        'webhook_base64' => false,
        'events' => ['MESSAGES_UPSERT']
    ];
    
    $url = rtrim($config['evolution_url'], '/') . '/webhook/set/' . $config['evolution_instance'];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $config['evolution_apikey']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo json_encode([
        'success' => ($httpCode === 200 || $httpCode === 201),
        'status' => $httpCode,
        'response' => json_decode($response, true)
    ]);
}
