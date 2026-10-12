(function () {
    'use strict';

    const API = new URL('../api/user/invoice.php', window.location.href).href;

    async function ensureJsPdf() {
        if (window.jspdf && window.jspdf.jsPDF) return window.jspdf.jsPDF;
        await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
        return window.jspdf.jsPDF;
    }

    function rs(n) {
        return '₹' + Number(n || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        try {
            const d = new Date(String(iso).replace(' ', 'T') + (String(iso).includes('Z') ? '' : 'Z'));
            return Number.isNaN(d.getTime()) ? iso
                : d.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        } catch { return iso; }
    }

    async function fetchInvoice(invoiceNumberOrOrderNumber, mode) {
        const param = mode === 'order'
            ? 'orderNumber=' + encodeURIComponent(invoiceNumberOrOrderNumber)
            : 'invoice_number=' + encodeURIComponent(invoiceNumberOrOrderNumber);

        const res  = await fetch(API + '?' + param, { credentials: 'include' });
        const data = await res.json();
        if (!data.success || !data.invoice) {
            throw new Error(data.message || 'Invoice not found.');
        }
        return data.invoice;
    }

    function buildPdf(inv) {
        const doc = new (window.jspdf.jsPDF)({ unit: 'mm', format: 'a4' });
        const W = 210;
        const M = 15;
        let y = M;

        // ── Header ──
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(20);
        doc.text('TAX INVOICE', W / 2, y, { align: 'center' });
        y += 8;

        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        if (inv.seller?.name)    { doc.text(inv.seller.name, M, y); y += 4; }
        if (inv.seller?.address) { doc.text(inv.seller.address, M, y); y += 4; }
        if (inv.seller?.gstin)   { doc.text('GSTIN: ' + inv.seller.gstin, M, y); y += 4; }
        if (inv.seller?.email)   { doc.text(inv.seller.email, M, y); y += 4; }

        let ry = M + 8;
        doc.setFont('helvetica', 'bold');
        doc.text('Invoice No:',  W - M - 42, ry);
        doc.text('Order No:',    W - M - 42, ry + 5);
        doc.text('Date:',        W - M - 42, ry + 10);
        doc.setFont('helvetica', 'normal');
        doc.text(String(inv.number || ''), W - M, ry,       { align: 'right' });
        doc.text(String(inv.orderNumber || ('#' + inv.orderId)), W - M, ry + 5, { align: 'right' });
        doc.text(fmtDate(inv.issuedAt), W - M, ry + 10, { align: 'right' });

        y = Math.max(y, ry + 15) + 4;

        // ── Bill To ──
        doc.setDrawColor(200);
        doc.line(M, y, W - M, y); y += 5;
        doc.setFont('helvetica', 'bold');
        doc.text('Bill To:', M, y); y += 5;
        doc.setFont('helvetica', 'normal');
        if (inv.buyer?.name)  { doc.text(inv.buyer.name, M, y); y += 4; }
        const b = inv.buyer?.billing || {};
        if (b.address_line1) { doc.text(b.address_line1, M, y); y += 4; }
        if (b.address_line2) { doc.text(b.address_line2, M, y); y += 4; }
        const cityLine = [b.city, b.state, b.pincode].filter(Boolean).join(', ');
        if (cityLine) { doc.text(cityLine, M, y); y += 4; }
        if (inv.buyer?.email) { doc.text(inv.buyer.email, M, y); y += 4; }
        if (inv.buyer?.phone) { doc.text('Ph: ' + inv.buyer.phone, M, y); y += 4; }

        y += 2;
        doc.line(M, y, W - M, y); y += 5;

        // ── Items table ──
        const cols = [
            { t: '#',        x: M,      w: 8,   a: 'left'  },
            { t: 'Item',     x: M + 8,  w: 70,  a: 'left'  },
            { t: 'Qty',      x: M + 78, w: 12,  a: 'right' },
            { t: 'Rate',     x: M + 90, w: 22,  a: 'right' },
            { t: 'Taxable',  x: M + 112,w: 25,  a: 'right' },
            { t: 'GST%',     x: M + 137,w: 15,  a: 'right' },
            { t: 'Tax',      x: M + 152,w: 22,  a: 'right' },
            { t: 'Total',    x: M + 174,w: 21,  a: 'right' },
        ];

        doc.setFont('helvetica', 'bold');
        doc.setFillColor(240, 240, 240);
        doc.rect(M, y - 4, W - 2 * M, 7, 'F');
        cols.forEach(c => doc.text(c.t, c.x + (c.a === 'right' ? c.w : 0), y, { align: c.a }));
        y += 6;

        doc.setFont('helvetica', 'normal');
        (inv.items || []).forEach((it, i) => {
            if (y > 260) { doc.addPage(); y = M; }
            const tax = (Number(it.cgst) || 0) + (Number(it.sgst) || 0) + (Number(it.igst) || 0);
            const row = [
                String(i + 1),
                String(it.name || '').substring(0, 40),
                String(it.quantity || 0),
                rs(it.unitPrice),
                rs(it.taxableValue),
                String(it.gstPercent || 0),
                rs(tax),
                rs(it.lineTotal),
            ];
            cols.forEach((c, j) => {
                doc.text(row[j], c.x + (c.a === 'right' ? c.w : 0), y, { align: c.a });
            });
            y += 5;
        });

        y += 2;
        doc.line(M, y, W - M, y); y += 6;

        // ── Totals ──
        const t = inv.totals || {};
        const totalsX = W - M - 70;
        doc.setFontSize(9);
        const rows = [
            ['Subtotal',       rs(t.subtotal)],
            ['Shipping',       rs(t.shipping)],
            ['Discount',       '-' + rs(t.discount)],
            ['Taxable Value',  rs(t.taxableValue)],
            ['CGST',           rs(t.cgst)],
            ['SGST',           rs(t.sgst)],
            ['IGST',           rs(t.igst)],
        ];
        rows.forEach(r => {
            doc.text(r[0], totalsX, y);
            doc.text(r[1], W - M, y, { align: 'right' });
            y += 5;
        });

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.text('Grand Total', totalsX, y);
        doc.text(rs(t.grandTotal), W - M, y, { align: 'right' });
        y += 8;

        doc.setFont('helvetica', 'italic');
        doc.setFontSize(9);
        doc.text('Amount in words: ' + (t.amountInWords || ''), M, y);
        y += 8;

        // ── Payment ──
        if (inv.payment) {
            doc.setFont('helvetica', 'normal');
            doc.text('Payment Method: ' + (inv.payment.provider || '—'), M, y); y += 4;
            doc.text('Payment Status: ' + (inv.payment.status   || '—'), M, y); y += 4;
            if (inv.payment.provider_payment_id) {
                doc.text('Txn ID: ' + inv.payment.provider_payment_id, M, y); y += 4;
            }
        }

        // ── Footer ──
        doc.setFont('helvetica', 'italic');
        doc.setFontSize(8);
        doc.text(
            'This is a computer-generated invoice. No signature required.',
            W / 2, 285, { align: 'center' }
        );

        return doc;
    }

    /**
     * downloadInvoice(identifier, mode, options)
     *   identifier — invoice number or order number
     *   mode       — 'number' (default) or 'order'
     *   options    — { preview: true }  opens PDF in a new tab instead of downloading
     */
    async function downloadInvoice(identifier, mode, options) {
        options = options || {};
        try {
            await ensureJsPdf();
            const inv = await fetchInvoice(identifier, mode);
            const doc = buildPdf(inv);

            if (options.preview) {
                const url = doc.output('bloburl');
                window.open(url, '_blank');
            } else {
                doc.save(String(inv.number || 'invoice').replace(/[\/\\]/g, '_') + '.pdf');
            }
        } catch (err) {
            console.error('Invoice PDF error:', err);
            alert('Could not generate invoice: ' + (err.message || 'unknown error'));
        }
    }

    window.downloadInvoice = downloadInvoice;
})();