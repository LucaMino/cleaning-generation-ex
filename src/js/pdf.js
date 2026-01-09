// function to generate PDF asynchronously with polling
function generatePDF(module)
{
    // retrieve the string to be processed
    const content = document.getElementById('output').value;
    const downloadLinkDiv = document.getElementById('download-link');
    const generateBtn = document.getElementById('generate-pdf-btn');

    // check if content is valid
    if(!content || !content.trim())
    {
        alert('Please process strings successfully before generating PDF.');
        return;
    }

    // validate module
    if(!module || !['pairs', 'brackets'].includes(module))
    {
        alert('Invalid module.');
        return;
    }

    // check if already generating
    if(generateBtn.disabled)
    {
        return;
    }

    // convert each line into an array
    const lines = content.split('\n').map(line => line.trim()).filter(line => line !== '');

    generateBtn.disabled = true;
    downloadLinkDiv.innerHTML = 'Waiting for PDF generation...';

    // send request to generate PDF
    fetch('api/generate_pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: lines, module: module })
    })
    .then(response => {
        if(!response.ok)
        {
            return response.json().then(data => {
                const error = new Error(data.error || 'Request failed');
                error.status = response.status;
                throw error;
            });
        }
        return response.json();
    })
    .then(data => {
        if(data.file_id)
        {
            // save module and file id to local storage
            savePendingPdf(module, data.file_id);
            // start polling for status
            checkStatus(data.file_id, module);
        } else {
            throw new Error(data.message || 'Error, please try again.');
        }
    })
    .catch(error => {
        generateBtn.disabled = false;
        downloadLinkDiv.innerHTML = error.status === 422 ? error.message : 'Error generating PDF.';
    });
}

function checkStatus(fileId, module, isLive = true) {
    const downloadLinkDiv = document.getElementById('download-link');
    const generateBtn = document.getElementById('generate-pdf-btn');
    let attempts = 0;
    const maxAttempts = 20;

    generateBtn.disabled = true;

    const interval = setInterval(() => {
        attempts++;
        // check for max attempts to avoid infinite polling
        if(attempts > maxAttempts)
        {
            clearInterval(interval);
            downloadLinkDiv.innerHTML = 'Timeout while waiting for PDF generation.';
            removePendingPdf(module);
            return;
        }
        // perform status check api
        fetch(`api/check_status.php?id=${fileId}&module=${module}`)
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
                        generateBtn.disabled = false;
                        removePendingPdf(module);
                        break;
                }
            })
            .catch(e => {
                generateBtn.disabled = false;
                console.error(e);
            });
    }, 2000);
}

// check for pending file and resume polling
function initPendingFile()
{
    const downloadLinkDiv = document.getElementById('download-link');
    const module = downloadLinkDiv.getAttribute('data-module');

    const pendingFileId = getPendingPdf(module);

    if(pendingFileId)
    {
        const downloadLinkDiv = document.getElementById('download-link');
        downloadLinkDiv.innerHTML = 'Retrieving previous PDF...';
        checkStatus(pendingFileId, module, false);
    }
}

// initialize on page load, detect pending file
document.addEventListener('DOMContentLoaded', initPendingFile);

/* local storage helpers for pending PDFs */

function savePendingPdf(module, fileId)
{
    let pendingPdfs = JSON.parse(localStorage.getItem('pending_pdfs') || '{}');
    pendingPdfs[module] = fileId;
    localStorage.setItem('pending_pdfs', JSON.stringify(pendingPdfs));
}

function getPendingPdf(module)
{
    let pendingPdfs = JSON.parse(localStorage.getItem('pending_pdfs') || '{}');
    return pendingPdfs[module] || null;
}

function removePendingPdf(module)
{
    let pendingPdfs = JSON.parse(localStorage.getItem('pending_pdfs') || '{}');
    delete pendingPdfs[module];
    localStorage.setItem('pending_pdfs', JSON.stringify(pendingPdfs));
}