<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cleaning Pairs</title>
        <!-- Style -->
        <link rel="stylesheet" href="css/app.css">
    </head>
    <body>
        <h1>Cleaning Pairs</h1>
        <!-- Buttons -->
        <div>
            <button type="button" class="btn-primary" onclick="processStrings()">Process</button>
            <button type="button" class="btn-primary" onclick="generatePDF()">Generate PDF</button>
        </div>
        <!-- Download file -->
        <div id="download-link"></div>
        <!-- Input/Output -->
        <div class="container">
            <div class="col">
                <label for="input">Input</label>
                <textarea
                    id="input"
                    name="input"
                    placeholder="You can input multiple strings, one per line"
                ></textarea>
            </div>

            <div class="col">
                <label for="output">Output</label>
                <textarea id="output" readonly></textarea>
            </div>
        </div>
        <!-- JavaScript -->
        <script src="js/common.js"></script>
        <script src="js/pairs.js"></script>
        <script src="js/pdf.js"></script>
    </body>
</html>