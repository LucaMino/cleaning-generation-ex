<?php

$pdfDir = dirname(__DIR__, 2) . '/storage/pdfs';

// clean output buffer to ensure PDF file integrity during download
ob_get_level() && ob_end_clean();

// validate request method
if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    http_response_code(405);
    header('Allow: GET');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// sanitize file ID
$fileId = basename(trim($_GET['id'] ?? ''));
$module = basename(trim($_GET['module'] ?? ''));

// validate file ID
if($fileId === '' || !preg_match('/^[a-f0-9.]{20,30}$/', $fileId))
{
    http_response_code(422);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or empty file ID'
    ]);
    exit;
}

// validate module
if(!in_array($module, ['pairs', 'brackets']))
{
    http_response_code(422);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing module']);
    exit;
}

$filePath = $pdfDir . '/' . $module . '/' . $fileId . '.pdf';

// check if file exists
if(!file_exists($filePath) || !is_file($filePath))
{
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File not found']);
    exit;
}

// set headers for file download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));

// output the file
readfile($filePath);
exit;