<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__.'/../lib/helpers.php';

if (APP_ENV !== 'local') {
    fwrite(STDERR, "Showcase accounts can only be seeded with APP_ENV=local.\n");
    exit(1);
}

$rotate = in_array('--rotate', $argv, true);
$password = 'Demo#'.bin2hex(random_bytes(8)).'Aa1!';
$creators = [
    ['Aanya Green Living', 'demo-aanya-green', 'Mumbai', 'Low-waste routines and practical swaps for Indian homes.'],
    ['Kabir Earth Notes', 'demo-kabir-earth', 'Delhi', 'Thoughtful everyday choices, repair culture and mindful shopping.'],
    ['Meera Handloom Stories', 'demo-meera-handloom', 'Jaipur', 'Indian textiles, slow fashion and the people who make them.'],
    ['Rohan Reuse Journal', 'demo-rohan-reuse', 'Bengaluru', 'Reuse, refill and realistic low-waste city living.'],
    ['Isha Conscious Home', 'demo-isha-home', 'Pune', 'Handmade homewares and responsible ways to make a home feel personal.'],
    ['Dev Nature Finds', 'demo-dev-nature', 'Ahmedabad', 'Simple eco-friendly products and stories of Indian makers.'],
    ['Tara Craft Trails', 'demo-tara-craft', 'Kolkata', 'Regional craft traditions and gifts made with care.'],
    ['Arjun Slow Style', 'demo-arjun-style', 'Hyderabad', 'Wear-more, waste-less fashion and artisan clothing.'],
    ['Naina Everyday Earth', 'demo-naina-earth', 'Chennai', 'Daily sustainability ideas that work for busy families.'],
    ['Samar Made in India', 'demo-samar-made', 'Lucknow', 'Useful, beautiful products from independent Indian makers.'],
];

$db = get_db();
$find = $db->prepare('SELECT id,verification_status FROM influencers WHERE email=:email LIMIT 1');
$insert = $db->prepare('INSERT INTO influencers (id,full_name,email,mobile,password_hash,email_verified,handle,bio,platforms_json,audience_json,status,verification_status,created_at) VALUES (:id,:name,:email,:mobile,:password,1,:handle,:bio,:platforms,:audience,"active","demo_fixture",UTC_TIMESTAMP())');
$role = $db->prepare('INSERT INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "influencer",:id,id,UTC_TIMESTAMP() FROM roles WHERE code="influencer"');
$update = $db->prepare('UPDATE influencers SET password_hash=:password,updated_at=UTC_TIMESTAMP() WHERE id=:id AND verification_status="demo_fixture"');
$created = 0;
$rotated = 0;

$db->beginTransaction();
try {
    foreach ($creators as $index => [$name, $handle, $city, $topic]) {
        $number = $index + 1;
        $email = sprintf('creator.demo%02d@conscious.test', $number);
        $find->execute(['email' => $email]);
        $existing = $find->fetch();
        if ($existing) {
            if ($existing['verification_status'] !== 'demo_fixture') {
                throw new RuntimeException("{$email} exists but is not a showcase fixture; refusing to modify it.");
            }
            if (!$rotate) {
                throw new RuntimeException("Showcase accounts already exist. Run with --rotate to generate new passwords for these demo accounts.");
            }
            $update->execute(['id' => $existing['id'], 'password' => password_hash($password, PASSWORD_DEFAULT)]);
            $rotated++;
            continue;
        }
        $id = uuid_string();
        $bio = $topic.' This is a fictional local demo creator for a Conscious Indi product walkthrough. No real partnership, audience or earnings are claimed.';
        $insert->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'mobile' => sprintf('900000%04d', 100 + $number),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'handle' => $handle,
            'bio' => $bio,
            'platforms' => json_encode([['name' => 'Demo social profile', 'url' => 'https://www.instagram.com/', 'followers' => 0]], JSON_UNESCAPED_SLASHES),
            'audience' => json_encode(['region' => $city], JSON_UNESCAPED_SLASHES),
        ]);
        $role->execute(['id' => $id]);
        $created++;
    }
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}

fwrite(STDOUT, "Local showcase accounts: {$created} created, {$rotated} password(s) rotated.\n");
fwrite(STDOUT, "Shared demo password (all 10): {$password}\n");
foreach ($creators as $index => [$name, $handle]) {
    fwrite(STDOUT, sprintf("%02d  creator.demo%02d@conscious.test  %s  /influencer-profile.html?handle=%s\n", $index + 1, $index + 1, $name, $handle));
}
fwrite(STDOUT, "These are local-only fictional profiles, not real verified creators. No documents, campaigns, orders, commissions or payouts were fabricated.\n");
