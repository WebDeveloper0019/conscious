<?php
declare(strict_types=1);
require_once __DIR__.'/lib/helpers.php';

if (APP_ENV !== 'local') {
    die("This seeder is for local development only.");
}

$db = get_db();
$db->exec('SET FOREIGN_KEY_CHECKS=0');

$stmt = $db->query('SELECT id, handle FROM influencers WHERE email LIKE "%@demo.com" LIMIT 3');
$creators = $stmt->fetchAll();

if (!$creators) {
    die("Please run seed-demo.php first.");
}

echo "<h2>Seeding Campaigns & Analytics...</h2>";

foreach ($creators as $index => $creator) {
    $infId = $creator['id'];
    $handle = $creator['handle'];
    
    // 🧹 CLEANUP
    $db->prepare('DELETE FROM influencer_conversions WHERE influencer_id = :id')->execute(['id' => $infId]);
    $db->prepare('DELETE FROM influencer_ledger_entries WHERE influencer_id = :id')->execute(['id' => $infId]);
    $db->prepare('DELETE FROM referral_clicks WHERE referral_link_id IN (SELECT id FROM influencer_referral_links WHERE influencer_id = :id)')->execute(['id' => $infId]);
    $db->prepare('DELETE FROM influencer_referral_links WHERE influencer_id = :id')->execute(['id' => $infId]);
    
    // 1. Create a Campaign
    $campId = uuid_string();
    $db->prepare('INSERT INTO influencer_campaigns (id, name, status, starts_at, ends_at, disclosure_text, attribution_days, created_at) 
                  VALUES (:id, "Monsoon Craft Mela 2026", "active", UTC_TIMESTAMP(), DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY), "Paid partnership with Conscious Indi.", 30, UTC_TIMESTAMP())')
       ->execute(['id' => $campId]);

    // 2. Create a Referral Link
    $linkId = uuid_string();
    $code = $handle . '-' . rand(1000, 9999);
    $db->prepare('INSERT INTO influencer_referral_links (id, influencer_id, campaign_id, code, destination_url, status, created_at) 
                  VALUES (:id, :inf, :camp, :code, "/conscious-git/shop.html", "active", UTC_TIMESTAMP())')
       ->execute(['id' => $linkId, 'inf' => $infId, 'camp' => $campId, 'code' => $code]);

    // 3. Generate Fake Clicks
    for ($i = 0; $i < 120; $i++) {
        $db->prepare('INSERT INTO referral_clicks (id, referral_link_id, visitor_hash, ip_hash, user_agent_hash, clicked_at) 
                      VALUES (:id, :link, :vis, :ip, :agent, DATE_SUB(UTC_TIMESTAMP(), INTERVAL :days DAY))')
           ->execute([
               'id' => uuid_string(), 
               'link' => $linkId, 
               'vis' => hash('sha256', 'mock_visitor_' . rand()), 
               'ip' => hash('sha256', '127.0.0.1'), 
               'agent' => hash('sha256', 'mock_agent'), 
               'days' => rand(1, 7)
           ]);
    }

    // 4. Generate Fake Sales (Conversions)
    $salesCount = rand(5, 15);
    $totalCommission = 0;
    
    for ($i = 0; $i < $salesCount; $i++) {
        $orderAmount = rand(2000, 10000);
        $commission = $orderAmount * 0.10;
        $totalCommission += $commission;
        
        $orderId = rand(10000000, 99999999);
        
        $db->prepare('INSERT INTO influencer_conversions (id, influencer_id, campaign_id, order_id, order_amount, commission_amount, status, created_at) 
                      VALUES (:id, :inf, :camp, :order, :amt, :comm, "approved", DATE_SUB(UTC_TIMESTAMP(), INTERVAL :days DAY))')
           ->execute([
               'id' => uuid_string(), 'inf' => $infId, 'camp' => $campId, 'order' => $orderId, 
               'amt' => $orderAmount, 'comm' => $commission, 'days' => rand(1, 7)
           ]);
    }

    // 5. Add Commission to Ledger (Removed 'description')
    $db->prepare('INSERT INTO influencer_ledger_entries (id, influencer_id, amount, status, created_at) 
                  VALUES (:id, :inf, :amt, "eligible", UTC_TIMESTAMP())')
       ->execute(['id' => uuid_string(), 'inf' => $infId, 'amt' => $totalCommission]);

    echo "✅ Seeded Campaign, Clicks, and ₹" . number_format($totalCommission) . " earnings for <strong>@$handle</strong><br>";
}

// Also seed for your main account
$stmt = $db->query('SELECT id, handle FROM influencers WHERE email NOT LIKE "%@demo.com" LIMIT 1');
$me = $stmt->fetch();
if ($me) {
    $infId = $me['id'];
    
    // 🧹 CLEANUP for main account
    $db->prepare('DELETE FROM influencer_conversions WHERE influencer_id = :id')->execute(['id' => $infId]);
    $db->prepare('DELETE FROM influencer_ledger_entries WHERE influencer_id = :id')->execute(['id' => $infId]);
    $db->prepare('DELETE FROM referral_clicks WHERE referral_link_id IN (SELECT id FROM influencer_referral_links WHERE influencer_id = :id)')->execute(['id' => $infId]);
    $db->prepare('DELETE FROM influencer_referral_links WHERE influencer_id = :id')->execute(['id' => $infId]);

    $campId = uuid_string();
    $db->prepare('INSERT INTO influencer_campaigns (id, name, status, starts_at, ends_at, disclosure_text, attribution_days, created_at) 
                  VALUES (:id, "Diwali Gifting Feature", "active", UTC_TIMESTAMP(), DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 DAY), "Sponsored collaboration.", 30, UTC_TIMESTAMP())')
       ->execute(['id' => $campId]);

    $linkId = uuid_string();
    $db->prepare('INSERT INTO influencer_referral_links (id, influencer_id, campaign_id, code, destination_url, status, created_at) 
                  VALUES (:id, :inf, :camp, "TARUN-DIWALI", "/conscious-git/shop.html", "active", UTC_TIMESTAMP())')
       ->execute(['id' => $linkId, 'inf' => $infId, 'camp' => $campId]);

    for ($i = 0; $i < 50; $i++) {
        $db->prepare('INSERT INTO referral_clicks (id, referral_link_id, visitor_hash, ip_hash, user_agent_hash, clicked_at) VALUES (:id, :link, :vis, :ip, :agent, UTC_TIMESTAMP())')
           ->execute(['id' => uuid_string(), 'link' => $linkId, 'vis' => hash('sha256', 'mock'), 'ip' => hash('sha256', '127.0.0.1'), 'agent' => hash('sha256', 'mock')]);
    }
    
    $orderId = rand(10000000, 99999999);
    $db->prepare('INSERT INTO influencer_conversions (id, influencer_id, campaign_id, order_id, order_amount, commission_amount, status, created_at) VALUES (:id, :inf, :camp, :order, 50000, 5000, "approved", UTC_TIMESTAMP())')->execute(['id' => uuid_string(), 'inf' => $infId, 'camp' => $campId, 'order' => $orderId]);
    
    // Removed 'description' here too
    $db->prepare('INSERT INTO influencer_ledger_entries (id, influencer_id, amount, status, created_at) VALUES (:id, :inf, 5000, "eligible", UTC_TIMESTAMP())')->execute(['id' => uuid_string(), 'inf' => $infId]);
    
    echo "✅ Seeded Campaign and ₹5,000 earnings for your main account (@{$me['handle']})<br>";
}

$db->exec('SET FOREIGN_KEY_CHECKS=1');

echo "<h3>Done! Check your Creator Studio.</h3>";