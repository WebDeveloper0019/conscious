<?php
$setupDb=get_db();
$appQuery=$setupDb->prepare('SELECT id,reference,status,decision_reason,submitted_at FROM vendor_applications WHERE vendor_id=:id ORDER BY version DESC LIMIT 1');
$appQuery->execute(['id'=>$vendorView['id']]);
$setupApplication=$appQuery->fetch()?:[];
$setupSections=[];
if(!empty($setupApplication['id'])){
    $sectionQuery=$setupDb->prepare('SELECT section_code,is_complete FROM vendor_onboarding_sections WHERE application_id=:id');
    $sectionQuery->execute(['id'=>$setupApplication['id']]);
    foreach($sectionQuery->fetchAll() as $section){$setupSections[$section['section_code']]=(bool)$section['is_complete'];}
}
$productQuery=$setupDb->prepare('SELECT status,COUNT(*) AS total FROM products WHERE vendor_id=:id GROUP BY status');
$productQuery->execute(['id'=>$vendorView['id']]);
$productCounts=[];
foreach($productQuery->fetchAll() as $row){$productCounts[$row['status']]=(int)$row['total'];}
$draftCount=($productCounts['draft']??0)+($productCounts['changes_requested']??0)+($productCounts['rejected']??0);
$requiredDocs=$vendorView['vendor_type']==='artisan'?['artisan_identity','artisan_craft_proof','artisan_address_proof','pan_file']:['brand_registration_proof','brand_identity','brand_authorization','pan_file'];
$documentQuery=$setupDb->prepare('SELECT d.document_type,COUNT(*) AS total FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.application_id=:id AND f.deleted_at IS NULL AND d.verification_status<>"rejected" GROUP BY d.document_type');
$documentQuery->execute(['id'=>$setupApplication['id']??'']);
$documentCounts=[];foreach($documentQuery->fetchAll() as $row){$documentCounts[$row['document_type']]=(int)$row['total'];}
$documentsReady=true;foreach($requiredDocs as $type){if(($documentCounts[$type]??0)<($type==='product_sample'?3:1)){$documentsReady=false;break;}}
$steps=[
    ['profile','Seller profile','fa-store','Who you are and what you make.'],
    ['verification','Identity proof','fa-id-card','Add documents for admin review.'],
];
$completeCount=1;
foreach($steps as $step){if(!empty($setupSections[$step[0]]))$completeCount++;}
$nextStep=1;
foreach($steps as $index=>$step){if(empty($setupSections[$step[0]])){$nextStep=$index+1;break;}$nextStep=$index+2;}
$completeCount+=(int)$documentsReady;
$percent=(int)round($completeCount/4*100);
$applicationStatus=(string)($setupApplication['status']??'draft');
$canEdit=in_array($applicationStatus,['draft','changes_requested'],true);
$reviewing=in_array($applicationStatus,['submitted','under_review'],true);
$h=static fn($value)=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
?>
<section class="vs-hero" aria-labelledby="vs-title">
  <div class="vs-hero-copy">
    <span class="vs-kicker"><i class="fa-solid fa-seedling" aria-hidden="true"></i> SELLER SETUP</span>
    <h2 id="vs-title">Welcome, <?= $h($vendorOwner) ?>.</h2>
    <p>Build your store at your pace. You can start product drafts now.</p>
    <div class="vs-progress-label"><strong><?= $completeCount ?> of 4 essentials ready</strong><span><?= $percent ?>%</span></div>
    <div class="vs-progress" role="progressbar" aria-label="Seller setup progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percent ?>"><span style="width:<?= $percent ?>%"></span></div>
  </div>
  <div class="vs-hero-icon" aria-hidden="true"><i class="fa-solid fa-store"></i><span><i class="fa-solid fa-check"></i></span></div>
</section>
<?php if($applicationStatus==='changes_requested'): ?>
  <div class="vs-banner vs-banner-attention" role="status"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><div><strong>Changes requested</strong><p><?= $h($setupApplication['decision_reason']?:'Review the sections below, then resubmit your application.') ?></p></div></div>
<?php elseif($reviewing): ?>
  <div class="vs-banner" role="status"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i><div><strong>Application under review</strong><p>Reference <?= $h($setupApplication['reference']??'') ?>. We will email you when a decision is made. You can keep preparing product drafts.</p></div></div>
<?php endif; ?>
<div class="vs-section-heading"><div><span class="vs-kicker">GET READY TO SELL</span><h2>Your setup</h2></div><span class="vs-count"><?= $percent ?>% complete</span></div>
<div class="vs-cards">
  <article class="vs-card is-complete"><span class="vs-card-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span><div class="vs-card-body"><h3>Email verified</h3><p>Your account is ready.</p></div><span class="vs-pill done"><i class="fa-solid fa-check" aria-hidden="true"></i> Done</span></article>
  <?php foreach($steps as $index=>$step): $done=!empty($setupSections[$step[0]]); $available=$canEdit&&($done||$nextStep===$index+1); ?>
  <article class="vs-card<?= $done?' is-complete':'' ?>">
    <span class="vs-card-icon"><i class="fa-solid <?= $h($step[2]) ?>" aria-hidden="true"></i></span>
    <div class="vs-card-body"><h3><?= $h($step[1]) ?></h3><p><?= $h($step[3]) ?></p></div>
    <?php if($available): ?><a class="vs-card-action" href="/conscious/partner/onboarding?step=<?= $index+1 ?>" aria-label="<?= $done?'Edit':'Start' ?> <?= $h($step[1]) ?>"><?= $done?'Edit':'Start' ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    <?php else: ?><span class="vs-pill <?= $done?'done':'locked' ?>"><i class="fa-solid <?= $done?'fa-check':'fa-lock' ?>" aria-hidden="true"></i> <?= $done?'Done':($canEdit?'Complete previous step':'In review') ?></span><?php endif; ?>
  </article>
  <?php endforeach; ?>
  <article class="vs-card<?= $documentsReady?' is-complete':'' ?>"><span class="vs-card-icon"><i class="fa-solid fa-file-shield" aria-hidden="true"></i></span><div class="vs-card-body"><h3>Verification documents</h3><p>Upload these during identity setup.</p></div><?php if($documentsReady): ?><span class="vs-pill done"><i class="fa-solid fa-check" aria-hidden="true"></i> Done</span><?php else: ?><span class="vs-pill locked"><i class="fa-solid fa-lock" aria-hidden="true"></i> Included in identity setup</span><?php endif; ?></article>
</div>
<div class="vs-next">
  <div class="vs-next-copy"><span class="vs-card-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span><div><span class="vs-kicker">NEXT STEP</span><h2>Prepare your products</h2><p><?= $draftCount ?> draft<?= $draftCount===1?'':'s' ?> saved. Buyers will see a product only after your store and its listing are approved.</p></div></div>
  <a class="vc-btn primary" href="/conscious/partner/catalog#add-product"><i class="fa-solid fa-plus" aria-hidden="true"></i> Create product draft</a>
</div>
<?php if($canEdit): ?>
<div class="vs-footer-action"><span><i class="fa-solid fa-lock" aria-hidden="true"></i> Complete your profile and identity proof to submit.</span><?php if($nextStep===3&&$documentsReady): ?><a class="vc-btn" href="/conscious/partner/onboarding?step=5">Review application <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php else: ?><span class="vs-pill locked"><i class="fa-solid fa-lock" aria-hidden="true"></i> Review unlocks after identity setup</span><?php endif; ?></div>
<?php endif; ?>
