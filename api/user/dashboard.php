<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$auth = require_auth();
$userId = auth_user_id($auth);
$db = get_db();

function dashboard_table_exists(PDO $db, string $table): bool {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=:table');
    $stmt->execute(['table' => $table]);
    return $cache[$table] = ((int)$stmt->fetchColumn() > 0);
}

function dashboard_columns(PDO $db, string $table): array {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];
    if (!dashboard_table_exists($db, $table)) return $cache[$table] = [];
    $stmt = $db->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
    $columns = [];
    foreach ($stmt->fetchAll() as $row) $columns[(string)$row['Field']] = true;
    return $cache[$table] = $columns;
}

function dashboard_has_columns(PDO $db, string $table, array $required): bool {
    $columns = dashboard_columns($db, $table);
    foreach ($required as $column) if (!isset($columns[$column])) return false;
    return true;
}

try {
    if (!dashboard_has_columns($db, 'users', ['id','first_name','last_name','email'])) {
        json_response(500, ['success' => false, 'message' => 'Customer database is not initialized.']);
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE id=:user LIMIT 1');
    $stmt->execute(['user' => $userId]);
    $user = $stmt->fetch();
    if (!$user) json_response(404, ['success' => false, 'message' => 'Customer account not found.']);

    $stats = ['orders'=>0,'activeOrders'=>0,'deliveredOrders'=>0,'wishlistItems'=>0,'totalSpent'=>0,'addresses'=>0,'unreadNotifications'=>0,'rewardPoints'=>0];
    $recentOrders = [];
    $pendingActions = [];
    $notifications = [];
    $coupons = [];
    $savedVendors = [];
    $upcomingEvents = [];
    $upcomingBookings = [];

    if (dashboard_has_columns($db, 'orders', ['id','user_id','order_number','status','total_amount','created_at'])) {
        $stmt = $db->prepare("SELECT COUNT(*), COALESCE(SUM(total_amount),0), SUM(status NOT IN ('delivered','cancelled','refunded')), SUM(status='delivered') FROM orders WHERE user_id=:user");
        $stmt->execute(['user'=>$userId]);
        $row = $stmt->fetch(PDO::FETCH_NUM) ?: [0,0,0,0];
        $stats['orders']=(int)$row[0]; $stats['totalSpent']=(float)$row[1]; $stats['activeOrders']=(int)$row[2]; $stats['deliveredOrders']=(int)$row[3];

        $stmt = $db->prepare('SELECT * FROM orders WHERE user_id=:user ORDER BY created_at DESC LIMIT 5');
        $stmt->execute(['user'=>$userId]);
        foreach ($stmt->fetchAll() as $order) {
            $tracking = null;
            if (dashboard_has_columns($db,'vendor_order_groups',['id','order_id','status']) && dashboard_has_columns($db,'shipment_packages',['vendor_group_id','status'])) {
                $trackingSql = 'SELECT g.id AS group_id,g.status AS group_status,p.status AS package_status';
                if (dashboard_has_columns($db,'shipment_packages',['expected_delivery_date'])) $trackingSql .= ',p.expected_delivery_date';
                if (dashboard_has_columns($db,'shipment_packages',['courier_name'])) $trackingSql .= ',p.courier_name';
                if (dashboard_has_columns($db,'shipment_packages',['tracking_reference'])) $trackingSql .= ',p.tracking_reference';
                if (dashboard_has_columns($db,'shipment_packages',['tracking_url'])) $trackingSql .= ',p.tracking_url';
                $trackingSql .= ' FROM vendor_order_groups g LEFT JOIN shipment_packages p ON p.vendor_group_id=g.id WHERE g.order_id=:order ORDER BY p.created_at DESC LIMIT 1';
                $trackStmt=$db->prepare($trackingSql); $trackStmt->execute(['order'=>$order['id']]); $tracking=$trackStmt->fetch() ?: null;
            }
            $recentOrders[]=[
                'id'=>(int)$order['id'],'orderNumber'=>$order['order_number'],'status'=>$order['status'],
                'paymentStatus'=>$order['payment_status']??null,'currency'=>$order['currency']??'INR',
                'subtotal'=>(float)($order['subtotal']??0),'shippingAmount'=>(float)($order['shipping_amount']??0),
                'discountAmount'=>(float)($order['discount_amount']??0),'taxAmount'=>(float)($order['tax_amount']??0),
                'totalAmount'=>(float)$order['total_amount'],'placedAt'=>$order['placed_at']??null,
                'createdAt'=>$order['created_at'],'updatedAt'=>$order['updated_at']??null,'tracking'=>$tracking
            ];
        }

        if (dashboard_has_columns($db,'order_items',['id','order_id','product_name','product_id'])) {
            $stmt=$db->prepare("SELECT id,order_number,total_amount FROM orders WHERE user_id=:user AND status='payment_pending' ORDER BY created_at DESC LIMIT 10");
            $stmt->execute(['user'=>$userId]);
            foreach($stmt->fetchAll() as $row) $pendingActions[]=['type'=>'payment','referenceId'=>(int)$row['id'],'title'=>'Complete payment for order '.$row['order_number'],'amount'=>(float)$row['total_amount'],'actionUrl'=>'checkout.html'];

            if (dashboard_table_exists($db,'product_reviews') && dashboard_has_columns($db,'product_reviews',['id','user_id','order_item_id'])) {
                $sql='SELECT oi.id,oi.product_name,oi.product_id FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN product_reviews r ON r.user_id=o.user_id AND r.order_item_id=oi.id WHERE o.user_id=:user AND o.status=\'delivered\' AND r.id IS NULL ORDER BY o.updated_at DESC,oi.id DESC LIMIT 10';
                $stmt=$db->prepare($sql); $stmt->execute(['user'=>$userId]);
                foreach($stmt->fetchAll() as $row) $pendingActions[]=['type'=>'review','referenceId'=>(int)$row['id'],'title'=>'Review '.$row['product_name'],'productId'=>$row['product_id'],'actionUrl'=>'reviews.html'];
            }
        }
    }

    if (dashboard_table_exists($db,'wishlist_items')) {
        $stmt=$db->prepare('SELECT COUNT(*) FROM wishlist_items WHERE user_id=:user'); $stmt->execute(['user'=>$userId]); $stats['wishlistItems']=(int)$stmt->fetchColumn();
    }
    if (dashboard_table_exists($db,'user_addresses')) {
        $stmt=$db->prepare('SELECT COUNT(*) FROM user_addresses WHERE user_id=:user'); $stmt->execute(['user'=>$userId]); $stats['addresses']=(int)$stmt->fetchColumn();
    }
    if (dashboard_has_columns($db,'notifications',['recipient_type','recipient_id','read_at','created_at'])) {
        $stmt=$db->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_type='customer' AND recipient_id=:user AND read_at IS NULL"); $stmt->execute(['user'=>$userId]); $stats['unreadNotifications']=(int)$stmt->fetchColumn();
        $cols='id,title,message,type,action_url,read_at,created_at';
        $stmt=$db->prepare("SELECT $cols FROM notifications WHERE recipient_type='customer' AND recipient_id=:user AND read_at IS NULL ORDER BY created_at DESC LIMIT 10"); $stmt->execute(['user'=>$userId]);
        foreach($stmt->fetchAll() as $n) $notifications[]=['id'=>$n['id'],'title'=>$n['title'],'message'=>$n['message'],'type'=>$n['type'],'actionUrl'=>$n['action_url'],'readAt'=>$n['read_at'],'createdAt'=>$n['created_at']];
    }
    if (dashboard_has_columns($db,'customer_reward_ledger',['user_id','points'])) {
        $stmt=$db->prepare("SELECT COALESCE(SUM(points),0) FROM customer_reward_ledger WHERE user_id=:user AND status='active' AND (points < 0 OR expires_at IS NULL OR expires_at > UTC_TIMESTAMP())"); $stmt->execute(['user'=>$userId]); $stats['rewardPoints']=(int)$stmt->fetchColumn();
    }
   if (dashboard_has_columns($db,'coupons',['id','code','status','starts_at','ends_at'])) {
    $sql="SELECT c.*,v.display_name AS vendor_name,
                   (SELECT COUNT(*)
                    FROM coupon_redemptions cr
                    WHERE cr.coupon_id=c.id) AS total_usage,

                   (SELECT COUNT(*)
                    FROM coupon_redemptions cr
                    WHERE cr.coupon_id=c.id
                      AND cr.user_id=:user) AS customer_usage

            FROM coupons c

            LEFT JOIN vendors v
              ON v.id=c.vendor_id

            WHERE c.status='active'

              AND c.starts_at<=UTC_TIMESTAMP()
              AND c.ends_at>=UTC_TIMESTAMP()

              /* Vendor must be usable */
              AND (
                    c.vendor_id IS NULL
                    OR (
                        v.status='active'
                        AND v.verification_status='approved'
                    )
              )

              /* Coupon must still have total usage available */
              AND (
                    c.usage_limit IS NULL
                    OR (
                        SELECT COUNT(*)
                        FROM coupon_redemptions cr2
                        WHERE cr2.coupon_id=c.id
                    ) < c.usage_limit
              )

              /* Current customer must still be eligible */
              AND (
                    c.per_customer_limit IS NULL
                    OR (
                        SELECT COUNT(*)
                        FROM coupon_redemptions cr3
                        WHERE cr3.coupon_id=c.id
                          AND cr3.user_id=:user2
                    ) < c.per_customer_limit
              )

            ORDER BY c.ends_at ASC
            LIMIT 10";

    $stmt=$db->prepare($sql);
    $stmt->execute([
        'user'=>$userId,
        'user2'=>$userId
    ]);

    foreach($stmt->fetchAll() as $c) {
        $coupons[]=[
            'id'=>$c['id'],
            'code'=>$c['code'],
            'discountType'=>$c['discount_type']??null,
            'discountValue'=>(float)($c['discount_value']??0),
            'minOrderAmount'=>(float)($c['min_order_amount']??0),
            'maxDiscountAmount'=>isset($c['max_discount_amount'])
                ? (float)$c['max_discount_amount']
                : null,
            'startsAt'=>$c['starts_at'],
            'endsAt'=>$c['ends_at'],
            'vendorName'=>$c['vendor_name']??null,

            'usage'=>[
                'total'=>(int)$c['total_usage'],
                'limit'=>$c['usage_limit']===null
                    ? null
                    : (int)$c['usage_limit'],
                'customer'=>(int)$c['customer_usage'],
                'customerLimit'=>$c['per_customer_limit']===null
                    ? null
                    : (int)$c['per_customer_limit']
            ]
        ];
    }

        $stmt=$db->prepare($sql); $stmt->execute(['user'=>$userId,'user2'=>$userId]);
        foreach($stmt->fetchAll() as $c) $coupons[]=[
            'id'=>$c['id'],'code'=>$c['code'],'discountType'=>$c['discount_type']??null,
            'discountValue'=>(float)($c['discount_value']??0),'minOrderAmount'=>(float)($c['min_order_amount']??0),
            'maxDiscountAmount'=>isset($c['max_discount_amount'])?(float)$c['max_discount_amount']:null,
            'startsAt'=>$c['starts_at'],'endsAt'=>$c['ends_at'],'vendorName'=>$c['vendor_name']??null,
            'usage'=>['total'=>(int)$c['total_usage'],'limit'=>$c['usage_limit']===null?null:(int)$c['usage_limit'],
                      'customer'=>(int)$c['customer_usage'],'customerLimit'=>(int)$c['per_customer_limit']]
        ];
    }
    if (dashboard_has_columns($db,'saved_vendors',['user_id','vendor_id','created_at']) && dashboard_has_columns($db,'vendors',['id','display_name'])) {
        $stmt=$db->prepare('SELECT v.id,v.display_name,v.owner_name,v.vendor_type,sv.created_at AS saved_at FROM saved_vendors sv JOIN vendors v ON v.id=sv.vendor_id WHERE sv.user_id=:user ORDER BY sv.created_at DESC LIMIT 10'); $stmt->execute(['user'=>$userId]);
        foreach($stmt->fetchAll() as $v) $savedVendors[]=['id'=>$v['id'],'displayName'=>$v['display_name'],'ownerName'=>$v['owner_name']??null,'vendorType'=>$v['vendor_type']??null,'savedAt'=>$v['saved_at']];
    }
    if (dashboard_has_columns($db,'events',['id','title','starts_at','ends_at','status'])) {
        $stmt=$db->query("SELECT * FROM events WHERE status='published' AND ends_at>=UTC_TIMESTAMP() ORDER BY starts_at ASC LIMIT 10");
        foreach($stmt->fetchAll() as $e) $upcomingEvents[]=['id'=>$e['id'],'title'=>$e['title'],'slug'=>$e['slug']??null,'eventType'=>$e['event_type']??null,'shortDescription'=>$e['short_description']??null,'venueType'=>$e['venue_type']??null,'venueName'=>$e['venue_name']??null,'startsAt'=>$e['starts_at'],'endsAt'=>$e['ends_at'],'hostName'=>null,'fromPrice'=>0];
    }
    if (dashboard_has_columns($db,'event_bookings',['id','user_id','event_id','status']) && dashboard_has_columns($db,'events',['id','title','starts_at','ends_at'])) {
        $stmt=$db->prepare("SELECT b.id,b.booking_number,b.status,b.quantity,b.total_amount,e.id AS event_id,e.title,e.starts_at,e.ends_at,e.venue_name FROM event_bookings b JOIN events e ON e.id=b.event_id WHERE b.user_id=:user AND e.ends_at>=UTC_TIMESTAMP() AND b.status IN ('confirmed','payment_pending') ORDER BY e.starts_at ASC LIMIT 10"); $stmt->execute(['user'=>$userId]); $upcomingBookings=$stmt->fetchAll();
    }

    json_response(200,[
        'success'=>true,'customer'=>public_user($user),'stats'=>$stats,
        'recentOrders'=>$recentOrders,'pendingActions'=>$pendingActions,'notifications'=>$notifications,
        'rewards'=>['points'=>$stats['rewardPoints']], 'coupons'=>$coupons,'savedVendors'=>$savedVendors,
        'upcomingEvents'=>$upcomingEvents,'upcomingBookings'=>$upcomingBookings,
        'empty'=>[
            'recentOrders'=>$recentOrders===[],'pendingActions'=>$pendingActions===[],'notifications'=>$notifications===[],
            'coupons'=>$coupons===[],'savedVendors'=>$savedVendors===[],'upcomingEvents'=>$upcomingEvents===[],'upcomingBookings'=>$upcomingBookings===[]
        ]
    ]);
} catch (Throwable $e) {
    error_log('Customer dashboard failed for user '.$userId.': '.$e->getMessage());
    json_response(500,['success'=>false,'message'=>'Unable to load the customer dashboard right now.']);
}
