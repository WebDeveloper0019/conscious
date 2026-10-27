<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

try {
    $ctx = require_influencer_session();
    $id = $ctx['creator']['id'];
    $db = get_db();

    $campaigns = $db->prepare('
        SELECT c.*, l.id AS link_id, l.code, l.destination_url, l.status AS link_status 
        FROM influencer_referral_links l 
        JOIN influencer_campaigns c ON c.id = l.campaign_id 
        WHERE l.influencer_id = :id 
        ORDER BY c.ends_at DESC
    ');
    $campaigns->execute(['id' => $id]);

    $stats = $db->prepare('
        SELECT COUNT(*) as conversions, 
               COALESCE(SUM(order_amount),0) as revenue, 
               COALESCE(SUM(commission_amount),0) as commission 
        FROM influencer_conversions 
        WHERE influencer_id = :id
    ');
    $stats->execute(['id' => $id]);

    $clicks = $db->prepare('
        SELECT COUNT(*) 
        FROM referral_clicks c 
        JOIN influencer_referral_links l ON l.id = c.referral_link_id 
        WHERE l.influencer_id = :id
    ');
    $clicks->execute(['id' => $id]);

    $balance = $db->prepare('
        SELECT status, COALESCE(SUM(amount),0) as amount 
        FROM influencer_ledger_entries 
        WHERE influencer_id = :id 
        GROUP BY status
    ');
    $balance->execute(['id' => $id]);
    $balances = $balance->fetchAll() ?: [];

    $eligible = 0.0;
    $pending = 0.0;
    foreach($balances as $row){
        if($row['status'] === 'eligible') $eligible = (float)$row['amount'];
        if($row['status'] === 'pending') $pending += (float)$row['amount'];
    }

    $reserved = $db->prepare('
        SELECT COALESCE(SUM(amount),0) 
        FROM influencer_withdrawals 
        WHERE influencer_id = :id AND status IN ("requested","approved","processing","paid")
    ');
    $reserved->execute(['id' => $id]);
    $reserved_amount = (float)$reserved->fetchColumn();

    // Safely handle if no stats exist yet
    $statsData = $stats->fetch() ?: ['conversions' => 0, 'revenue' => 0, 'commission' => 0];
    $statsData['clicks'] = (int)$clicks->fetchColumn();
    $statsData['pending_earnings'] = $pending;

    json_response(200, [
        'success' => true,
        'campaigns' => $campaigns->fetchAll() ?: [],
        'stats' => $statsData,
        'balances' => $balances,
        'available_to_withdraw' => max(0, round($eligible - $reserved_amount, 2))
    ]);

} catch (Throwable $e) {
    json_response(500, [
        'success' => false,
        'message' => 'DEBUG ERROR: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}