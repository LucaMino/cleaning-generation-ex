<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

header('Content-Type: application/json');

// validate request method
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// validate content type
if(!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json'))
{
    http_response_code(415);
    echo json_encode(['error' => 'Unsupported Media Type']);
    exit;
}

// retrieve the JSON data
$data = json_decode(file_get_contents('php://input'), true);

// check for JSON errors
if(json_last_error() !== JSON_ERROR_NONE)
{
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// TODO validate required field
if(!isset($data['content']) || !is_array($data['content']) || count($data['content']) === 0)
{
    http_response_code(422);
    echo json_encode(['error' => 'Missing or empty content']);
    exit;
}

// generate a unique file ID
$fileId = uniqid('', true);

$response = [
    'file_id' => $fileId,
    'poll_url' => '/api/check_status.php?id=' . $fileId,
];

// return immediate response
echo json_encode($response);

// flush the response to the client, close the connection but let the script run
if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();

// generate the PDF asynchronously
try {
    $mergedContent = sanitizeInput($data['content']);
    generatePdf($mergedContent, $fileId);
} catch (Throwable $e) {
    // log error, update job status, etc.
    error_log($e->getMessage());
}

/**
 * todo
 *
 * @param string $content
 * @param string $fileId
 * @return void
 */
function generatePdf(string $content, string $fileId): void
{
    sleep(2);
    // convert content to spiral format
    $spiralText = spiralFromCenter($content);

    // set pdf storage directory and ensure it exists
    $pdfDir = dirname(__DIR__, 2) . '/storage/pdfs';

    if(!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);

    $filename = $fileId . '.pdf';
    $filePath = $pdfDir . '/' . $filename;

    // create new PDF document
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    // set document properties
    $pdf->SetCreator('Luca Minorello');
    $pdf->SetTitle('Spiral Text PDF');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    // set font using a monospaced font (each character has the same width)
    $pdf->SetFont('courier', '', 10);
    // write the spiral text to the PDF
    $pdf->MultiCell(0, 0, $spiralText, 0, 'C', false);
    // save file
    $pdf->Output($filePath, 'F');
}

function spiralFromCenter($str)
{
    $len = strlen($str);
    if ($len == 0) return "";

    $size = ceil(sqrt($len)) * 4;
    if ($size % 2 == 0) $size++;

    $matrix = array_fill(0, $size, array_fill(0, $size, ' '));

    $x = $y = intdiv($size, 2);
    $matrix[$y][$x] = $str[0];

    $index = 1;
    $steps = 2;
    $dx = [2, 0, -2, 0];
    $dy = [0, -2, 0, 2];
    $dir = 0;

    while ($index < $len) {
        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < $steps && $index < $len; $j++) {
                $x += $dx[$dir];
                $y += $dy[$dir];
                if ($y >= 0 && $y < $size && $x >= 0 && $x < $size) {
                    $matrix[$y][$x] = $str[$index++];
                }
            }
            $dir = ($dir + 1) % 4;
        }
        $steps += 2;
    }

    $minX = $size; $maxX = 0;
    $minY = $size; $maxY = 0;
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            if ($matrix[$i][$j] != ' ') {
                $minY = min($minY, $i); $maxY = max($maxY, $i);
                $minX = min($minX, $j); $maxX = max($maxX, $j);
            }
        }
    }

    $result = "";
    for ($i = $minY; $i <= $maxY; $i++) {
        for ($j = $minX; $j <= $maxX; $j++) {
            $result .= $matrix[$i][$j] . ' ';
        }
        $result .= "\n";
    }

    return $result;
}





function sanitizeInput(array $lines): string
{
    usort($lines, function($a, $b) {
        return strlen($a) - strlen($b);
    });

    $result = [];

    $grouped = [];
    foreach ($lines as $line) {
        $len = strlen($line);
        if (!isset($grouped[$len])) {
            $grouped[$len] = [];
        }
        $grouped[$len][] = $line;
    }

    foreach ($grouped as $length => $words) {
        if (count($words) == 1) {
            $result[] = $words[0];
        } else {
            foreach ($words as $idx => $word) {
                if ($idx > 0) {
                    $result[] = '-';
                }
                $result[] = $word;
            }
        }
    }

    return implode('', $result);
}