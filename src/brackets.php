<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cleaning Brackets</title>
        <!-- Style -->
        <link rel="stylesheet" href="css/app.css">
    </head>
    <body>
        <h1>Cleaning Brackets</h1>
        <!-- Buttons -->
        <div>
            <button type="button" class="btn-primary" onclick="processStrings('brackets')">Process</button>
            <button
                id="generate-pdf-btn"
                type="button"
                class="btn-primary"
                onclick="generatePDF('brackets')"
            >
                Generate PDF
            </button>
        </div>
        <!-- Download file -->
        <div id="download-link" data-module="brackets"></div>
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
        <script src="js/brackets.js"></script>
        <script src="js/pdf.js"></script>
    </body>
</html>