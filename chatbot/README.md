# RAISE 2025 Chatbot

A simple AI-powered chatbot for the RAISE 2025 summer school event, providing information about the event, registration, program, competition, and more.

## Features

- Interactive chat interface with real-time streaming responses
- Multi-language support (English, French, Arabic)
- Mobile-responsive design
- SQLite database logging of chat interactions (with file-based fallback)
- Event-specific knowledge about RAISE 2025
- Two display modes: full page or embedded widget

## Files

- `index.php` - Full page chat interface with multi-language support
- `embedded.php` - Embedded version of the chatbot for in-page use
- `embedded-widget.js` - JavaScript to add the embedded chatbot to the event page
- `chatbot.php` - API endpoint that communicates with OpenAI
- `config.php` - Configuration settings
- `database.php` - Database handling (chat logging)
- `.htaccess` - Security rules to protect sensitive files
- `data/chatbot.db` - SQLite database file (auto-created)

## Technical Details

The chatbot uses:
- OpenAI GPT models for AI responses
- Server-Sent Events (SSE) for real-time streaming
- PHP for the backend
- HTML/CSS/JavaScript for the frontend
- SQLite for chat logging (with file-based fallback)

## Usage

The chatbot is available in two modes:

### Full Page Mode
- Visit `/events/raise/chatbot/` to access the full page chatbot interface
- This opens in a new tab and provides a dedicated chat experience

### Embedded Mode
- Use the chatbot directly on the event page without leaving the page
- Click the "Ask RAISE Bot" button to open the embedded chatbot
- To switch between modes:
  - Right-click the chatbot button to switch to embedded mode
  - Click the settings (gear) icon in the embedded chatbot to switch back to full page mode

## Setup

1. Ensure the `data` directory is writable (for SQLite database)
2. Update OpenAI API key in `config.php`
3. Ensure the logs directory is writable

## Security

- Direct access to configuration files is blocked via .htaccess
- API key should be stored as an environment variable in production
- Input is sanitized before processing
- SQLite database file is stored outside web root for security 