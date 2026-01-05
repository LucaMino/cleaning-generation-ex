<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cleaning Brackets</title>
        <!-- Style -->
        <link rel="stylesheet" href="app.css">
        <!-- JavaScript -->
        <script src="js/pdf.js"></script>
    </head>
    <body>
        <h1>Cleaning Brackets</h1>
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
            function removeOuterMatchingBrackets(str)
            {
                let s = str.trim();
                console.log('Input:', s);
                while(s.startsWith('(') && s.endsWith(')'))
                {
                    let parenCount = 0;
                    let isWrapped = true;

                    for(let i = 0; i < s.length - 1; i++)
                    {

                        if (s[i] === '(') parenCount++;
                        else if (s[i] === ')') parenCount--;

                        console.log('Index:', i, 'Character:', s[i], 'Balance:', parenCount);


                        if(parenCount === 0)
                        {
                            console.log('Balance reached 0 at index:', i, '- NOT a single unit');
                            isWrapped = false;
                            break;
                        }
                    }

                    console.log('isWrapped:', isWrapped);

                    if (isWrapped) {

                        s = s.slice(1, -1).trim();
                        console.log('After removing brackets:', s);
                    } else {
                        console.log('Breaking loop - not a single unit');
                        break;
                    }
                }
                console.log('Final result:', s);
                return s;
            }

            function processStrings()
            {
                const input = document.getElementById('input').value;
                const lines = input.split('\n');
                // process each line
                const processed = lines.map(line => removeOuterMatchingBrackets(line));
                // display output
                document.getElementById('output').value = processed.join('\n');
            }
        </script>
    </body>
</html>