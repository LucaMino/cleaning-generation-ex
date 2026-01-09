<?php

// require composer autoload for TCPDF
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

// validate required fields
if(!isset($data['content']) || !is_array($data['content']) || count($data['content']) === 0)
{
    http_response_code(422);
    echo json_encode(['error' => 'Missing or empty content']);
    exit;
}

// validate module
if(!isset($data['module']) || !in_array($data['module'], ['pairs', 'brackets']))
{
    http_response_code(422);
    echo json_encode(['error' => 'Invalid or missing module']);
    exit;
}

$module = $data['module'];

// sanitize input strings
$strings = sanitizeInput($data['content']);

// limit number of strings to prevent abuse
if(count($strings) > 100)
{
    http_response_code(422);
    echo json_encode(['error' => 'No valid strings provided']);
    exit;
}

// function to check if any string exceeds max length (estimated values, can be adjusted)
if(exceedsMaxLength($strings))
{
    http_response_code(422);
    echo json_encode(['error' => 'One or more strings exceed maximum length']);
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
try
{
    // set pdf storage directory
    $pdfDir = dirname(__DIR__, 2) . '/storage/pdfs/' . $module;

    // create directory if it doesn't exist
    if(!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);
    $destination = $pdfDir . '/' . $fileId . '.pdf';

    // generate the PDF
    generatePdf($strings, $destination);
} catch (Throwable $e) {
    // log error, update job status, etc.
    error_log($e->getMessage());
}

/**
 * Generates a PDF with spiral text
 *
 * @param array $content
 * @param string $destination
 * @return void
 */
function generatePdf(array $content, string $destination): void
{
    // convert content to spiral format
    $spiralText = createSpiral($content);
    // create new PDF document
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    // set document properties
    $pdf->SetCreator('Luca Minorello');
    $pdf->SetTitle('Spiral Text PDF');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    // set font using a monospaced font (each character has the same width)
    $pdf->SetFont('dejavusansmono', '', 10);
    // write the spiral text to the PDF
    $pdf->MultiCell(0, 0, $spiralText, 0, 'C', false);
    // save file
    $pdf->Output($destination, 'F');
}

/**
 * Checks if any string exceeds maximum length
 *
 * @param array $strings
 * @param int $maxHoriz
 * @param int $maxVert
 * @return bool
 */
function exceedsMaxLength(array $strings, int $maxHoriz = 30, int $maxVert = 50): bool
{
    foreach($strings as $index => $str)
    {
        // retrieve max length based on orientation
        $max = $index % 2 === 0 ? $maxHoriz : $maxVert;

        if(mb_strlen($str) > $max) return true;
    }
    return false;
}

/* SPIRAL METHODS */

/**
 * Creates a spiral string from an array of strings
 *
 * @param array $strings
 * @return string
 */
function createSpiral(array $strings): string
{
    // retrieve matrix dimensions
    $matrixDim = getMatrixDim($strings);

    $height = $matrixDim['height'];
    $width = $matrixDim['width'];

    // initialize empty matrix
    $matrix = array_fill(0, $height, array_fill(0, $width, ' '));

    // starting coordinates (using offsets to center the spiral and avoid negative indexes)
    $x = $matrixDim['offsetX'];
    $y = $matrixDim['offsetY'];

    // first direction: right
    $dir = 0;
    // 0:right, 1:down, 2:left, 3:up
    $dx = [0, 1, 0, -1];
    $dy = [1, 0, -1, 0];

    foreach($strings as $index => $str)
    {
        $lenStr = mb_strlen($str);
        // determine side length (last side uses string length)
        $lenSide = ($index === count($strings) - 1) ? $lenStr : $matrixDim['sides'][$index];

        for($i = 0; $i < $lenSide; $i++)
        {
            $char = $i < $lenStr ? mb_substr($str, $i, 1) : '-';

            $matrix[$x][$y] = $char;

            // change direction at the end of the side
            if($i == $lenSide - 1)
            {
                // rotate clockwise: 0,1,2,3,0 (right, down, left, up)
                $dir = ($dir + 1) % 4;
            }
            // move to next position
            $x += $dx[$dir];
            $y += $dy[$dir];
        }
    }

    return getSpiralString(['matrix' => $matrix, 'h' => $height, 'w' => $width], false);
}

/**
 * Calculates the dimensions of the spiral matrix
 *
 * @param array $strings
 * @return array
 */
function getMatrixDim(array $strings): array
{
    $sides = [];

    foreach($strings as $index => $string)
    {
        $len = mb_strlen($string);
        // determine side length, the first two sides are equal to the string length
        // the next sides are at least 2 units longer than the parallel side
        $sides[$index] = ($index < 2) ? $len : max($len, $sides[$index - 2] + 2);
    }

    // starting coordinates (x: row, y: column)
    $currX = 0;
    $currY = 0;
    // min/max coordinates
    $minX = 0; $maxX = 0;
    $minY = 0; $maxY = 0;
    // first direction: right
    $dir = 0;
    // 0:right, 1:down, 2:left, 3:up
    $dx = [0, 1, 0, -1];
    $dy = [1, 0, -1, 0];

    foreach($strings as $index => $string)
    {
        // determine side length (last side uses string length)
        $lenSide = ($index === count($strings) - 1) ? mb_strlen($string) : $sides[$index];

        for($i = 0; $i < $lenSide; $i++)
        {
            // move to next position
            $currX += $dx[$dir];
            $currY += $dy[$dir];
            // track the max/min rows reached
            $minX = min($minX, $currX);
            $maxX = max($maxX, $currX);
            // track the max/min columns reached
            $minY = min($minY, $currY);
            $maxY = max($maxY, $currY);
            // change direction at the end of the side
            if($i == $lenSide - 1)
            {
                // rotate clockwise: 0,1,2,3,0 (right, down, left, up)
                $dir = ($dir + 1) % 4;
            }
        }
    }

    return [
        'width'  => $maxY - $minY + 1,  // total columns
        'height' => $maxX - $minX + 1,  // total rows
        'offsetX' => 0 - $minX,         // shift to start at row 0
        'offsetY' => 0 - $minY,         // shift to start at column 0
        'sides'  => $sides
    ];
}

/**
 * Converts the matrix to a string representation
 *
 * @param array $matrixData
 * @return string
 */
function getSpiralString(array $matrixData, bool $smaller = false): string
{
    $matrix = $matrixData['matrix'];
    $h = $matrixData['h'];
    $w = $matrixData['w'];

    // start with min/max at extremes
    $minRow = $h;
    $maxRow = 0;
    $minCol = $w;
    $maxCol = 0;

    // scan matrix to find limits
    foreach($matrix as $r => $row)
    {
        foreach($row as $c => $val)
        {
            if($val !== ' ')
            {
                // update limits
                $minRow = min($minRow, $r);
                $maxRow = max($maxRow, $r);
                $minCol = min($minCol, $c);
                $maxCol = max($maxCol, $c);
            }
        }
    }

    $lines = [];

    // loop through rows (from minRow to maxRow)
    for($i = $minRow; $i <= $maxRow; $i++)
    {
        $line = [];
        // loop through columns (from minCol to maxCol)
        for($j = $minCol; $j <= $maxCol; $j++)
        {
            $char = $matrix[$i][$j] ?? ' ';
            // add char with a space for better readability
            $line[] = $char . ($smaller ? '' : ' ');
        }
        $lines[] = implode('', $line);
    }

    return implode("\n", $lines);
}

/**
 * Returns sanitized and sorted input lines
 *
 * @param array $lines
 * @return array
 */
function sanitizeInput(array $lines): array
{
    // trim and remove empty lines
    $lines = array_filter(array_map('trim', $lines), fn($line) => $line !== '');
    // reindex array
    $lines = array_values($lines);
    // sort lines by length (shortest to longest)
    usort($lines, fn($a, $b) => mb_strlen($a) <=> mb_strlen($b));
    return $lines;
}