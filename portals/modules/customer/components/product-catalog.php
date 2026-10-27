<?php
$customerProducts = [
    ['blue-pottery-vase','Blue Pottery Floral Vase','Jaipur Pottery House','Jaipur, Rajasthan',4200,4990,'4.9',87,'GI-authorized craft','blue-pottery-vase.jpg'],
    ['kundan-necklace','Emerald Kundan Necklace','Shilpa Crafts','Jaipur, Rajasthan',3499,4999,'4.9',126,'Verified artisan','kundan-necklace.jpg'],
    ['kantha-cushion','Hand-stitched Kantha Cushion','Kantha Collective','Kolkata, West Bengal',1850,2190,'4.8',64,'Upcycled textile','kantha-cushion.jpg'],
    ['dhokra-musician','Dhokra Musician Figurine','Bastar Metal Arts','Kondagaon, Chhattisgarh',3250,3890,'4.7',42,'Lost-wax handmade','dhokra-musician.jpg'],
    ['pashmina-shawl','Sage Handwoven Pashmina','Kashmir Loom House','Srinagar, Jammu & Kashmir',6890,7990,'4.9',93,'Handwoven fibre','pashmina-shawl.jpg'],
    ['warli-wall-art','Warli Village Wall Art','Warli Roots Studio','Palghar, Maharashtra',2480,2990,'4.9',51,'Natural pigment','warli-wall-art.jpg'],
    ['sandalwood-box','Carved Sandalwood Keepsake Box','Mysore Wood Studio','Mysuru, Karnataka',2790,3290,'4.8',38,'Hand-carved wood','sandalwood-box.jpg'],
    ['madhubani-painting','Madhubani Tree of Life Painting','Mithila Arts Collective','Madhubani, Bihar',3890,4590,'4.9',72,'Natural pigment','madhubani-painting.jpg'],
];

function customerProductBySlug(array $products, string $slug): array {
    foreach ($products as $product) if ($product[0] === $slug) return $product;
    return $products[0];
}

function renderCustomerProductCard(array $product, string $extraClass=''): void {
    [$slug,$name,$vendor,$location,$price,$mrp,$rating,$reviews,$claim,$image] = $product;
    $discount = (int) round((1 - ($price / $mrp)) * 100);
    ?>
    <article class="cp-product-card <?=htmlspecialchars($extraClass,ENT_QUOTES,'UTF-8')?>" data-product-card>
        <a class="cp-product-photo" href="product.php?product=<?=urlencode($slug)?>"><img src="assets/images/products/<?=htmlspecialchars($image,ENT_QUOTES,'UTF-8')?>" alt="<?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?>" loading="lazy"><span><i class="fa-solid fa-certificate"></i> <?=htmlspecialchars($claim,ENT_QUOTES,'UTF-8')?></span></a>
        <button class="cp-save" type="button" aria-label="Save <?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?>" aria-pressed="false" data-save-product><i class="fa-regular fa-heart"></i></button>
        <div class="cp-product-copy"><p><?=htmlspecialchars($vendor,ENT_QUOTES,'UTF-8')?></p><a href="product.php?product=<?=urlencode($slug)?>"><h3><?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?></h3></a><small><i class="fa-solid fa-location-dot"></i> <?=htmlspecialchars($location,ENT_QUOTES,'UTF-8')?></small><div class="cp-rating"><i class="fa-solid fa-star"></i><b><?=htmlspecialchars($rating,ENT_QUOTES,'UTF-8')?></b><span>(<?=htmlspecialchars($reviews,ENT_QUOTES,'UTF-8')?>)</span></div><footer><div><strong>₹<?=number_format($price)?></strong><del>₹<?=number_format($mrp)?></del><em><?=$discount?>% off</em></div><button type="button" data-demo-action="Add <?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?> to cart" aria-label="Add <?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?> to cart"><i class="fa-solid fa-bag-shopping"></i></button></footer></div>
    </article>
    <?php
}
