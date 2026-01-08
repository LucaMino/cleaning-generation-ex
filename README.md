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
├── Dockerfile                 # PHP-FPM container configuration
├── docker-compose.yml         # Multi-container orchestration
├── nginx.conf                 # Nginx web server configuration
├── composer.json              # PHP dependencies (TCPDF)
├── .dockerignore              # Docker build exclusions
├── .gitignore                 # Git exclusions
└── README.md                  # Project documentation
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
   docker-compose up -d --build
   ```

3. **Access the application**
   - Open your browser and navigate to: `http://localhost:8080`

### Stopping the Application
```bash
docker-compose down
```

### Viewing Logs
```bash
docker-compose logs php
```

## Design Decisions & Assumptions

### Stack: Nginx + PHP-FPM

I chose Nginx because, compared to Apache, it provides the fastcgi_finish_request() function, which allows the server to immediately send the HTTP response to the client while continuing to execute the script asynchronously in the background.

This solution works well at the current scale, and Redis could be added in the future to handle asynchronous PDF generation via worker queues; implementing it from the start would be over-engineering for the current requirements.

### State Management via Local Storage
I chose to store a key-value object in the browser’s localStorage, saving the module (brackets/pairs) and the associated file_id. This allows users to retrieve their file even if the page is closed before the PDF is ready, providing a better UX without requiring login.

### Polling System
Once the PDF is generated asynchronously, a polling API checks the file status and returns it to the user when ready. A limit has been implemented to avoid infinite polling.

## 3 - Architecture

## Motivate your choices, the pros and cons, the tradeoffs and how would you change the architecture if you should move from docker to Cloud.

### Current Architecture (Docker-based)

**Stack Choice: Nginx + PHP-FPM**
- **Pro**: `fastcgi_finish_request()` provides pseudo-async without external dependencies (Redis/RabbitMQ)
- **Pro**: Simple deployment, reproducible environment via Docker
- **Con**: PHP-FPM has a limited number of worker processes
- **Con**: Local filesystem storage is limited

**SISTEMA Tradeoff**:
- Using Nginx + PHP-FPM with `fastcgi_finish_request()` allows pseudo-asynchronous execution without external dependencies, simplifying deployment, but concurrency is limited by worker processes and the local filesystem does not scale across multiple instances.
- Docker provides a reproducible and easy-to-deploy environment, but it is not sufficient for handling high traffic volumes or multi-instance applications.
- Moving to the cloud (Lambda, S3, SQS, DynamoDB) increases scalability, resilience, and eliminates local state dependency, but introduces provider lock-in, higher latency for short tasks, and added complexity in management and monitoring.
- A stateless, event-driven architecture enables autoscaling and fault tolerance, but requires careful handling of retries, idempotency, and file lifecycle management.

### Evolution: From Docker to Cloud
Moving to the cloud involves adopting managed services and stateless components.

**Cloud Infrastructure**:
Container hosting on AWS App Runner or Google Cloud Run maintains Docker compatibility while providing cloud benefits such as auto-scaling.

**Service Architecture**:

1. **Serverless Compute**: AWS Lambda or Google Cloud Functions for PDF generation

2. **Storage**: Amazon S3 with signed URLs

3. **Managed Queue**: AWS SQS (gestione erorri)

4. **State Management**: DynamoDB (managed NoSQL) for job status tracking

## How should you modify the architecture should the service scale to thousands of users?

1. **Framework** – At large scale, using a PHP framework (e.g., Laravel or Symfony) could improve security, code structure, routing and maintainability.

2. **Horizontal Scaling** – Automatically add more web and worker containers based on demand.

3. **Frontend Distribution** – Serve static files via CDN (Cloudflare) from S3 to reduce server load and improve global speed.

4. **Rate Limiting** – Limit users (e.g., 10 PDFs/hour) and return 429 when overloaded using Redis or API Gateway.

5. **File Cleanup** – Automatically delete old PDFs with S3 lifecycle policies and clean old database records to control storage costs.

6. **Monitoring** – Track queue depth, job time, and errors, set alerts for high latency, and optionally use distributed tracing.