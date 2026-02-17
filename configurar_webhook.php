<?php

echo "=== CONFIGURADOR DE WEBHOOK EVOLUTION API ===\n\n";

// Ler configuração
$config = json_decode(file_get_contents('config.json'), true);

$url = $config['evolution_url'];
$apikey = $config['evolution_apikey'];
$instance = $config['evolution_instance'];

echo "Instância: $instance\n";
echo "Evolution URL: $url\n\n";

// Solicitar URL do webhook
echo "Digite a URL completa do webhook (ex: https://seudominio.com/webhook.php):\n";
echo "URL do webhook: ";
$webhookUrl = trim(fgets(STDIN));

if (empty($webhookUrl)) {
    die("URL do webhook não pode estar vazia!\n");
}

echo "\nConfigurando webhook com:\n";
echo "- URL: $webhookUrl\n";
echo "- Events: MESSAGES_UPSERT\n\n";

// Configurar webhook
$data = [
    "url" => $webhookUrl,
    "webhook_by_events" => false,
    "webhook_base64" => false,
    "events" => [
        "MESSAGES_UPSERT"
    ]
];

$ch = curl_init("$url/webhook/set/$instance");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apikey",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Resposta (HTTP $httpCode):\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

if ($httpCode == 200 || $httpCode == 201) {
    echo "✓ Webhook configurado com sucesso!\n\n";
    echo "IMPORTANTE:\n";
    echo "1. Certifique-se que a URL $webhookUrl está acessível publicamente\n";
    echo "2. Envie uma mensagem de teste para a instância\n";
    echo "3. Verifique o arquivo webhook_log.json para ver se os dados estão chegando\n";
} else {
    echo "✗ Erro ao configurar webhook\n";
}
