<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';

if (APP_ENV !== 'local') {
    die("This seeder is for local development only.");
}

$db = get_db();
$creators = [
    ['Priya Sharma', 'priya-creates', 'Sustainable Living', 'Mumbai', 'Making conscious choices feel practical, warm, and full of character.'],
    ['Riya Kapoor', 'riya-design', 'Home Decor', 'Bengaluru', 'Design-forward creator exploring mindful living and artisan stories.'],
    ['Aarav Nair', 'aarav-stories', 'Travel & Culture', 'Kochi', 'Travel and culture creator spotlighting underrepresented Indian crafts.'],
    ['Ananya Rawat', 'ananya-slowfashion', 'Slow Fashion', 'New Delhi', 'Showcasing handloom and sustainable fashion that you will love forever.'],
    ['Kabir Sehgal', 'kabir-zerowaste', 'Zero Waste', 'Pune', 'Documenting my journey to a zero-waste lifestyle and eco-friendly swaps.'],
    ['Meera Iyer', 'meera-sustainable', 'Eco Friendly', 'Chennai', 'Sharing honest reviews of natural skincare and eco-friendly home goods.'],
    ['Dev Rao', 'dev-upcycled', 'Upcycled Design', 'Hyderabad', 'Turning waste into beautiful objects. DIY tutorials and upcycled art.'],
    ['Sneha Patel', 'sneha-artisan', 'Handloom & Textiles', 'Ahmedabad', 'Connecting you directly with weavers and handloom artisans of India.'],
    ['Rohan Mehta', 'rohan-earth', 'Sustainable Brands', 'Auroville', 'Spotlighting brands that care about the planet as much as their products.'],
    ['Aditi Joshi', 'aditi-crafts', 'Traditional Art', 'Jaipur', 'Preserving heritage crafts like blue pottery and block printing.']
];

echo "<h2>Seeding 10 Demo Creators...</h2>";

foreach ($creators as $c) {
    $name = $c[0];
    $handle = $c[1];
    $category = $c[2];
    $city = $c[3];
    $bio = $c[4];
    
    $stmt = $db->prepare('SELECT id FROM influencers WHERE handle = :h');
    $stmt->execute(['h' => $handle]);
    if ($stmt->fetch()) {
        echo "Skipping $name (already exists)<br>";
        continue;
    }

    $id = uuid_string();
    $email = strtolower(str_replace(' ', '.', $name)) . "@demo.com";
    $password = password_hash('Password123!', PASSWORD_DEFAULT);
    
    $platforms = json_encode([
        ['name' => 'Instagram', 'url' => 'https://instagram.com/' . $handle, 'followers' => rand(50, 300) * 1000],
        ['name' => 'YouTube', 'url' => 'https://youtube.com/'.$handle, 'followers' => rand(10, 80) * 1000]
    ]);
    
    $audience = json_encode(['region' => $city, 'city' => $city, 'niches' => [$category], 'languages' => ['English', 'Hindi']]);

    try {
        // 1. Insert Influencer (No file_assets this time)
        $db->prepare('INSERT INTO influencers (id, full_name, email, mobile, password_hash, email_verified, handle, bio, platforms_json, audience_json, status, verification_status, created_at) 
                      VALUES (:id, :name, :email, "9999999999", :pw, 1, :handle, :bio, :plat, :aud, "active", "approved", UTC_TIMESTAMP())')
           ->execute([
               'id' => $id, 'name' => $name, 'email' => $email, 'pw' => $password, 
               'handle' => $handle, 'bio' => $bio, 'plat' => $platforms, 'aud' => $audience
           ]);

        // 2. Insert Packages
        $pkg1 = uuid_string();
        $db->prepare('INSERT INTO influencer_packages (id, influencer_id, title, description, price_inr, deliverables_json, platforms_json, turnaround_days, revisions, usage_rights, vendor_requirements, capacity, tax_treatment, cancellation_terms, status, created_at, updated_at) 
                      VALUES (:id, :inf, "Instagram Story Set", "3 slides with swipe up link and product mention.", 4999, :deliv, :plat, 3, 1, "30 days organic repost", "Product shipped 5 days prior", 5, "Inclusive of GST", "50% refund if cancelled 48h prior", "active", UTC_TIMESTAMP(), UTC_TIMESTAMP())')
           ->execute(['id' => $pkg1, 'inf' => $id, 'deliv' => json_encode(["3x Story Slides", "Swipe up link"]), 'plat' => json_encode(["Instagram"])]);

        $pkg2 = uuid_string();
        $db->prepare('INSERT INTO influencer_packages (id, influencer_id, title, description, price_inr, deliverables_json, platforms_json, turnaround_days, revisions, usage_rights, vendor_requirements, capacity, tax_treatment, cancellation_terms, status, created_at, updated_at) 
                      VALUES (:id, :inf, "Dedicated Reel + Story", "A full 30s dedicated reel showcasing your product plus 2 stories.", 14999, :deliv, :plat, 7, 2, "60 days paid ads usage", "Product shipped 10 days prior", 3, "Inclusive of GST", "Non-refundable once approved", "active", UTC_TIMESTAMP(), UTC_TIMESTAMP())')
           ->execute(['id' => $pkg2, 'inf' => $id, 'deliv' => json_encode(["1x Reel (30s)", "2x Story Slides", "Raw footage"]), 'plat' => json_encode(["Instagram", "YouTube"])]);

        echo "✅ Created: <strong>$name</strong> (@$handle)<br>";
    } catch (Throwable $e) {
        echo "❌ Failed to create $name: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>Done! You can now view the directory.</h3>";