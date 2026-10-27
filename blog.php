<?php
declare(strict_types=1);
require_once __DIR__.'/api/lib/helpers.php';
$stmt=get_db()->query('SELECT slug,title,content_json,published_at FROM cms_entries WHERE entry_type="blog" AND status="published" AND (starts_at IS NULL OR starts_at<=UTC_TIMESTAMP()) AND (ends_at IS NULL OR ends_at>=UTC_TIMESTAMP()) ORDER BY COALESCE(published_at,created_at) DESC,created_at DESC');
$posts=[];
foreach($stmt->fetchAll() as $row){$content=json_decode((string)$row['content_json'],true);$posts[]=['slug'=>$row['slug'],'title'=>$row['title'],'published_at'=>$row['published_at'],'content'=>is_array($content)?$content:[]];}
function blog_e(string $value):string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#4B572C">
  <meta name="description" content="Stories about Indian craft, conscious living and the people behind thoughtful products."><title>Stories — Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/conscious/assets/css/bharatcraft.css"><link rel="stylesheet" href="/conscious/assets/css/blog-editorial.css?v=20261026-1">
</head>
<body>
<div id="bc-preloader" role="status" aria-label="Loading Conscious India"><img src="/conscious/assets/images/symbol.png" alt="" class="bc-preloader-symbol" width="90" height="90"><div class="bc-preloader-brand">Conscious India</div><div class="bc-preloader-chakra"></div></div>
<?php require __DIR__.'/includes/public-header.php'; ?>
<main id="main-content">
  <section class="ci-blog-hero"><div class="container ci-blog-hero-grid">
    <div class="ci-blog-hero-copy"><p class="ci-eyebrow">Stories worth carrying forward</p><h1>Ideas, hands and places shaping a <em>more conscious India.</em></h1><p>Meet the makers, materials and everyday choices behind a kinder way to live.</p></div>
    <?php if($posts): $featured=$posts[0];$fc=$featured['content']; ?>
    <a class="ci-featured-story" href="/conscious/blog-post?slug=<?=blog_e($featured['slug'])?>"><img src="<?=blog_e((string)($fc['hero_image']??'/conscious/assets/images/about/artisan-weaving-natural-basket.jpg'))?>" alt="<?=blog_e((string)($fc['hero_alt']??$featured['title']))?>"><span class="ci-featured-overlay"></span><span class="ci-featured-content"><small>Featured · <?=blog_e((string)($fc['category']??'Conscious living'))?></small><strong><?=blog_e($featured['title'])?></strong><span>Read the story <i class="fa-solid fa-arrow-right"></i></span></span></a>
    <?php endif; ?>
  </div></section>
  <section class="ci-blog-index"><div class="container">
    <header class="ci-section-heading"><div><p class="ci-eyebrow">The conscious journal</p><h2>Latest stories</h2></div><p>Original perspectives on craft, community, heritage and living with intention.</p></header>
    <div class="ci-filter-row" role="group" aria-label="Filter stories"><button class="is-active" type="button" data-blog-filter="all">All stories</button><button type="button" data-blog-filter="craft">Craft &amp; culture</button><button type="button" data-blog-filter="sustainability">Sustainability</button><button type="button" data-blog-filter="makers">Makers</button><button type="button" data-blog-filter="living">Conscious living</button></div>
    <?php if(!$posts): ?><div class="ci-blog-empty"><h2>Stories are being prepared.</h2><p>Please visit again soon.</p></div><?php else: ?>
    <div class="ci-story-grid" id="ci-story-grid">
      <?php foreach($posts as $index=>$post):$c=$post['content'];$category=(string)($c['category']??'Conscious living'); ?>
      <article class="ci-story-card<?= $index===0?' ci-story-card-wide':'' ?>" data-blog-category="<?=blog_e((string)($c['category_key']??'living'))?>">
        <a class="ci-story-image" href="/conscious/blog-post?slug=<?=blog_e($post['slug'])?>"><img src="<?=blog_e((string)($c['hero_image']??'/conscious/assets/images/about/eco-friendly-indian-crafts.jpg'))?>" alt="<?=blog_e((string)($c['hero_alt']??$post['title']))?>" loading="<?= $index<2?'eager':'lazy' ?>"><span><?=str_pad((string)($index+1),2,'0',STR_PAD_LEFT)?></span></a>
        <div class="ci-story-copy"><div class="ci-story-meta"><span><?=blog_e($category)?></span><time><?=blog_e(date('d M Y',strtotime((string)$post['published_at'])))?></time><span><?=blog_e((string)($c['reading_minutes']??'5'))?> min read</span></div><h3><a href="/conscious/blog-post?slug=<?=blog_e($post['slug'])?>"><?=blog_e($post['title'])?></a></h3><p><?=blog_e((string)($c['excerpt']??''))?></p><a class="ci-read-link" href="/conscious/blog-post?slug=<?=blog_e($post['slug'])?>">Continue reading <i class="fa-solid fa-arrow-right"></i></a></div>
      </article>
      <?php endforeach; ?>
    </div><?php endif; ?>
  </div></section>
  <section class="ci-journal-note"><div class="container ci-journal-note-inner"><p class="ci-eyebrow">A note from us</p><h2>Every object has a place, a person and a story.</h2><p>We tell those stories so that buying becomes less about more—and more about meaning.</p><a href="/conscious/artisans">Meet the makers <i class="fa-solid fa-arrow-right"></i></a></div></section>
</main>
<?php require __DIR__.'/includes/public-footer.php'; ?>
<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top"><i class="fa-solid fa-chevron-up"></i></button>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="/conscious/assets/js/bharatcraft.js?v=20261017-1"></script><script src="/conscious/assets/js/blog-editorial.js?v=20261026-1"></script>
</body></html>
