// retrieves input strings, processes them and displays output
function processStrings()
{
    const input = document.getElementById('input').value;
    const lines = input.split('\n');
    // process each line
    const processed = lines.map(line => processLine(line));
    // display output
    document.getElementById('output').value = processed.join('\n');
    // clear any pending PDF status
    const downloadLinkDiv = document.getElementById('download-link');
    downloadLinkDiv.innerHTML = '';
    localStorage.removeItem('pending_pdf');
}