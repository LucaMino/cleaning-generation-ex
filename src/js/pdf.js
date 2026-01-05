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



    downloadLinkDiv.innerHTML = 'Richiesta presa in carico...';



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
            throw new Error(data.message || 'Errore durante l\'avvio.');
        }
    })
    .catch(error => {
        downloadLinkDiv.innerHTML = `❌ Errore: ${error.message}`;
    });
}

function checkStatus(fileId) {
    const downloadLinkDiv = document.getElementById('download-link');

    const interval = setInterval(() => {
        fetch(`api/check_status.php?id=${fileId}`)
            .then(response => {
                if(!response.ok) {
                    clearInterval(interval);
                    downloadLinkDiv.innerHTML = '';
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                switch(data.status)
                {
                    case 'completed':
                        clearInterval(interval);
                        downloadLinkDiv.innerHTML = `✅ PDF Pronto! <a href="${data.download_url}" target="_blank">Scarica PDF</a>`;
                        localStorage.removeItem('pending_pdf');
                        break;
                    case 'processing':
                        downloadLinkDiv.innerHTML = "⚡ Generazione PDF in corso (spirale)...";
                        break;
                    case 'error':
                        clearInterval(interval);
                        downloadLinkDiv.innerHTML = 'Ops, error during PDF generation.';
                        localStorage.removeItem('pending_pdf');
                        break;
                }
            })
            .catch(e => {
                console.error("Errore polling:", e);
            });
    }, 2000);
}

// check for pending file and resume polling
function initPendingFile()
{
    const pendingJobId = localStorage.getItem('pending_pdf');

    if(pendingJobId)
    {
        const downloadLinkDiv = document.getElementById('download-link');
        downloadLinkDiv.innerHTML = 'Retrieving previous PDF...';
        checkStatus(pendingJobId);
    }
}

// initialize on page load, detect pending file
document.addEventListener('DOMContentLoaded', initPendingFile);