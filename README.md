# Cleaning & Generation Exercise

A PHP web application that processes and sanitizes text strings, then generates spiral-pattern PDFs from the cleaned content.

## Overview

This application provides two text cleaning tools:
1. **Cleaning Brackets** - Removes outer matching parentheses from strings
2. **Cleaning Pairs** - Removes matching character pairs (az, by, cx, etc.) wrapped around strings

After processing, users can generate PDFs with the cleaned text arranged in a spiral pattern from the center outward.

## Technology Stack

- **Backend**: PHP 8.2 (without MVC frameworks)
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **PDF Generation**: TCPDF library
- **Infrastructure**: Docker, Nginx, PHP-FPM

## Project Structure

```
.
├── src/
│   ├── index.php              # Homepage with tool selection
│   ├── brackets.php           # Brackets cleaning interface
│   ├── pairs-en.php           # Pairs cleaning interface
│   ├── js/
│   │   ├── common.js          # Shared processing logic
│   │   ├── brackets.js        # Bracket removal algorithm
│   │   ├── pairs.js           # Pair removal algorithm
│   │   └── pdf.js             # PDF generation & polling
│   ├── css/
│   │   └── app.css            # Application styles
│   └── api/
│       ├── generate_pdf.php   # PDF generation endpoint
│       ├── check_status.php   # PDF status polling endpoint
│       └── download_pdf.php   # PDF download endpoint
├── storage/pdfs/              # Generated PDF files (auto-created)
├── Dockerfile                 # PHP-FPM container
├── docker-compose.yml         # Multi-container orchestration
├── nginx.conf                 # Nginx configuration
└── composer.json              # PHP dependencies
```

## Installation & Setup

### Prerequisites
- Docker
- Docker Compose

### Running the Application

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd cleaning-generation-ex
   ```

2. **Start the containers**
   ```bash
   docker-compose up -d
   ```

3. **Access the application**
   - Open your browser and navigate to: `http://localhost:8080`

### Stopping the Application
```bash
docker-compose down
```

## Design Decisions & Assumptions



2. **Asynchronous Processing**: Implemented polling instead of WebSockets for simplicity


4. **LocalStorage**: Tracks pending PDF generation across page refreshes

6. **Character Spacing**: Spiral uses 2-unit spacing for better readability
