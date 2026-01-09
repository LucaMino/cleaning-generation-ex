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

## Installation & Setup

### Prerequisites
- Docker
- Docker Compose

### Running the Application

1. **Clone the repository**
   ```bash
   git clone https://github.com/LucaMino/cleaning-generation-ex.git
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

I chose Nginx because, compared to Apache, it provides the `fastcgi_finish_request()` function which allows the server to immediately send the HTTP response to the client while continuing to execute the script asynchronously in the background.

This solution works well at the current scale using PHP-FPM workers, while adding a queue system (Redis) with dedicated workers from the start would be over-engineering.

### State Management via Local Storage
I chose to store a key-value object in the browser’s localStorage, mapping the module (brackets/pairs) to its file_id. This lets users retrieve their PDF even if the page is closed before generation is complete, improving UX without authentication.

### Polling System
Once the PDF is generated asynchronously, a polling API checks the file status and returns it to the user when ready.

### Spiral Algorithm
- Sort the input strings by length in ascending order
- Simulates the path to calculate the exact matrix size
- Initializes the matrix and sets the starting point using calculated offsets
- Places characters following a spiral movement in the four directions, filling with '-' when needed
- Crops empty areas of the matrix and returns the final spiral as a string used in the PDF

# 3 - Architecture
## Motivate your choices, the pros and cons, the tradeoffs and how would you change the architecture if you should move from docker to Cloud.

### Current Architecture (Docker-based)

- **Pro**: `fastcgi_finish_request()` provides pseudo-async without external dependencies
- **Pro**: Simple deployment, reproducible environment via Docker
- **Con**: PHP-FPM has a limited number of worker processes
- **Con**: Local filesystem storage is limited

**Tradeoff**:
The current architecture prioritizes simplicity, fast development and minimal dependencies but comes with the following limitations:
- Using Nginx + PHP-FPM with `fastcgi_finish_request()` allows pseudo-async execution but concurrency is limited by the number of PHP workers
- PDF generation still occupies PHP-FPM workers and there is no automatic retry or error handling
- Using localStorage for state management cannot be shared across devices and provides no server-side persistence
- Files are stored on the local filesystem which limits scalability and durability across multiple instances

### Cloud Architecture (from Docker to Cloud)

**Service Architecture**:
1. **Container Hosting** Use managed container services from cloud providers (AWS, Google Cloud) to preserve Docker compatibility while enabling auto-scaling

2. **Serverless Compute**: Move PDF generation to serverless functions (AWS Lambda or Google Cloud Functions)

3. **Storage**: Use cloud storage (S3, GCS) with signed URLs for secure, temporary access to generated PDFs

4. **Managed Queue** – A cloud queue service that allows you to track PDF generation jobs and retry failed tasks automatically

5. **State Management** – Track job status and file_id in a database, using an anonymous token for users

## How should you modify the architecture should the service scale to thousands of users?

1. **Framework** – At large scale, using a PHP framework (Laravel) could improve security, code structure, routing and maintainability

2. **Scaling** - Handle more requests by increasing compute resources and distributing traffic through a load balancer

3. **Frontend Distribution** – Serve static frontend files (JS, CSS, HTML) via CDN (Cloudflare) from cloud storage to reduce server load

4. **Realtime Updates** – Replace polling with WebSockets or a managed service (Pusher) to notify users when PDF generation is complete and reduce unnecessary requests

5. **Rate Limiting** – Apply a per-user limit on PDF generation

6. **File Cleanup** – Automatically delete old PDFs with cloud storage lifecycle policies

7. **Monitoring** – Track job time, errors and set alerts
