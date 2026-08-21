# 📱 JR Tech Automation - WhatsAPI

Coleção de APIs para integração externa com o ecossistema JR Tech Automation. Esta API permite o envio automatizado de mensagens, mídias e verificação de números de WhatsApp de forma simples via requisições HTTP POST.

---

## 🔒 Autenticação e Configuração Base

Todas as requisições devem ser feitas para a URL base e exigem autenticação via Header.

*   **Base URL:** `https://whatsappi.jrtechautomation.com.br/api`
*   **Method:** `POST`
*   **Header Obrigatório:**
    *   `apikey`: `SEU_TOKEN_PRIVADO_AQUI`
    *   `Content-Type`: `application/json`

---

## 🚀 Endpoints Disponíveis

### 1. Enviar Mensagem de Texto
Envia uma mensagem de texto simples para um número de WhatsApp.

*   **Endpoint:** `?action=send_message`
*   **Body (JSON):**
```json
{
    "instance": "ID_DA_SUA_INSTANCIA",
    "type": "text",
    "payload": {
        "number": "558182596095",
        "text": "Olá! Mensagem enviada via API JR Tech."
    }
}

```

### 2. Enviar Imagem

Envia uma imagem a partir de uma URL pública, acompanhada de uma legenda.

* **Endpoint:** `?action=send_message`
* **Body (JSON):**

```json
{
    "instance": "ID_DA_SUA_INSTANCIA",
    "type": "image",
    "payload": {
        "number": "558182596095",
        "url": "[https://url-publica-da-imagem.png](https://url-publica-da-imagem.png)",
        "caption": "Legenda da Imagem"
    }
}

```

### 3. Enviar Documento (PDF, etc)

Envia um arquivo ou documento a partir de uma URL pública.

* **Endpoint:** `?action=send_message`
* **Body (JSON):**

```json
{
    "instance": "ID_DA_SUA_INSTANCIA",
    "type": "document",
    "payload": {
        "number": "558182596095",
        "url": "https://url-publica-do-arquivo/relatorio.pdf",
        "fileName": "relatorio-1.pdf"
    }
}

```

### 4. Enviar Áudio

Envia um arquivo de áudio para o número de destino.

* **Endpoint:** `?action=send_message`
* **Body (JSON):**

```json
{
    "instance": "ID_DA_SUA_INSTANCIA",
    "type": "audio",
    "payload": {
        "number": "5581984201425",
        "url": "[https://link-do-audio.mp3](https://link-do-audio.mp3)"
    }
}

```

---

### 5. Verificar Números de WhatsApp

Verifica se uma lista de números possui uma conta de WhatsApp ativa registrada.

* **Endpoint:** `?action=check_numbers`
* **Body (JSON):**

```json
{
    "instance": "ID_DA_SUA_INSTANCIA",
    "numbers": ["558182596095"]
}

```

---

## 🐳 Deploy no Easypanel (Docker)

Para garantir que a sessão do WhatsApp não seja perdida a cada deploy (o que obrigaria a ler o QR Code novamente), certifique-se de configurar um **Volume** no painel do Easypanel:

1. Acesse o projeto no Easypanel.
2. Na aba **Advanced** (ou Volumes), crie um novo volume apontando para a pasta onde sua aplicação salva as sessões (ex: `/app/sessions` ou `/app/.wwebjs_auth`).
3. Configure suas variáveis de ambiente na aba **Environment** (ex: Porta, Tokens).
4. Faça o **Deploy**.

---

*Desenvolvido por JR Tech Automation*
