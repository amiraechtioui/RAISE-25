<?php
// Include configuration
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAISE 2025 Chatbot</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Add DinNext-Regular font for Arabic text */
        @font-face {
            font-family: 'DinNext';
            src: url('../assets/fonts/DinNext-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        :root {
            --primary-color: #0a2540;
            --secondary-color: #635bff;
            --accent-color: #00d4ff;
            --text-color: #424770;
            --light-bg: #f6f9fc;
            --light-accent: #e3e8ee;
            --success-color: #32D583;
            --arabic-font: 'DinNext', Arial, sans-serif;
        }

        /* Apply Arabic font for RTL content */
        .rtl {
            direction: rtl;
            text-align: right;
            font-family: var(--arabic-font) !important;
        }

        /* Apply to both user and bot messages in Arabic */
        [lang="ar"] .message {
            font-family: var(--arabic-font);
        }

        /* Apply to specific elements with RTL direction */
        [dir='rtl'] {
            font-family: var(--arabic-font);
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: transparent;
        }

        .chatbot-container {
            display: flex;
            flex-direction: column;
            height: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            background-color: white;
            width: 400px;
            transition: all 0.3s ease;
        }

        /* Special mode-specific styling */
        .expanded-mode {
            width: 100% !important;
            /* Take up full iframe width */
        }

        .expanded-mode .chat-messages {
            flex: 1;
            max-height: none;
            height: calc(100% - 155px);
            /* Consistent with normal mode */
        }

        .expanded-mode .message {
            max-width: 70%;
            font-size: 1rem;
        }

        .chat-header {
            padding: 0.8rem 1.2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex: 1;
        }

        .chat-header-title h5 {
            margin: 0;
            font-weight: 600;
            font-family: 'Space Grotesk', sans-serif;
            white-space: nowrap;
        }

        .chat-icon {
            width: 30px;
            height: 30px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-color);
        }

        .chat-header-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .language-selector {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            z-index: 100;
        }

        .language-button {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s ease;
        }

        .language-button:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        .language-dropdown {
            position: absolute;
            top: 44px;
            left: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            z-index: 150;
            display: none;
            min-width: 140px;
            animation: fadeIn 0.2s ease;
        }

        .language-dropdown.active {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .language-option {
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
        }

        .language-option:hover {
            background-color: var(--light-bg);
        }

        .language-option.active {
            font-weight: 500;
            background-color: var(--light-accent);
        }

        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            height: calc(100% - 155px);
            /* Adjusted to account for header + input + footer */
        }

        .message {
            max-width: 80%;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            line-height: 1.5;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .user-message {
            align-self: flex-end;
            background-color: var(--light-bg);
            color: var(--text-color);
            border-bottom-right-radius: 4px;
        }

        .bot-message {
            align-self: flex-start;
            background-color: var(--light-accent);
            color: var(--text-color);
            border-bottom-left-radius: 4px;
        }

        .bot-message a {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .bot-message a:hover {
            text-decoration: underline;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            align-self: flex-start;
            background-color: var(--light-accent);
            padding: 0.7rem 1rem;
            border-radius: 12px;
            border-bottom-left-radius: 4px;
        }

        .dot {
            width: 8px;
            height: 8px;
            background-color: #aaa;
            border-radius: 50%;
            margin: 0 2px;
            animation: typing-dot 1.4s infinite ease-in-out;
        }

        .dot:nth-child(1) {
            animation-delay: 0s;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing-dot {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-5px);
            }
        }

        .chat-input {
            padding: 0.8rem;
            border-top: 1px solid var(--light-accent);
            display: flex;
            background-color: white;
        }

        .chat-input input {
            flex: 1;
            padding: 0.7rem 1rem;
            border: 1px solid var(--light-accent);
            border-radius: 24px;
            outline: none;
            transition: border-color 0.2s;
        }

        .chat-input input:focus {
            border-color: var(--secondary-color);
        }

        /* Style for Arabic input */
        [lang="ar"] .chat-input input {
            font-family: var(--arabic-font);
            direction: rtl;
            text-align: right;
        }

        .send-button {
            width: 40px;
            height: 40px;
            margin-left: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .send-button:hover {
            transform: scale(1.05);
        }

        .send-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* RTL Support */
        .rtl {
            direction: rtl;
            text-align: right;
        }

        .rtl .user-message {
            align-self: flex-end;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 12px;
        }

        .rtl .bot-message {
            align-self: flex-start;
            border-bottom-right-radius: 4px;
            border-bottom-left-radius: 12px;
        }

        /* Make embedded version responsive */
        @media (max-width: 576px) {
            .chatbot-container {
                border-radius: 0;
                width: 100%;
            }

            .language-selector {
                margin-right: 5px;
            }
        }

        /* Add responsive classes for different sizes */
        .minimized-mode .chat-messages,
        .minimized-mode .chat-input,
        .minimized-mode .typing-indicator,
        .minimized-mode .powered-by {
            display: none;
        }

        @media (min-width: 600px) {

            /* Adjustments for larger screens */
            .expanded-mode .message {
                max-width: 75%;
                /* Narrower messages on wide screens */
            }
        }

        /* Add a powered-by footer */
        .powered-by {
            text-align: center;
            font-size: 0.75rem;
            color: #8b8b8b;
            padding: 0.3rem;
            background-color: rgba(246, 249, 252, 0.7);
            border-top: 1px solid var(--light-accent);
        }

        .powered-by a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .powered-by a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="chatbot-container">
        <div class="chat-header">
            <div class="chat-header-title">
                <div class="chat-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h5>RAISE Bot</h5>
                <div class="language-selector">
                    <div class="language-button" style="background-color: rgba(255, 255, 255, 0.3); font-size: 18px;">
                        🇺🇸
                    </div>
                    <div class="language-dropdown">
                        <div class="language-option" data-lang="en">
                            <span>🇺🇸</span> English
                        </div>
                        <div class="language-option" data-lang="fr">
                            <span>🇫🇷</span> Français
                        </div>
                        <div class="language-option" data-lang="ar">
                            <span>🇸🇦</span> العربية
                        </div>
                    </div>
                </div>
            </div>
            <div class="chat-header-controls">
            </div>
        </div>

        <div class="chat-messages">
            <!-- Messages will be added here dynamically -->
            <div class="message bot-message" id="welcome-message">
                <h5>Hello! 👋</h5>
                <p>I'm the RAISE 2025 assistant. Ask me anything about the summer school, registration, program, and more!</p>
            </div>
        </div>

        <div class="typing-indicator" style="display: none;">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>

        <div class="chat-input">
            <input type="text" id="user-input" placeholder="Type your message...">
            <button id="send-button" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

        <div class="powered-by">
            Powered by <a href="https://scalexi.ai" target="_blank">ScaleX Innovation</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM elements
            const chatBox = document.querySelector('.chat-messages');
            const userInput = document.getElementById('user-input');
            const sendButton = document.getElementById('send-button');
            const typingIndicator = document.querySelector('.typing-indicator');
            const languageButton = document.querySelector('.language-button');
            const languageDropdown = document.querySelector('.language-dropdown');
            const languageOptions = document.querySelectorAll('.language-option');
            const welcomeMessage = document.getElementById('welcome-message');
            const chatbotContainer = document.querySelector('.chatbot-container');

            // Listen for resize messages from parent
            window.addEventListener('message', function(event) {
                if (event.data && event.data.action === 'resize') {
                    // Remove all size classes first
                    chatbotContainer.classList.remove('normal-mode', 'expanded-mode', 'minimized-mode');

                    // Add the appropriate class
                    chatbotContainer.classList.add(event.data.state + '-mode');

                    // Scroll to bottom after resize
                    setTimeout(() => {
                        scrollToBottom();
                    }, 100);
                }
            });

            // Set initial language
            let currentLang = 'en';
            updateLanguageButton('🇺🇸');

            // Function to update language button with flag
            function updateLanguageButton(flag) {
                languageButton.innerHTML = flag;
            }

            // Update welcome message based on language
            function updateWelcomeMessage() {
                if (currentLang === 'en') {
                    welcomeMessage.innerHTML = `<h5>Hello! 👋</h5><p>I'm the RAISE 2025 assistant. Ask me anything about the summer school, registration, program, and more!</p>`;
                    welcomeMessage.classList.remove('rtl');
                    document.documentElement.setAttribute('lang', 'en');
                    userInput.setAttribute('placeholder', 'Type your message...');
                    updateLanguageButton('🇺🇸');
                } else if (currentLang === 'fr') {
                    welcomeMessage.innerHTML = `<h5>Bonjour! 👋</h5><p>Je suis l'assistant RAISE 2025. Posez-moi des questions sur l'école d'été, l'inscription, le programme, et plus encore!</p>`;
                    welcomeMessage.classList.remove('rtl');
                    document.documentElement.setAttribute('lang', 'fr');
                    userInput.setAttribute('placeholder', 'Tapez votre message...');
                    updateLanguageButton('🇫🇷');
                } else if (currentLang === 'ar') {
                    welcomeMessage.innerHTML = `<h5 dir='rtl' style="font-family: var(--arabic-font);">مرحباً! 👋</h5><p dir='rtl' style="font-family: var(--arabic-font);">أنا مساعد RAISE 2025. اسألني أي شيء عن المدرسة الصيفية، التسجيل، البرنامج، والمزيد!</p>`;
                    welcomeMessage.classList.add('rtl');
                    document.documentElement.setAttribute('lang', 'ar');
                    userInput.setAttribute('placeholder', 'اكتب رسالتك...');
                    userInput.style.fontFamily = 'var(--arabic-font)';
                    userInput.style.direction = 'rtl';
                    updateLanguageButton('🇸🇦');
                }
            }

            // Language selector event listeners
            languageButton.addEventListener('click', function(e) {
                // Toggle dropdown
                e.stopPropagation();
                languageDropdown.classList.toggle('active');
            });

            // Handle language selection
            languageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Set current language
                    currentLang = this.getAttribute('data-lang');

                    // Update welcome message
                    updateWelcomeMessage();

                    // Hide dropdown
                    languageDropdown.classList.remove('active');
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                languageDropdown.classList.remove('active');
            });

            // Add message to chat
            function addMessage(text, className) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${className}`;

                // Apply RTL and Arabic font for Arabic messages (both user and bot)
                if (currentLang === 'ar') {
                    messageDiv.classList.add('rtl');
                    messageDiv.style.fontFamily = 'var(--arabic-font)';
                } else if (currentLang !== 'ar' && userInput.style.direction === 'rtl') {
                    // Reset text direction for non-Arabic languages
                    userInput.style.direction = 'ltr';
                    userInput.style.fontFamily = '';
                }

                messageDiv.innerHTML = text;
                chatBox.appendChild(messageDiv);
                scrollToBottom();
            }

            // Scroll chat to bottom
            function scrollToBottom() {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            // Send message to AI and process response
            function sendToAI(message) {
                // Create an absolute path to chatbot.php
                const baseUrl = new URL(window.location.href);
                const chatbotUrl = new URL('chatbot.php', baseUrl.href.replace(/\/[^\/]*$/, '/'));

                // Create EventSource for Server-Sent Events with absolute URL
                const eventSource = new EventSource(`${chatbotUrl}?message=${encodeURIComponent(message)}&lang=${currentLang}`);

                let fullResponse = '';
                let responseDiv = null;

                // Listen for messages
                eventSource.addEventListener('message', function(e) {
                    const data = JSON.parse(e.data);

                    if (data.error) {
                        // Hide typing indicator
                        typingIndicator.style.display = 'none';

                        // Show error message
                        addMessage(`Error: ${data.error}`, 'bot-message');

                        // Close connection
                        eventSource.close();
                        return;
                    }

                    if (data.choices && data.choices[0].delta && data.choices[0].delta.content) {
                        const content = data.choices[0].delta.content;

                        // If this is the first chunk, create a new message div
                        if (!responseDiv) {
                            // Hide typing indicator
                            typingIndicator.style.display = 'none';

                            responseDiv = document.createElement('div');
                            responseDiv.className = 'message bot-message';

                            // Add RTL class for Arabic
                            if (currentLang === 'ar') {
                                responseDiv.classList.add('rtl');
                            }

                            chatBox.appendChild(responseDiv);
                        }

                        // Append to full response
                        fullResponse += content;

                        // Update the message content
                        responseDiv.innerHTML = fullResponse;

                        // Scroll to the bottom
                        scrollToBottom();
                    }
                });

                // Handle completion
                eventSource.addEventListener('done', function() {
                    eventSource.close();

                    // Enable input and button
                    userInput.disabled = false;
                    sendButton.disabled = false;
                    userInput.focus();
                });

                // Handle ping events (keepalive)
                eventSource.addEventListener('ping', function() {
                    // Just acknowledge the ping
                });

                // Handle connection error
                eventSource.onerror = function() {
                    // Hide typing indicator
                    typingIndicator.style.display = 'none';

                    // Only show error if we haven't received any response yet
                    if (!responseDiv) {
                        addMessage("Sorry, I'm having trouble connecting to the server. Please try again later.", 'bot-message');
                    }

                    // Close connection
                    eventSource.close();

                    // Enable input and button
                    userInput.disabled = false;
                    sendButton.disabled = false;
                    userInput.focus();
                };
            }

            // Send button click handler
            sendButton.addEventListener('click', function() {
                sendMessage();
            });

            // Enter key press handler
            userInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });

            // Send message function
            function sendMessage() {
                const message = userInput.value.trim();

                if (message === '') return;

                // Add user message to chat
                addMessage(message, 'user-message');

                // Clear input
                userInput.value = '';

                // Disable input and button while waiting for response
                userInput.disabled = true;
                sendButton.disabled = true;

                // Show typing indicator
                typingIndicator.style.display = 'flex';
                chatBox.appendChild(typingIndicator);
                scrollToBottom();

                // Send to AI
                sendToAI(message);
            }

            // Focus input on load
            userInput.focus();
        });
    </script>
</body>

</html>