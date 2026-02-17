<?php

$config = json_decode(file_get_contents('config.json'), true);

$url = $config['evolution_url'];
$apikey = $config['evolution_apikey'];
$instance = $config['evolution_instance'];

echo "=== TESTANDO CONEXÃO COM EVOLUTION API ===\n\n";
echo "URL: $url\n";
echo "Instance: $instance\n\n";

// 1. Verificar webhook atual
echo "1. Verificando webhook configurado...\n";
$ch = curl_init("$url/webhook/find/$instance");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $apikey"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: $httpCode\n";
echo "Resposta:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

// 2. Verificar status da instância
echo "2. Verificando status da instância...\n";
$ch = curl_init("$url/instance/connectionState/$instance");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $apikey"]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: $httpCode\n";
echo "Resposta:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

echo "=== FIM DO TESTE ===\n";
