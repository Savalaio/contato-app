# 📱 Sistema de Atendimento WhatsApp

Sistema completo de atendimento via WhatsApp integrado com Evolution API.

## ✨ Funcionalidades

- ✅ Painel de atendimento estilo WhatsApp Web
- ✅ Lista de conversas ativas
- ✅ Visualização de mensagens recebidas
- ✅ Envio de mensagens direto pelo painel
- ✅ Suporte a múltiplos tipos de mensagem (texto, imagem, vídeo, áudio, documento, localização)
- ✅ Interface responsiva e moderna
- ✅ Atualização automática a cada 3 segundos
- ✅ Logs completos de todas as requisições

## 🚀 Instalação

### Opção 1: Deploy no Easypanel (RECOMENDADO)

1. **Prepare o repositório:**
   ```bash
   git add .
   git commit -m "Sistema de atendimento WhatsApp"
   git push
   ```

2. **No Easypanel:**
   - Clique em "Create" → "App"
   - Nome: whatsapp-atendimento
   - Source: GitHub (conecte seu repositório)
   - Build Type: Dockerfile
   - Port: 8080

3. **Configure o domínio:**
   - Adicione seu domínio personalizado
   - SSL é configurado automaticamente

4. **Configure as credenciais:**
   - Após deploy, edite `config.json` no container
   - Ou use variáveis de ambiente (veja `DEPLOY_EASYPANEL.txt`)

5. **Configure o webhook no Evolution API:**
   - URL: `https://seu-dominio.com/webhook.php`
   - Evento: `MESSAGES_UPSERT`

📖 **Guia completo:** Veja `DEPLOY_EASYPANEL.txt`

---

### Opção 2: Rodar Localmente

Edite o arquivo `config.json`:

```json
{
  "evolution_url": "https://sua-evolution-api.com",
  "evolution_apikey": "SUA_API_KEY_AQUI",
  "evolution_instance": "NOME_DA_INSTANCIA"
}
```

### 2. Configurar Webhook no Evolution API

Configure o webhook para apontar para: `https://seu-dominio.com/webhook.php`

Eventos necessários:
- `MESSAGES_UPSERT`

### 3. Rodar Localmente

```bash
docker-compose up -d
```

Acesse: http://localhost:8081

### 4. Deploy no Easypanel

1. Crie um novo App no Easypanel
2. Selecione "Docker" como source
3. Aponte para este repositório
4. Configure a porta 8080
5. Após deploy, atualize o webhook no Evolution API

## 📂 Estrutura de Arquivos

```
├── index.php           # Interface do painel de atendimento
├── webhook.php         # Recebe mensagens do Evolution API
├── api.php             # API para listar conversas e enviar mensagens
├── config.json         # Configurações do Evolution API
├── mensagens.json      # Armazena histórico de mensagens
├── webhook_log.json    # Log de todas as requisições
├── assets/
│   └── style.css       # Estilos da interface
├── Dockerfile          # Container para produção
└── docker-compose.yml  # Container para desenvolvimento
```

## 🔧 API Endpoints

### GET /api.php?action=conversas
Retorna todas as conversas agrupadas por telefone

### POST /api.php
Envia mensagem via Evolution API

Payload:
```json
{
  "action": "enviar",
  "telefone": "5511999999999@s.whatsapp.net",
  "mensagem": "Olá!"
}
```

### POST /webhook.php
Recebe mensagens do Evolution API (configurado no Evolution)

## 🎨 Interface

O sistema possui:

- **Sidebar esquerda**: Lista de conversas ativas com preview da última mensagem
- **Área principal**: Chat completo com histórico de mensagens
- **Campo de envio**: Input para digitar e enviar mensagens
- **Diferenciação visual**: Mensagens recebidas (cinza) e enviadas (verde)

## 🔒 Segurança

- Todas as mensagens são sanitizadas com `htmlspecialchars()`
- Suporte a autenticação por token (opcional)
- Logs completos para auditoria

## 📝 Tipos de Mensagem Suportados

- ✅ Texto simples
- ✅ Texto formatado
- ✅ Imagens (com legenda)
- ✅ Vídeos (com legenda)
- ✅ Áudios
- ✅ Documentos
- ✅ Localização
- ✅ Contatos

## 🐛 Troubleshooting

### Mensagens não aparecem
- Verifique se o webhook está configurado corretamente no Evolution API
- Consulte o arquivo `webhook_log.json` para ver as requisições recebidas

### Não consigo enviar mensagens
- Verifique as configurações em `config.json`
- Teste a conexão com o Evolution API manualmente
- Verifique se a instância está conectada

### Erro ao carregar conversas
- Verifique se o arquivo `mensagens.json` existe e é válido
- Verifique as permissões dos arquivos

## 📄 Licença

Este projeto é open source e está disponível sob a licença MIT.
