<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Atendimento WhatsApp</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <div class="container">
        <aside class="sidebar">
            <header class="sidebar-header">
                <h2>📱 Atendimentos</h2>
                <div class="status-info">
                    <span id="total-conversas">0</span> conversas
                </div>
            </header>
            <div id="lista-conversas" class="conversas-list"></div>
        </aside>

        <main class="chat-area">
            <header class="chat-header">
                <div id="cliente-info">
                    <h3 id="cliente-nome">Selecione uma conversa</h3>
                    <span id="cliente-telefone"></span>
                </div>
                <div class="chat-actions">
                    <button id="btn-atualizar" onclick="carregarDados()">🔄 Atualizar</button>
                </div>
            </header>
            
            <div id="chat" class="chat-messages">
                <div class="empty-state">
                    <p>👈 Selecione uma conversa para começar</p>
                </div>
            </div>

            <footer class="chat-footer">
                <div class="input-area">
                    <input type="text" id="mensagem-input" placeholder="Digite sua mensagem..." disabled>
                    <button id="btn-enviar" onclick="enviarMensagem()" disabled>Enviar</button>
                </div>
            </footer>
        </main>
    </div>

    <script>
    let conversas = {};
    let conversaAtiva = null;

    async function carregarDados() {
        try {
            const res = await fetch('api.php?action=conversas&_=' + Date.now());
            
            if (!res.ok) {
                console.log("Erro ao carregar conversas");
                return;
            }

            const dados = await res.json();
            conversas = dados;
            
            atualizarListaConversas();
            
            if (conversaAtiva) {
                atualizarChatAtivo();
            }

        } catch (e) {
            console.error("Erro ao carregar dados:", e);
        }
    }

    function atualizarListaConversas() {
        const lista = document.getElementById('lista-conversas');
        lista.innerHTML = '';

        const telefones = Object.keys(conversas);
        document.getElementById('total-conversas').textContent = telefones.length;

        if (telefones.length === 0) {
            lista.innerHTML = '<div class="empty-conversas">Nenhuma conversa ainda</div>';
            return;
        }

        telefones.forEach(telefone => {
            const conversa = conversas[telefone];
            const ultimaMsg = conversa.mensagens[conversa.mensagens.length - 1];
            
            const div = document.createElement('div');
            div.className = 'conversa-item' + (telefone === conversaAtiva ? ' ativa' : '');
            div.onclick = () => selecionarConversa(telefone);
            
            div.innerHTML = `
                <div class="conversa-info">
                    <div class="conversa-nome">${conversa.nome}</div>
                    <div class="conversa-telefone">${telefone}</div>
                </div>
                <div class="conversa-preview">
                    <div class="ultima-msg">${ultimaMsg.mensagem.substring(0, 50)}...</div>
                    <div class="ultima-hora">${ultimaMsg.hora}</div>
                </div>
                <div class="conversa-badge">${conversa.mensagens.length}</div>
            `;
            
            lista.appendChild(div);
        });
    }

    function selecionarConversa(telefone) {
        conversaAtiva = telefone;
        document.getElementById('mensagem-input').disabled = false;
        document.getElementById('btn-enviar').disabled = false;
        
        atualizarChatAtivo();
        atualizarListaConversas();
    }

    function atualizarChatAtivo() {
        if (!conversaAtiva || !conversas[conversaAtiva]) return;

        const conversa = conversas[conversaAtiva];
        const chat = document.getElementById('chat');
        
        document.getElementById('cliente-nome').textContent = conversa.nome;
        document.getElementById('cliente-telefone').textContent = conversaAtiva;
        
        chat.innerHTML = '';
        
        conversa.mensagens.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'msg ' + (msg.fromMe ? 'msg-enviada' : 'msg-recebida');
            
            div.innerHTML = `
                <div class="msg-content">
                    <div class="texto">${msg.mensagem}</div>
                    <div class="hora">${msg.hora}</div>
                </div>
            `;
            chat.appendChild(div);
        });
        
        chat.scrollTop = chat.scrollHeight;
    }

    async function enviarMensagem() {
        if (!conversaAtiva) return;
        
        const input = document.getElementById('mensagem-input');
        const mensagem = input.value.trim();
        
        if (!mensagem) return;
        
        try {
            const res = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'enviar',
                    telefone: conversaAtiva,
                    mensagem: mensagem
                })
            });
            
            const resultado = await res.json();
            
            if (resultado.success) {
                input.value = '';
                carregarDados();
            } else {
                alert('Erro ao enviar: ' + resultado.error);
            }
            
        } catch (e) {
            console.error("Erro ao enviar mensagem:", e);
            alert('Erro ao enviar mensagem');
        }
    }

    document.getElementById('mensagem-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            enviarMensagem();
        }
    });

    setInterval(carregarDados, 3000);
    carregarDados();
    </script>

</body>

</html>