<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cleaning Pairs</title>
        <!-- Style -->
        <link rel="stylesheet" href="app.css">
        <!-- JavaScript -->
        <script src="js/pdf.js"></script>
    </head>
    <body>
        <h1>Cleaning Pairs</h1>
        <!-- Buttons -->
        <div>
            <button onclick="processStrings()">Process</button>
            <button onclick="generatePDF()">Generate PDF</button>
        </div>
        <!-- Download file -->
        <div id="download-link" style="margin-top: 10px;"></div>
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

        <script>
            function removeMatchingPairCharacters(str)
            {
                // set en pairs
                const pairs = ['az', 'by', 'cx', 'dw', 'ev', 'fu', 'gt', 'hs', 'ir', 'jq', 'kp', 'lo', 'mn'];
                // sanitize input
                const s = str.trim();
                // extract first and last characters
                const first = s[0];
                const last = s[s.length - 1];
                const pair = first + last;
                // if pair matches, remove them
                if(pairs.includes(pair))
                {
                    // TODO guarda const
                    return s.slice(1, -1);
                }
                return null;
            }

            function processStrings()
            {
                const input = document.getElementById('input').value;
                const lines = input.split('\n');
                // process each line
                const processed = lines.map(line => removeMatchingPairCharacters(line));
                // display output
                document.getElementById('output').value = processed.join('\n');
            }
        </script>
    </body>
</html>