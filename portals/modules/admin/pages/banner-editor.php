<?php $bannerId = trim((string) ($_GET['id'] ?? '')); ?>
<a class="ac-record-back" href="/conscious/admin/banners"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to banners</a>
<div class="ac-callout info mt-3" id="ah-editor-message" role="status"><?= $bannerId === '' ? 'Complete the banner details below.' : 'Loading banner details.' ?></div>
<form class="ac-record-layout mt-3" id="ah-editor-form" data-record-id="<?= htmlspecialchars($bannerId, ENT_QUOTES, 'UTF-8') ?>">
  <section class="ac-card ac-record-primary">
    <div class="ac-head"><div><span class="ac-kicker">HOME HERO</span><h2><?= $bannerId === '' ? 'Create banner' : 'Edit banner' ?></h2></div></div>
    <div class="ac-body"><div class="ac-form-grid">
      <div class="ac-field full"><label for="ah-image">Banner image <?= $bannerId === '' ? '*' : '' ?></label><input class="ac-input" id="ah-image" name="image" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP · up to 6 MB · minimum 900 × 400 px</small><input type="hidden" name="image_url"><img id="ah-preview" class="ah-preview" alt="Selected banner preview" hidden></div>
      <div class="ac-field full"><label for="ah-alt">Image description *</label><input class="ac-input" id="ah-alt" name="image_alt" required maxlength="180" placeholder="Example: Indian artisan shaping a ceramic bowl"></div>
      <div class="ac-field"><label for="ah-eyebrow">Subheading *</label><input class="ac-input" id="ah-eyebrow" name="eyebrow" required maxlength="90" placeholder="MADE BY INDIAN HANDS"></div>
      <div class="ac-field"><label for="ah-title">Title *</label><input class="ac-input" id="ah-title" name="title" required maxlength="150" placeholder="Crafted with care. Chosen to last."></div>
      <div class="ac-field full"><label for="ah-description">Paragraph *</label><textarea class="ac-textarea" id="ah-description" name="description" required maxlength="360" rows="4" placeholder="A short message about this collection or campaign"></textarea></div>
      <div class="ac-field"><label for="ah-button-label">Button text *</label><input class="ac-input" id="ah-button-label" name="button_label" required maxlength="45" placeholder="Shop the collection"></div>
      <div class="ac-field"><label for="ah-button-url">Button link *</label><input class="ac-input" id="ah-button-url" name="button_url" required maxlength="500" placeholder="/shop"></div>
    </div></div>
  </section>
  <aside class="ac-card ac-record-aside">
    <div class="ac-head"><h2>Publishing</h2></div>
    <div class="ac-body"><div class="ac-field"><label for="ah-order">Display order</label><input class="ac-input" id="ah-order" name="sort_order" type="number" min="0" max="999" value="100" required></div><label class="ac-check mt-3"><input name="is_active" type="checkbox" checked> Show this banner on the home page</label><div class="ac-form-actions"><button class="ac-btn primary" type="submit" id="ah-save">Save banner</button><a class="ac-btn" href="/conscious/admin/banners">Cancel</a></div></div>
  </aside>
</form>
