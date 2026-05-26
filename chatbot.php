<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="glass-card chat-container">
        <h2 class="text-center mb-4">AI Farming Assistant</h2>
        
        <div id="chat-box" class="chat-box">
            <div class="message bot-message">
                Hello! I am your AI farming assistant. How can I help you today?
            </div>
        </div>
        
        <div class="chat-input-area">
            <input type="text" id="user-input" placeholder="Ask about crops, weather, or farming..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()" class="btn-primary"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const userInput = document.getElementById('user-input');

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function appendMessage(message, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', sender + '-message');
        messageDiv.textContent = message;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        appendMessage(message, 'user');
        userInput.value = '';

        // Show loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.classList.add('message', 'bot-message', 'loading');
        loadingDiv.textContent = 'Typing...';
        chatBox.appendChild(loadingDiv);

        try {
            const response = await fetch('chatbot_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message, type: 'chat' })
            });

            const data = await response.json();
            chatBox.removeChild(loadingDiv);

            if (data.reply) {
                appendMessage(data.reply, 'bot');
            } else if (data.error) {
                appendMessage("Error: " + data.error, 'bot');
            }
        } catch (error) {
            chatBox.removeChild(loadingDiv);
            appendMessage("Error: Could not connect to server.", 'bot');
        }
    }
</script>

<style>
    .chat-container {
        max-width: 800px;
        margin: 0 auto;
        height: 600px;
        display: flex;
        flex-direction: column;
    }
    
    .chat-box {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .message {
        margin-bottom: 15px;
        padding: 10px 15px;
        border-radius: 10px;
        max-width: 70%;
    }
    
    .bot-message {
        background: rgba(255, 255, 255, 0.8);
        color: #333;
        align-self: flex-start;
        margin-right: auto;
    }
    
    .user-message {
        background: #2ecc71;
        color: white;
        align-self: flex-end;
        margin-left: auto;
        text-align: right;
    }
    
    .chat-input-area {
        display: flex;
        gap: 10px;
    }
    
    #user-input {
        flex: 1;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.9);
    }
</style>

<?php include 'includes/footer.php'; ?>
