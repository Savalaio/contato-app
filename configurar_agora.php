<?php

// Configuração
$config = json_decode(file_get_contents('config.json'), true);

$url = $config['evolution_url'];
$apikey = $config['evolution_apikey'];
$instance = $config['evolution_instance'];
$webhookUrl = 'https://contato.realizador.com.br/webhook.php';

echo "=== CONFIGURANDO WEBHOOK ===\n\n";
echo "Evolution: $url\n";
echo "Instância: $instance\n";
echo "Webhook URL: $webhookUrl\n\n";

// Payload
$data = [
    "url" => $webhookUrl,
    "webhook_by_events" => false,
    "webhook_base64" => false,
    "events" => ["MESSAGES_UPSERT"]
];

// Configurar webhook
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

echo "Status HTTP: $httpCode\n";
echo "Resposta:\n";
echo $response;
echo "\n\n";

if ($httpCode == 200 || $httpCode == 201) {
    echo "✓ WEBHOOK CONFIGURADO COM SUCESSO!\n\n";
    echo "Próximo passo: Envie uma mensagem de teste para a instância Controle_11\n";
} else {
    echo "✗ ERRO AO CONFIGURAR WEBHOOK\n";
}
