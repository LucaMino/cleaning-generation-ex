// retrieves input strings, processes them and displays output
function processStrings(module)
{
    const input = document.getElementById('input').value;

    if(!input.trim())
    {
        alert('Input field is required.');
        return;
    }

    const lines = input.split('\n');
    // process each line
    const processed = lines.map(line => processLine(line));
    // display output
    document.getElementById('output').value = processed.join('\n');
    // clear any pending PDF status
    const downloadLinkDiv = document.getElementById('download-link');
    const generateBtn = document.getElementById('generate-pdf-btn');
    downloadLinkDiv.innerHTML = '';
    generateBtn.disabled = false;
    // remove file id for this module
    removePendingPdf(module);
}