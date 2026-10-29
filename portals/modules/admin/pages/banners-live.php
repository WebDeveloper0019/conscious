<section class="ac-card ah-manager" aria-labelledby="ah-heading">
  <div class="ac-head"><div><h2 id="ah-heading">Home page banner slides</h2><p>Each published slide has its own image, message and button. Drag is not required: set a lower display number to show a slide earlier.</p></div><button class="ac-btn primary" type="button" id="ah-add"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add banner</button></div>
  <div class="ac-body"><p id="ah-message" role="status">Loading banners…</p><div id="ah-list" class="ah-list" aria-live="polite"></div><p class="ah-help">Recommended image: 1600 × 720 px or wider, with the important subject on the right and room for text on the left. Only active banners appear on the public home page.</p></div>
</section>
<dialog class="ac-dialog ah-dialog" id="ah-dialog" aria-labelledby="ah-dialog-title">
  <div class="ac-dialog-head"><h2 id="ah-dialog-title">Add banner</h2><button class="ac-btn small" type="button" id="ah-close">Close</button></div>
  <form class="ac-body" id="ah-form">
    <input type="hidden" name="id">
    <p id="ah-form-message" class="ah-form-message" role="alert" hidden></p>
    <div class="ac-form-grid">
      <div class="ac-field full"><label for="ah-image">Banner image *</label><input class="ac-input" id="ah-image" name="image" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP · up to 6 MB · minimum 900 × 400 px</small><input type="hidden" name="image_url"><img id="ah-preview" class="ah-preview" alt="Selected banner preview" hidden></div>
      <div class="ac-field full"><label for="ah-alt">Image description *</label><input class="ac-input" id="ah-alt" name="image_alt" required maxlength="180" placeholder="Example: Indian artisan shaping a ceramic bowl"></div>
      <div class="ac-field"><label for="ah-eyebrow">Subheading *</label><input class="ac-input" id="ah-eyebrow" name="eyebrow" required maxlength="90" placeholder="MADE BY INDIAN HANDS"></div>
      <div class="ac-field"><label for="ah-title">Title *</label><input class="ac-input" id="ah-title" name="title" required maxlength="150" placeholder="Crafted with care. Chosen to last."></div>
      <div class="ac-field full"><label for="ah-description">Paragraph *</label><textarea class="ac-textarea" id="ah-description" name="description" required maxlength="360" rows="3" placeholder="A short message about this collection or campaign"></textarea></div>
      <div class="ac-field"><label for="ah-button-label">Button text *</label><input class="ac-input" id="ah-button-label" name="button_label" required maxlength="45" placeholder="Shop the collection"></div>
      <div class="ac-field"><label for="ah-button-url">Button link *</label><input class="ac-input" id="ah-button-url" name="button_url" required maxlength="500" placeholder="/shop"></div>
      <div class="ac-field"><label for="ah-order">Display order</label><input class="ac-input" id="ah-order" name="sort_order" type="number" min="0" max="999" value="100" required></div>
      <div class="ac-field ah-check"><label><input name="is_active" type="checkbox" checked> Show this banner on the home page</label></div>
    </div>
    <div class="ac-form-actions"><button class="ac-btn primary" type="submit" id="ah-save">Save banner</button><button class="ac-btn" type="button" id="ah-cancel">Cancel</button></div>
  </form>
</dialog>
