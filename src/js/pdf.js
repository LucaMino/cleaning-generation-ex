// function to generate PDF asynchronously with polling
function generatePDF()
{
    // retrieve the string to be processed
    const content = document.getElementById('output').value;
    const downloadLinkDiv = document.getElementById('download-link');

    // check if content is valid
    if(!content || content.trim() === '')
    {
        alert('Please process the strings first.');
        return;
    }

    // convert each line into an array
    const lines = content.split('\n').map(line => line.trim()).filter(line => line !== '');

    downloadLinkDiv.innerHTML = 'Waiting for PDF generation...';

    // send request to generate PDF
    fetch('api/generate_pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: lines })
    })
    .then(response => {
        if(!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if(data.file_id)
        {
            // save the file_id in localStorage
            localStorage.setItem('pending_pdf', data.file_id);
            // start polling for status
            checkStatus(data.file_id);
        } else {
            throw new Error(data.message || 'Error, please try again.');
        }
    })
    .catch(error => {
        downloadLinkDiv.innerHTML = 'Error generating PDF.';
    });
}

function checkStatus(fileId, isLive = true) {
    const downloadLinkDiv = document.getElementById('download-link');
    let attempts = 0;
    const maxAttempts = 20;

    const interval = setInterval(() => {
        attempts++;
        // check for max attempts to avoid infinite polling
        if(attempts > maxAttempts)
        {
            clearInterval(interval);
            downloadLinkDiv.innerHTML = 'Timeout while waiting for PDF generation.';
            localStorage.removeItem('pending_pdf');
            return;
        }
        // perform status check api
        fetch(`api/check_status.php?id=${fileId}`)
            .then(response => {
                if(!response.ok) {
                    clearInterval(interval);
                    downloadLinkDiv.innerHTML = 'Error checking PDF status.';
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                switch(data.status)
                {
                    case 'completed':
                        clearInterval(interval);
                        downloadLinkDiv.innerHTML = `✅ PDF Ready! <a href="${data.download_url}" target="_blank">Download PDF</a>`;
                        break;
                    case 'processing':
                        downloadLinkDiv.innerHTML = isLive ? 'Waiting for PDF generation...' : 'Retrieving previous PDF...';
                        break;
                    case 'error':
                        clearInterval(interval);
                        downloadLinkDiv.innerHTML = 'Ops, error during PDF generation.';
                        localStorage.removeItem('pending_pdf');
                        break;
                }
            })
            .catch(e => {
                console.error(e);
            });
    }, 2000);
}

// check for pending file and resume polling
function initPendingFile()
{
    const pendingFileId = localStorage.getItem('pending_pdf');

    if(pendingFileId)
    {
        const downloadLinkDiv = document.getElementById('download-link');
        downloadLinkDiv.innerHTML = 'Retrieving previous PDF...';
        checkStatus(pendingFileId, false);
    }
}

// initialize on page load, detect pending file
document.addEventListener('DOMContentLoaded', initPendingFile);