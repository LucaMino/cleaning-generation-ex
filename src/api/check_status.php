<?php

header('Content-Type: application/json');

$pdfDir = dirname(__DIR__, 2) . '/storage/pdfs';

// validate request method
if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
// validate content type
if(!isset($_GET['id']) || trim($_GET['id']) === '')
{
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or empty file ID'
    ]);
    exit;
}

// sanitize file ID
$fileId = basename(trim($_GET['id']));
$filePath = $pdfDir . '/' . $fileId . '.pdf';

try
{
    if(file_exists($filePath))
    {
        // file is ready, return download URL
        echo json_encode([
            'status' => 'completed',
            'download_url' => '/api/download_pdf.php?id=' . $fileId
        ]);
    } else {
        // file not ready yet
        echo json_encode([
            'status' => 'processing'
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Internal Server Error'
    ]);
    error_log($e->getMessage());
}