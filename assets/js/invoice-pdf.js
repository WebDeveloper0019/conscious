(function () {
    'use strict';

    const SERVER = new URL('../api/user/invoice-pdf.php', window.location.href).href;

    /**
     * Download or preview an invoice PDF.
     *   identifier — invoice number or order number
     *   mode       — 'number' (default) or 'order'
     *   options    — { preview: true } to open in a new tab
     */
    async function downloadInvoice(identifier, mode, options) {
        options = options || {};
        if (!identifier) {
            alert('Invoice identifier missing.');
            return;
        }

        const param = mode === 'order'
            ? 'orderNumber=' + encodeURIComponent(identifier)
            : 'invoice_number=' + encodeURIComponent(identifier);
        const modeParam = options.preview ? '&mode=preview' : '&mode=download';
        const url = SERVER + '?' + param + modeParam;

        if (options.preview) {
            // Open in a new tab — browser will render the PDF
            window.open(url, '_blank');
            return;
        }

        try {
            const res = await fetch(url, { credentials: 'include' });
            if (!res.ok) {
                const err = await res.json().catch(() => ({ message: 'Download failed.' }));
                throw new Error(err.message || 'Download failed.');
            }
            const blob = await res.blob();

            // Extract filename from Content-Disposition if present
            const cd = res.headers.get('Content-Disposition') || '';
            const match = cd.match(/filename="?([^";]+)"?/);
            const filename = match ? match[1] : 'invoice.pdf';

            const objectUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = objectUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);

        } catch (err) {
            console.error('Invoice download failed:', err);
            alert('Could not download invoice: ' + (err.message || 'unknown error'));
        }
    }

    window.downloadInvoice = downloadInvoice;
})();