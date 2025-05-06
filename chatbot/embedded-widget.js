/**
 * RAISE 2025 Embedded Chatbot Widget
 * This script adds an embedded chatbot widget to the RAISE 2025 event page
 */
(function () {
    // Create styles
    const styles = document.createElement('style');
    styles.innerHTML = `
        /* Chatbot Widget Styles */
        .raise-chatbot-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 10000;
            font-family: 'Poppins', sans-serif;
        }
        
        .raise-chat-button {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0a2540, #635bff);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .raise-chat-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
        }
        
        .raise-chat-icon {
            background-color: white;
            color: #635bff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 20px;
        }
        
        .raise-chat-text {
            font-weight: 500;
            font-size: 16px;
        }
        
        .raise-chatbot-container {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 400px;
            height: 600px;
            max-width: 90vw;
            max-height: 70vh;
            z-index: 10001;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            display: none;
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
            opacity: 0;
            transform: translateY(20px);
        }
        
        .raise-chatbot-container.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .raise-chatbot-container.minimized {
            height: 65px;
            overflow: hidden;
            resize: none;
        }
        
        .raise-chatbot-container.expanded {
            width: 600px;
            height: 800px;
            max-width: 95vw;
            max-height: 80vh;
        }
        
        .raise-chatbot-container.minimized .raise-chatbot-iframe {
            pointer-events: none;
        }
        
        .raise-chatbot-iframe {
            width: 100%;
            height: 100%;
            border: none;
            overflow: hidden; /* Prevent scrollbars */
        }
        
        .raise-widget-close,
        .raise-widget-toggle,
        .raise-widget-minimize,
        .raise-widget-settings {
            position: absolute;
            top: 10px;
            width: 34px;
            height: 34px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            z-index: 10002;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .raise-widget-close {
            right: 15px;
        }
        
        .raise-widget-toggle {
            right: 60px;
        }
        
        .raise-widget-minimize {
            right: 105px;
        }
        
        .raise-widget-settings {
            display: none;
        }
        
        .raise-widget-close:hover,
        .raise-widget-toggle:hover,
        .raise-widget-minimize:hover,
        .raise-widget-settings:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .raise-widget-close:hover {
            transform: rotate(90deg);
        }
        
        .raise-settings-dropdown {
            display: none;
        }
        
        /* Responsive styles */
        @media (max-width: 576px) {
            .raise-chat-text {
                display: none;
            }
            
            .raise-chat-button {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                justify-content: center;
                padding: 0;
            }
            
            .raise-chat-icon {
                margin-right: 0;
            }
            
            .raise-chatbot-container {
                bottom: 80px;
                right: 10px;
                width: 95vw;
                height: 70vh;
            }
            
            .raise-chatbot-container.expanded {
                width: 98vw; /* Almost full width on mobile */
                height: 85vh; /* Taller on mobile */
                bottom: 60px; /* Closer to bottom */
                right: 1vw; /* Centered */
            }
            
            /* Adjust button spacing for smaller screens but maintain enough space */
            .raise-widget-toggle {
                right: 55px;
            }
            
            .raise-widget-minimize {
                right: 95px;
            }
            
            .raise-widget-settings {
                right: 135px;
            }
            
            .raise-settings-dropdown {
                right: 135px;
            }
        }
    `;
    document.head.appendChild(styles);

    // Create widget elements
    const widgetContainer = document.createElement('div');
    widgetContainer.className = 'raise-chatbot-widget';

    // Chat button
    const chatButton = document.createElement('div');
    chatButton.className = 'raise-chat-button';
    chatButton.innerHTML = `
        <div class="raise-chat-icon">
            <i class="fas fa-robot"></i>
        </div>
        <div class="raise-chat-text">Ask RAISE Bot</div>
    `;

    // Chatbot container
    const chatbotContainer = document.createElement('div');
    chatbotContainer.className = 'raise-chatbot-container';

    // Close button
    const closeButton = document.createElement('div');
    closeButton.className = 'raise-widget-close';
    closeButton.innerHTML = '<i class="fas fa-times"></i>';

    // Toggle button (switch between embedded and full page)
    const toggleButton = document.createElement('div');
    toggleButton.className = 'raise-widget-toggle';
    toggleButton.title = 'Open in new tab';
    toggleButton.innerHTML = '<i class="fas fa-external-link-alt"></i>';

    // Minimize/Expand button
    const minimizeButton = document.createElement('div');
    minimizeButton.className = 'raise-widget-minimize';
    minimizeButton.title = 'Maximize';
    minimizeButton.innerHTML = '<i class="fas fa-expand"></i>';

    // Settings button
    const settingsButton = document.createElement('div');
    settingsButton.className = 'raise-widget-settings';
    settingsButton.title = 'Settings';
    settingsButton.innerHTML = '<i class="fas fa-cog"></i>';

    // Settings dropdown
    const settingsDropdown = document.createElement('div');
    settingsDropdown.className = 'raise-settings-dropdown';

    // Settings options
    const switchToFullOption = document.createElement('div');
    switchToFullOption.className = 'raise-settings-option';
    switchToFullOption.innerHTML = 'Always use full-page mode';

    // Add option to dropdown
    settingsDropdown.appendChild(switchToFullOption);

    // Iframe
    const iframe = document.createElement('iframe');
    iframe.className = 'raise-chatbot-iframe';

    // Get the current script path
    const scripts = document.getElementsByTagName('script');
    const scriptUrl = scripts[scripts.length - 1].src;
    const scriptPath = scriptUrl.substring(0, scriptUrl.lastIndexOf('/') + 1);

    // Set the iframe source to embedded.php using the path from the script
    iframe.src = scriptPath + 'embedded.php';
    iframe.title = 'RAISE 2025 Chatbot';
    iframe.setAttribute('allowtransparency', 'true');

    // Set initial mode when iframe loads
    iframe.onload = function () {
        // Set initial mode to normal
        notifyIframeResize('normal');
    };

    // Add elements to DOM
    chatbotContainer.appendChild(iframe);
    chatbotContainer.appendChild(closeButton);
    chatbotContainer.appendChild(toggleButton);
    chatbotContainer.appendChild(minimizeButton);
    chatbotContainer.appendChild(settingsButton);
    chatbotContainer.appendChild(settingsDropdown);

    widgetContainer.appendChild(chatButton);
    widgetContainer.appendChild(chatbotContainer);

    document.body.appendChild(widgetContainer);

    // Add event listeners
    chatButton.addEventListener('click', function () {
        chatbotContainer.classList.add('active');

        // Try to focus the input field inside the iframe
        setTimeout(() => {
            try {
                const iframeInput = iframe.contentWindow.document.getElementById('user-input');
                if (iframeInput) iframeInput.focus();
            } catch (e) {
                // Ignore errors from cross-origin restrictions
            }
        }, 300);

        // Add click listener to the iframe's header for minimize toggle
        setTimeout(() => {
            try {
                const iframeDocument = iframe.contentWindow.document;
                const chatHeader = iframeDocument.querySelector('.chat-header');

                if (chatHeader) {
                    // Double click on header to toggle between minimized and normal
                    chatHeader.addEventListener('dblclick', function (e) {
                        if (chatbotContainer.classList.contains('minimized')) {
                            // If minimized, restore to normal
                            chatbotContainer.classList.remove('minimized');
                            minimizeButton.title = 'Maximize';
                            minimizeButton.innerHTML = '<i class="fas fa-expand"></i>';
                            notifyIframeResize('normal');
                        } else {
                            // If normal or expanded, minimize
                            chatbotContainer.classList.remove('expanded');
                            chatbotContainer.classList.add('minimized');
                            minimizeButton.title = 'Restore';
                            minimizeButton.innerHTML = '<i class="fas fa-window-restore"></i>';
                            notifyIframeResize('minimized');
                        }
                    });
                }
            } catch (e) {
                // Ignore errors from cross-origin restrictions
            }
        }, 500);
    });

    closeButton.addEventListener('click', function (e) {
        e.stopPropagation();
        chatbotContainer.classList.remove('active');
    });

    toggleButton.addEventListener('click', function (e) {
        e.stopPropagation();
        // Open the full chatbot in a new tab
        window.open(scriptPath + 'index.php', '_blank');
        chatbotContainer.classList.remove('active');
    });

    // Function to notify iframe about container resize
    function notifyIframeResize(state) {
        try {
            // Check if iframe has loaded
            if (iframe.contentWindow) {
                // Post message to iframe with new state
                iframe.contentWindow.postMessage({
                    action: 'resize',
                    state: state // 'normal', 'expanded', or 'minimized'
                }, '*');
            }
        } catch (e) {
            // Ignore cross-origin errors
            console.error('Could not notify iframe about resize:', e);
        }
    }

    // Minimize/Expand button functionality
    minimizeButton.addEventListener('click', function (e) {
        e.stopPropagation();
        if (chatbotContainer.classList.contains('minimized')) {
            // Expand from minimized to normal size
            chatbotContainer.classList.remove('minimized');
            minimizeButton.title = 'Maximize';
            minimizeButton.innerHTML = '<i class="fas fa-expand"></i>';
            notifyIframeResize('normal');

            // Try to focus the input field
            setTimeout(() => {
                try {
                    const iframeInput = iframe.contentWindow.document.getElementById('user-input');
                    if (iframeInput) iframeInput.focus();
                } catch (e) {
                    // Ignore errors from cross-origin restrictions
                }
            }, 300);
        } else if (chatbotContainer.classList.contains('expanded')) {
            // Return to normal size from expanded
            chatbotContainer.classList.remove('expanded');
            minimizeButton.title = 'Maximize';
            minimizeButton.innerHTML = '<i class="fas fa-expand"></i>';
            notifyIframeResize('normal');
        } else {
            // Expand from normal to larger size
            chatbotContainer.classList.add('expanded');
            minimizeButton.title = 'Restore';
            minimizeButton.innerHTML = '<i class="fas fa-compress"></i>';
            notifyIframeResize('expanded');
        }
    });

    // Add double-click on minimize button to truly minimize
    minimizeButton.addEventListener('dblclick', function (e) {
        e.stopPropagation();

        if (!chatbotContainer.classList.contains('minimized')) {
            // Minimize to header only
            chatbotContainer.classList.remove('expanded'); // First remove expanded if present
            chatbotContainer.classList.add('minimized');
            minimizeButton.title = 'Restore';
            minimizeButton.innerHTML = '<i class="fas fa-window-restore"></i>';
            notifyIframeResize('minimized');
        } else {
            // Expand from minimized to normal size
            chatbotContainer.classList.remove('minimized');
            minimizeButton.title = 'Maximize';
            minimizeButton.innerHTML = '<i class="fas fa-expand"></i>';
            notifyIframeResize('normal');
        }
    });

    // Settings button click
    settingsButton.addEventListener('click', function (e) {
        e.stopPropagation();
        settingsDropdown.classList.toggle('active');
    });

    // Switch to full-page mode option
    switchToFullOption.addEventListener('click', function (e) {
        e.stopPropagation();

        if (confirm('Switch to full-page mode? The chatbot will always open in a new tab.')) {
            // Remove embedded preference
            localStorage.removeItem('useEmbeddedChatbot');

            // Reload the page to apply changes
            window.location.reload();
        }

        settingsDropdown.classList.remove('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function () {
        settingsDropdown.classList.remove('active');
    });

    // Close on Esc key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (settingsDropdown.classList.contains('active')) {
                settingsDropdown.classList.remove('active');
            } else if (chatbotContainer.classList.contains('active')) {
                chatbotContainer.classList.remove('active');
            }
        }
    });

    // Remove the existing chatbot button if present
    const existingButton = document.getElementById('chatbotButton');
    if (existingButton) {
        existingButton.remove();
    }

    // Remove the tooltip if present
    const existingTooltip = document.querySelector('.chatbot-tooltip');
    if (existingTooltip) {
        existingTooltip.remove();
    }
})(); 