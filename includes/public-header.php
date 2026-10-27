<?php /* Shared public storefront header. Dashboard layouts do not include this file. */ ?>
<link rel="stylesheet" href="/conscious/assets/css/site-motion.css?v=20261026-1">
<div class="bc-announce-bar" aria-label="Conscious India partner links">
  <div class="bc-announce-track container">
    <div class="bc-utility-inner">
      <span class="bc-utility-trust"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Conscious commerce · Verified brands &amp; artisans</span>
      <nav class="bc-utility-links" aria-label="Partner and experience links">
        <a href="/conscious/become-brand-partner">Sell With Us</a>
        <details class="bc-utility-menu"><summary>Join Us <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="bc-utility-menu-panel">
          <a href="/conscious/become-brand-partner">Conscious Brand</a>
          <a href="/conscious/become-artisan-partner">Artisan/SHG</a>
          <a href="/conscious/creators">Conscious Creator / Influencer</a>
        </div></details>
        <details class="bc-utility-menu"><summary>Register As <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="bc-utility-menu-panel">
          <a href="/conscious/portals/modules/vendor/auth/register.php?type=brand">Conscious Brand</a>
          <a href="/conscious/portals/modules/vendor/auth/register.php?type=artisan">Artisan</a>
          <a href="/conscious/portals/modules/influencer/auth/register.php">Conscious Creator</a>
        </div></details>
        <details class="bc-utility-menu"><summary>Book/Collab For <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="bc-utility-menu-panel">
          <a href="/conscious/events#event-stall-booking">Event Stall</a>
          <a href="/conscious/events#event-workshop-booking">Workshops</a>
          <a href="/conscious/creators">Conscious Creator / Influencer</a>
        </div></details>
        <a href="/conscious/corporate-gifting">Conscious Gifting</a>
      </nav>
    </div>
  </div>
</div>
<header class="bc-header" id="bc-header" role="banner">

  <!-- Top Row: Logo + Search + Actions -->
  <div class="bc-header-top">
    <div class="container">
      <div class="bc-header-top-inner">

        <!-- Logo -->
        <a href="/conscious/" class="bc-logo bc-logo-real" aria-label="Conscious India — Story-Led Commerce, Conscious Living Marketplace & Events Platform">
          <img
            src="/conscious/assets/images/logo-horizontal.png"
            alt="Conscious India"
            class="bc-logo-img bc-logo-horizontal"
            width="68"
            height="64"
            loading="eager"
            fetchpriority="high"
          />
        </a>

        <!-- Search Bar -->
        <div class="bc-search-wrap">
          <div class="bc-search-box" role="search">
            <select class="bc-search-cat" aria-label="Search category">
              <option value="all">All Categories</option>
              <option value="brands">Sustainable Brands</option>
              <option value="pottery">Pottery &amp; Ceramics</option>
              <option value="jewelry">Handmade Jewelry</option>
              <option value="wood">Wood Crafts</option>
              <option value="home">Home Decor</option>
              <option value="handloom">Handloom &amp; Textiles</option>
              <option value="paintings">Paintings</option>
              <option value="art">Traditional Art</option>
              <option value="fashion">Fashion Accessories</option>
              <option value="gifts">Gift Items</option>
              <option value="eco">Eco Friendly</option>
            </select>
            <input class="bc-search-input" type="search" id="bc-search-input"
              placeholder="Search products, crafts, makers and values"
              aria-label="Search products" autocomplete="off" />
            <button class="bc-search-btn" aria-label="Search">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
        </div>

        <!-- Header Actions -->
        <div class="bc-header-actions">
          <a href="/conscious/portals/modules/customer/wishlist.php" class="bc-header-action-link" aria-label="Wishlist">
            <i class="fa-regular fa-heart"></i>
            <span>Wishlist</span>
          </a>
          <a href="/conscious/portals/modules/customer/cart.php" class="bc-header-action-link" aria-label="Cart (0 items)">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="bc-action-badge" aria-label="0 items in cart">0</span>
            <span>Cart</span>
          </a>
          <a href="/conscious/portals/modules/customer/index.php" class="bc-header-action-link" aria-label="My Account">
            <i class="fa-regular fa-circle-user"></i>
            <span>Account</span>
          </a>
        </div>

      </div>
    </div>
  </div>

  <!-- Navigation Row -->
  <div class="bc-header-nav">
    <div class="container">
      <div class="bc-nav-inner">

        <nav aria-label="Main Navigation">
          <ul class="bc-nav-list">

            <!-- Marketplace (Mega Menu) -->
            <li class="bc-nav-item has-mega">
              <a href="/conscious/shop">
                <i class="fa-solid fa-grid-2" style="font-size:0.875rem;color:var(--saffron)"></i>
                Marketplace
                <i class="fa-solid fa-chevron-down" style="font-size:0.625rem;margin-left:2px;"></i>
              </a>
              <div class="bc-mega-menu" role="region" aria-label="Marketplace mega menu">
                <div class="bc-mega-title"><strong>Marketplace</strong><span>Shop by Product</span></div>
                <div class="bc-mega-grid">
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-shirt" aria-hidden="true"></i> Fashion &amp; Textiles</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Handloom">Handloom</a></li>
                      <li><a href="/conscious/shop?q=Sustainable%20Fashion">Sustainable Fashion</a></li>
                      <li><a href="/conscious/shop?q=Natural%20Fabrics">Natural Fabrics</a></li>
                      <li><a href="/conscious/shop?q=Khadi">Khadi</a></li>
                      <li><a href="/conscious/shop?q=Handwoven%20Sarees">Handwoven Sarees</a></li>
                      <li><a href="/conscious/shop?q=Apparel">Apparel</a></li>
                    </ul>
                    </div>
                    <div class="bc-mega-group"><h5><i class="fa-regular fa-gem" aria-hidden="true"></i> Jewellery &amp; Accessories</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Handmade%20Jewellery">Handmade Jewellery</a></li>
                      <li><a href="/conscious/shop?q=Silver%20Jewellery">Silver Jewellery</a></li>
                      <li><a href="/conscious/shop?q=Tribal%20Jewellery">Tribal Jewellery</a></li>
                      <li><a href="/conscious/shop?q=Kundan%20Meenakari">Kundan &amp; Meenakari</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-house" aria-hidden="true"></i> Home &amp; Living</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Home%20Decor">Home Décor</a></li>
                      <li><a href="/conscious/shop?q=Furniture">Furniture</a></li>
                      <li><a href="/conscious/shop?q=Lighting">Lighting</a></li>
                      <li><a href="/conscious/shop?q=Rugs%20Dhurries">Rugs &amp; Dhurries</a></li>
                      <li><a href="/conscious/shop?q=Kitchen%20Dining">Kitchen &amp; Dining</a></li>
                    </ul>
                    </div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-palette" aria-hidden="true"></i> Art &amp; Collectibles</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Paintings">Paintings</a></li>
                      <li><a href="/conscious/shop?q=Wall%20Art">Wall Art</a></li>
                      <li><a href="/conscious/shop?q=Sculptures">Sculptures</a></li>
                      <li><a href="/conscious/shop?q=Tribal%20Folk%20Art">Tribal &amp; Folk Art</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-spa" aria-hidden="true"></i> Beauty &amp; Wellness</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Natural%20Skincare">Natural Skincare</a></li>
                      <li><a href="/conscious/shop?q=Organic%20Beauty">Organic Beauty</a></li>
                      <li><a href="/conscious/shop?q=Haircare">Haircare</a></li>
                      <li><a href="/conscious/shop?q=Bath%20Body">Bath &amp; Body</a></li>
                      <li><a href="/conscious/shop?q=Aromatherapy">Aromatherapy</a></li>
                    </ul>
                    </div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-bowl-food" aria-hidden="true"></i> Food &amp; Beverages</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Organic%20Food">Organic Food</a></li>
                      <li><a href="/conscious/shop?q=Millet%20Products">Millet Products</a></li>
                      <li><a href="/conscious/shop?q=Healthy%20Snacks">Healthy Snacks</a></li>
                      <li><a href="/conscious/shop?q=Tea%20Coffee">Tea &amp; Coffee</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-child-reaching" aria-hidden="true"></i> Kids</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Eco-Friendly%20Toys">Eco-Friendly Toys</a></li>
                      <li><a href="/conscious/shop?q=Wooden%20Toys">Wooden Toys</a></li>
                      <li><a href="/conscious/shop?q=Sustainable%20Kids%20Clothing">Sustainable Clothing</a></li>
                      <li><a href="/conscious/shop?q=Books%20Stationery">Books &amp; Stationery</a></li>
                    </ul>
                    </div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-gift" aria-hidden="true"></i> Gifting</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Sustainable%20Gifts">Sustainable Gifts</a></li>
                      <li><a href="/conscious/shop?q=Artisan%20Gifts">Artisan Gifts</a></li>
                      <li><a href="/conscious/shop?q=Wedding%20Gifts">Wedding Gifts</a></li>
                      <li><a href="/conscious/corporate-gifting">Corporate Gifting</a></li>
                    </ul>
                    </div>
                  </div>
                </div>
                <div class="bc-mega-banner">
                  <div>
                    <div class="bc-mega-banner-text">
                      <span class="bc-mega-banner-label">Browse the complete marketplace</span>
                      🌱 New: Verified Sustainable Brands Directory
                      <span>Explore every product category from verified brands and artisans.</span>
                    </div>
                  </div>
                  <a href="/conscious/shop" class="bc-btn bc-btn-primary bc-btn-sm">Shop all products</a>
                </div>
              </div>
            </li>

            <!-- Conscious Shop (values-led discovery) -->
            <li class="bc-nav-item has-mega">
              <a href="/conscious/shop?q=Sustainable">
                Conscious Shop
                <i class="fa-solid fa-chevron-down" style="font-size:0.625rem;margin-left:2px;"></i>
              </a>
              <div class="bc-mega-menu bc-conscious-menu" role="region" aria-label="Conscious Shop mega menu">
                <div class="bc-mega-title"><strong>Conscious Shop</strong><span>Shop by Values</span></div>
                <div class="bc-mega-grid">
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-heart" aria-hidden="true"></i> Shop by Conscious Choice</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Sustainable">Sustainable</a></li>
                      <li><a href="/conscious/shop?q=Upcycled">Upcycled</a></li>
                      <li><a href="/conscious/shop?q=Organic">Organic</a></li>
                      <li><a href="/conscious/shop?q=Natural">Natural</a></li>
                      <li><a href="/conscious/shop?q=Vegan">Vegan</a></li>
                      <li><a href="/conscious/shop?q=Plastic%20Free">Plastic Free</a></li>
                      <li><a href="/conscious/shop?q=Handmade">Handmade</a></li>
                      <li><a href="/conscious/shop?q=Women-Led">Women-Led</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-seedling" aria-hidden="true"></i> Shop by Material</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Natural%20Fibres">Natural Fibres</a></li>
                      <li><a href="/conscious/shop?q=Bamboo">Bamboo</a></li>
                      <li><a href="/conscious/shop?q=Jute">Jute</a></li>
                      <li><a href="/conscious/shop?q=Hemp">Hemp</a></li>
                      <li><a href="/conscious/shop?q=Terracotta">Terracotta</a></li>
                      <li><a href="/conscious/shop?q=Recycled%20Materials">Recycled Materials</a></li>
                      <li><a href="/conscious/shop?q=Natural%20Dyes">Natural Dyes</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-leaf" aria-hidden="true"></i> Shop by Lifestyle</h5>
                    <ul>
                      <li><a href="/conscious/shop?q=Conscious%20Wardrobe">Conscious Wardrobe</a></li>
                      <li><a href="/conscious/shop?q=Conscious%20Home">Conscious Home</a></li>
                      <li><a href="/conscious/shop?q=Sustainable%20Beauty">Sustainable Beauty</a></li>
                      <li><a href="/conscious/shop?q=Mindful%20Living">Mindful Living</a></li>
                      <li><a href="/conscious/shop?q=Zero-Waste%20Living">Zero-Waste Living</a></li>
                      <li><a href="/conscious/shop?q=Sustainable%20Gifting">Sustainable Gifting</a></li>
                      <li><a href="/conscious/shop?q=Conscious%20Food">Conscious Food</a></li>
                    </ul>
                  </div></div>
                </div>
                <div class="bc-mega-banner">
                  <div class="bc-mega-banner-text"><span class="bc-mega-banner-label">Shop by what you believe in</span><span>Discover products through values, materials and mindful lifestyles.</span></div>
                  <a href="/conscious/shop?q=Sustainable" class="bc-btn bc-btn-primary bc-btn-sm">Explore conscious choices</a>
                </div>
              </div>
            </li>
            <!-- Crafts of India (heritage, makers and regional discovery) -->
            <li class="bc-nav-item has-mega">
              <a href="/conscious/stories">
                Crafts of India
                <i class="fa-solid fa-chevron-down" style="font-size:0.625rem;margin-left:2px;"></i>
              </a>
              <div class="bc-mega-menu bc-crafts-menu" role="region" aria-label="Art and Crafts mega menu">
                <div class="bc-mega-title"><strong>Crafts of India</strong><span>Meet India’s Makers</span></div>
                <div class="bc-mega-grid">
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-shirt" aria-hidden="true"></i> Indian Handloom</h5><ul>
                      <li><a href="/conscious/shop?q=Handloom%20Sarees">Handloom Sarees</a></li>
                      <li><a href="/conscious/shop?q=Ikat">Ikat</a></li>
                      <li><a href="/conscious/shop?q=Banarasi">Banarasi</a></li>
                    </ul></div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-scissors" aria-hidden="true"></i> Textiles &amp; Embroidery</h5><ul>
                      <li><a href="/conscious/shop?q=Kantha">Kantha</a></li>
                      <li><a href="/conscious/shop?q=Chikankari">Chikankari</a></li>
                      <li><a href="/conscious/shop?q=Phulkari">Phulkari</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-palette" aria-hidden="true"></i> Traditional Paintings</h5><ul>
                      <li><a href="/conscious/shop?q=Madhubani">Madhubani</a></li>
                      <li><a href="/conscious/shop?q=Warli">Warli</a></li>
                      <li><a href="/conscious/shop?q=Pattachitra">Pattachitra</a></li>
                    </ul></div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-mug-hot" aria-hidden="true"></i> Pottery &amp; Ceramics</h5><ul>
                      <li><a href="/conscious/shop?q=Blue%20Pottery">Blue Pottery</a></li>
                      <li><a href="/conscious/shop?q=Terracotta">Terracotta</a></li>
                      <li><a href="/conscious/shop?q=Studio%20Ceramics">Studio Ceramics</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-tree" aria-hidden="true"></i> Woodcraft</h5><ul>
                      <li><a href="/conscious/shop?q=Wood%20Carving">Wood Carving</a></li>
                      <li><a href="/conscious/shop?q=Wooden%20Toys">Wooden Toys</a></li>
                      <li><a href="/conscious/shop?q=Wood%20Decor">Wood Décor</a></li>
                    </ul></div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-hammer" aria-hidden="true"></i> Metal Crafts</h5><ul>
                      <li><a href="/conscious/shop?q=Dhokra">Dhokra</a></li>
                      <li><a href="/conscious/shop?q=Bidri">Bidri</a></li>
                      <li><a href="/conscious/shop?q=Brass%20Craft">Brass Craft</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-regular fa-gem" aria-hidden="true"></i> Jewellery Crafts</h5><ul>
                      <li><a href="/conscious/shop?q=Kundan">Kundan</a></li>
                      <li><a href="/conscious/shop?q=Meenakari">Meenakari</a></li>
                      <li><a href="/conscious/shop?q=Silver%20Filigree">Silver Filigree</a></li>
                    </ul></div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-seedling" aria-hidden="true"></i> Natural Fibre Crafts</h5><ul>
                      <li><a href="/conscious/shop?q=Bamboo%20Craft">Bamboo Craft</a></li>
                      <li><a href="/conscious/shop?q=Cane%20Craft">Cane Craft</a></li>
                      <li><a href="/conscious/shop?q=Jute">Jute</a></li>
                    </ul></div>
                  </div>
                  <div class="bc-mega-col">
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Leather &amp; Natural</h5><ul>
                      <li><a href="/conscious/shop?q=Vegetable-Tanned%20Leather">Vegetable-Tanned Leather</a></li>
                      <li><a href="/conscious/shop?q=Handmade%20Leather">Handmade Leather</a></li>
                      <li><a href="/conscious/shop?q=Natural%20Fibre%20Accessories">Natural Accessories</a></li>
                    </ul></div>
                    <div class="bc-mega-group"><h5><i class="fa-solid fa-puzzle-piece" aria-hidden="true"></i> Toys &amp; Folk Crafts</h5><ul>
                      <li><a href="/conscious/shop?q=Dolls">Dolls</a></li>
                      <li><a href="/conscious/shop?q=Puppets">Puppets</a></li>
                      <li><a href="/conscious/shop?q=Traditional%20Games">Traditional Games</a></li>
                    </ul></div>
                  </div>
                </div>
                <div class="bc-craft-shortcuts" aria-label="More craft discovery">
                  <a href="/conscious/stories"><i class="fa-solid fa-hands" aria-hidden="true"></i><span><b>Meet the Artisans</b><small>Masters, collectives and rural entrepreneurs</small></span></a>
                  <a href="/conscious/shop?q=Regional"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i><span><b>Explore by Region</b><small>Discover crafts from across India</small></span></a>
                  <a href="/conscious/stories"><i class="fa-solid fa-book-open" aria-hidden="true"></i><span><b>Artisan Stories</b><small>Behind the craft and maker journeys</small></span></a>
                </div>
              </div>
            </li>
            <!-- Discover (content, community and experiences) -->
            <li class="bc-nav-item has-mega">
              <a href="/conscious/stories">
                Discover
                <i class="fa-solid fa-chevron-down" style="font-size:0.625rem;margin-left:2px;"></i>
              </a>
              <div class="bc-mega-menu bc-discover-menu" role="region" aria-label="Discover mega menu">
                <div class="bc-mega-title"><strong>Discover</strong><span>Explore the Conscious World</span></div>
                <div class="bc-mega-grid">
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-book-open" aria-hidden="true"></i> Stories</h5><ul>
                      <li><a href="/conscious/blog">The Conscious Journal</a></li>
                      <li><a href="/conscious/stories">Artisan Stories</a></li>
                      <li><a href="/conscious/stories">Brand Stories</a></li>
                      <li><a href="/conscious/stories">Women Entrepreneurs</a></li>
                      <li><a href="/conscious/stories">Founder Stories</a></li>
                      <li><a href="/conscious/stories">Sustainability Stories</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Learn</h5><ul>
                      <li><a href="/conscious/stories">Sustainable Living</a></li>
                      <li><a href="/conscious/stories">Conscious Consumption</a></li>
                      <li><a href="/conscious/stories">Slow Fashion</a></li>
                      <li><a href="/conscious/stories">Indian Crafts</a></li>
                      <li><a href="/conscious/stories">Circular Economy</a></li>
                      <li><a href="/conscious/stories">Zero Waste</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-regular fa-calendar" aria-hidden="true"></i> Events &amp; Experiences</h5><ul>
                      <li><a href="/conscious/events">Kala Samāgam</a></li>
                      <li><a href="/conscious/events">Conscious Land</a></li>
                      <li><a href="/conscious/events">Artisan Markets</a></li>
                      <li><a href="/conscious/events">Pop-Ups &amp; Exhibitions</a></li>
                      <li><a href="/conscious/events">Workshops</a></li>
                      <li><a href="/conscious/events">Conferences</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-people-group" aria-hidden="true"></i> Community</h5><ul>
                      <li><a href="/conscious/community">Self-Help Groups</a></li>
                      <li><a href="/conscious/community">NGOs</a></li>
                      <li><a href="/conscious/community">LGBTQ Community</a></li>
                      <li><a href="/conscious/community">Fauji Entrepreneurs</a></li>
                      <li><a href="/conscious/community">Universities</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-star" aria-hidden="true"></i> Creator Shops</h5><ul>
                      <li><a href="/conscious/creators">Sustainability Creators</a></li>
                      <li><a href="/conscious/creators">Slow Fashion Creators</a></li>
                      <li><a href="/conscious/creators">Conscious Living Creators</a></li>
                      <li><a href="/conscious/creators">Zero-Waste Creators</a></li>
                      <li><a href="/conscious/creators">Creator Collections</a></li>
                    </ul>
                  </div></div>
                </div>
                <div class="bc-mega-banner">
                  <div class="bc-mega-banner-text"><span class="bc-mega-banner-label">Stories, experiences and community</span><span>Learn from makers, join events and explore conscious creator collections.</span></div>
                  <a href="/conscious/events" class="bc-btn bc-btn-primary bc-btn-sm">Explore events</a>
                </div>
              </div>
            </li>
            <!-- GI Tagged & Authenticated (provenance and credentials) -->
            <li class="bc-nav-item has-mega">
              <a href="/conscious/gi-tag">
                GI &amp; Certified
                <i class="fa-solid fa-chevron-down" style="font-size:0.625rem;margin-left:2px;"></i>
              </a>
              <div class="bc-mega-menu bc-gi-menu" role="region" aria-label="GI Tagged and Certified mega menu">
                <div class="bc-mega-title"><strong>GI Tagged &amp; Authenticated</strong><span>Verify provenance and credentials</span></div>
                <div class="bc-mega-grid">
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-certificate" aria-hidden="true"></i> Shop by GI</h5><ul>
                      <li><a href="/conscious/shop?q=GI%20Tagged%20Textiles">GI Tagged Textiles</a></li>
                      <li><a href="/conscious/shop?q=GI%20Tagged%20Handicrafts">GI Tagged Handicrafts</a></li>
                      <li><a href="/conscious/shop?q=GI%20Tagged%20Paintings">GI Tagged Paintings</a></li>
                      <li><a href="/conscious/shop?q=GI%20Tagged%20Food">GI Tagged Food</a></li>
                      <li><a href="/conscious/shop?q=GI%20Tagged%20Agricultural">GI Agricultural Products</a></li>
                      <li><a href="/conscious/shop?q=GI%20Tagged%20Jewellery">GI Tagged Jewellery</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> States — North &amp; West</h5><ul>
                      <li><a href="/conscious/shop?q=Rajasthan">Rajasthan</a></li>
                      <li><a href="/conscious/shop?q=Gujarat">Gujarat</a></li>
                      <li><a href="/conscious/shop?q=Maharashtra">Maharashtra</a></li>
                      <li><a href="/conscious/shop?q=Uttar%20Pradesh">Uttar Pradesh</a></li>
                      <li><a href="/conscious/shop?q=Himachal%20Pradesh">Himachal Pradesh</a></li>
                      <li><a href="/conscious/shop?q=Jammu%20Kashmir">Jammu &amp; Kashmir</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-location-dot" aria-hidden="true"></i> States — South &amp; East</h5><ul>
                      <li><a href="/conscious/shop?q=Karnataka">Karnataka</a></li>
                      <li><a href="/conscious/shop?q=Kerala">Kerala</a></li>
                      <li><a href="/conscious/shop?q=Tamil%20Nadu">Tamil Nadu</a></li>
                      <li><a href="/conscious/shop?q=Telangana">Telangana</a></li>
                      <li><a href="/conscious/shop?q=West%20Bengal">West Bengal</a></li>
                      <li><a href="/conscious/shop?q=Odisha">Odisha</a></li>
                      <li><a href="/conscious/shop?q=Assam">Assam</a></li>
                    </ul>
                  </div></div>
                  <div class="bc-mega-col"><div class="bc-mega-group">
                    <h5><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Verified Credentials</h5><ul>
                      <li><a href="/conscious/shop?q=GI%20Tagged">GI Tagged</a></li>
                      <li><a href="/conscious/shop?q=Handloom%20Mark">Handloom Mark</a></li>
                      <li><a href="/conscious/shop?q=Silk%20Mark">Silk Mark</a></li>
                      <li><a href="/conscious/shop?q=Organic%20Certified">Organic Certified</a></li>
                      <li><a href="/conscious/shop?q=Fair%20Trade%20Certified">Fair Trade Certified</a></li>
                      <li><a href="/conscious/shop?q=FSC%20Certified">FSC Certified</a></li>
                      <li><a href="/conscious/shop?q=Vegan%20Certified">Vegan Certified</a></li>
                      <li><a href="/conscious/shop?q=Cruelty-Free%20Certified">Cruelty-Free Certified</a></li>
                    </ul>
                  </div></div>
                </div>
                <div class="bc-mega-banner">
                  <div class="bc-mega-banner-text"><span class="bc-mega-banner-label">Conscious Credentials</span><span>Look for approved GI, handmade, artisan-made, natural-fibre and Made-in-India credentials.</span></div>
                  <a href="/conscious/gi-tag" class="bc-btn bc-btn-primary bc-btn-sm">Explore GI &amp; certified</a>
                </div>
              </div>
            </li>
          </ul>
        </nav>

        <div class="bc-nav-extras">
          <label class="bc-language-selector">
            <i class="fa-solid fa-language" aria-hidden="true"></i>
            <span class="visually-hidden">Language</span>
            <select aria-label="Language" class="bc-language-select">
              <option value="en" selected>English</option>
              <option value="hi">Hindi</option>
              <option value="gu">Gujarati</option>
              <option value="bn">Bengali</option>
              <option value="ta">Tamil</option>
              <option value="te">Telugu</option>
              <option value="mr">Marathi</option>
              <option value="pa">Punjabi</option>
            </select>
          </label>
        </div>

      </div>
    </div>
  </div>

</header>



<header class="bc-header-mobile" role="banner">
  <div class="bc-mobile-top">

    <!-- Hamburger -->
    <button class="bc-hamburger" id="bc-hamburger" aria-label="Open navigation menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <!-- Mobile Logo -->
    <a href="/conscious/" class="bc-logo bc-logo-real" aria-label="Conscious India">
      <img
        src="/conscious/assets/images/logo-horizontal.png"
        alt="Conscious India"
        class="bc-logo-img bc-logo-horizontal bc-logo-mobile"
        width="47"
        height="44"
        loading="eager"
      />
    </a>

    <!-- Mobile Actions -->
    <div class="bc-mobile-actions">
      <a href="/conscious/portals/modules/customer/wishlist.php" class="bc-mobile-action-btn" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></a>
      <a href="/conscious/portals/modules/customer/cart.php" class="bc-mobile-action-btn" aria-label="Cart" style="position:relative;">
        <i class="fa-solid fa-bag-shopping"></i>
        <span class="bc-action-badge" style="top:2px;right:2px;">0</span>
      </a>
      <a href="/conscious/portals/modules/customer/index.php" class="bc-mobile-action-btn" aria-label="Account"><i class="fa-regular fa-circle-user"></i></a>
    </div>

  </div>

  <!-- Mobile Search -->
  <div class="bc-mobile-search-wrap">
    <div class="bc-mobile-search" role="search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" placeholder="Search products, crafts, makers and values" aria-label="Search" />
    </div>
  </div>
</header>

<!-- Mobile Drawer -->
<nav class="bc-mobile-drawer" id="bc-mobile-drawer" aria-label="Mobile Navigation" aria-hidden="true">
  <div class="bc-drawer-head">
    <a href="/conscious/" class="bc-logo bc-logo-real" aria-label="Conscious India Home">
      <img
        src="/conscious/assets/images/logo-square.png"
        alt="Conscious India"
        class="bc-logo-img bc-logo-square-img"
        width="130"
        height="130"
        loading="lazy"
      />
    </a>
    <button class="bc-drawer-close" id="bc-drawer-close" aria-label="Close menu">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <div class="bc-drawer-nav">
    <label class="bc-language-selector bc-language-selector-mobile">
      <i class="fa-solid fa-language" aria-hidden="true"></i>
      <span class="visually-hidden">Language</span>
      <select aria-label="Language" class="bc-language-select">
        <option value="en" selected>English</option>
        <option value="hi">Hindi</option>
        <option value="gu">Gujarati</option>
        <option value="bn">Bengali</option>
        <option value="ta">Tamil</option>
        <option value="te">Telugu</option>
        <option value="mr">Marathi</option>
        <option value="pa">Punjabi</option>
      </select>
    </label>
    <div class="bc-drawer-section-title">Explore</div>
    <a href="/conscious/"><i class="fa-solid fa-house"></i> Home</a>
    <a href="/conscious/shop"><i class="fa-solid fa-store"></i> Marketplace</a>
    <a href="/conscious/shop?q=Sustainable"><i class="fa-solid fa-leaf"></i> Conscious Shop</a>
    <a href="/conscious/stories"><i class="fa-solid fa-palette"></i> Crafts of India</a>
    <a href="/conscious/stories"><i class="fa-solid fa-compass"></i> Discover</a>
    <a href="/conscious/gi-tag"><i class="fa-solid fa-certificate"></i> GI &amp; Certified</a>

    <div class="bc-drawer-divider"></div>
    <div class="bc-drawer-section-title">Discover</div>
    <a href="/conscious/stories"><i class="fa-solid fa-book-open"></i> Stories &amp; Learn</a>
    <a href="/conscious/blog"><i class="fa-regular fa-newspaper"></i> The Conscious Journal</a>
    <a href="/conscious/events"><i class="fa-regular fa-calendar"></i> Events &amp; Experiences</a>
    <a href="/conscious/community"><i class="fa-solid fa-people-group"></i> Community</a>
    <a href="/conscious/creators"><i class="fa-solid fa-star"></i> Creator Shops</a>
    <a href="/conscious/corporate-gifting"><i class="fa-solid fa-gift"></i> Conscious Gifting</a>

    <div class="bc-drawer-divider"></div>
    <div class="bc-drawer-section-title">Partners</div>
    <a href="/conscious/become-brand-partner"><i class="fa-solid fa-store"></i> Sell With Us</a>
    <a href="#exhibit"><i class="fa-solid fa-people-roof"></i> Exhibit With Us</a>
    <a href="/conscious/creators"><i class="fa-solid fa-star"></i> Become a Creator</a>

    <div class="bc-drawer-divider"></div>
    <a href="/conscious/portals/modules/customer/tracking.php"><i class="fa-regular fa-clock"></i> Track Order</a>
    <a href="/conscious/portals/modules/customer/index.php"><i class="fa-regular fa-circle-user"></i> My Account</a>
    <a href="/conscious/help"><i class="fa-regular fa-circle-question"></i> Help &amp; Support</a>
  </div>
</nav>
<div class="bc-overlay" id="bc-overlay"></div>
