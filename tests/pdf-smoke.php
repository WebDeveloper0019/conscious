<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require_once __DIR__.'/../api/lib/invoice-pdf.php';
require_once __DIR__.'/../api/lib/credit-note-pdf.php';
if(APP_ENV!=='local'){fwrite(STDERR,"Local test only.\n");exit(1);}
try{
    $invoice=['number'=>'QA-INVOICE','orderNumber'=>'QA-ORDER','issuedAt'=>'2026-10-20 10:00:00',
        'seller'=>['name'=>'Conscious Indi QA'],
        'buyer'=>['name'=>'Purchase test','address'=>'Test address, Delhi'],
        'items'=>[], 'totals'=>['grandTotal'=>100,'taxableValue'=>100,'amountInWords'=>'One hundred rupees only']];
    $documents=['invoice'=>invoice_render_pdf($invoice),'credit-note'=>credit_note_render_pdf($invoice)];
    foreach($documents as $name=>$bytes){if(!str_starts_with($bytes,'%PDF-')||strlen($bytes)<1000)throw new RuntimeException($name.' did not generate a valid PDF.');echo 'PASS '.$name.' PDF ('.strlen($bytes)." bytes)\n";}
}catch(Throwable $error){fwrite(STDERR,$error->getMessage()."\n");exit(1);}
