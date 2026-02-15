<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Atendimento</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <header>
        <h1>📲 Atendimento WhatsApp</h1>
    </header>

    <main id="chat"></main>

    <script>
    async function carregarMensagens() {
        try {
            // O timestamp (?_=) evita que o navegador use uma versão antiga (cache) do arquivo
            const res = await fetch('mensagens.json?_=' + Date.now());
            
            if (!res.ok) {
                console.log("Arquivo de mensagens ainda não foi criado.");
                return;
            }

            const textoBruto = await res.text();
            
            // Verifica se o arquivo está vazio ou contém apenas um array vazio
            if (!textoBruto || textoBruto.trim() === "" || textoBruto === "[]") {
                console.log("Histórico vazio. Aguardando mensagens...");
                return;
            }

            const dados = JSON.parse(textoBruto);
            const chat = document.getElementById('chat');
            
            // Limpa o chat antes de reconstruir para evitar duplicatas
            chat.innerHTML = '';

            dados.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'msg';

                div.innerHTML = `
                    <div class="nome">${msg.nome || 'Cliente'}</div>
                    <div class="texto">${msg.mensagem}</div>
                    <div class="hora">${msg.hora}</div>
                `;
                chat.appendChild(div);
            });

            // Mantém o scroll sempre no final das mensagens
            chat.scrollTop = chat.scrollHeight;

        } catch (e) {
            console.error("Erro ao carregar ou processar mensagens:", e);
        }
    }

    // Tenta carregar novas mensagens a cada 2 segundos
    setInterval(carregarMensagens, 2000);
    
    // Executa uma carga inicial assim que a página abre
    carregarMensagens();
    </script>

</body>
</html>
