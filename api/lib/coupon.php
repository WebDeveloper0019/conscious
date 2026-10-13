<?php
declare(strict_types=1);

function find_customer_coupon(PDO $db, string $code, string $userId, array $vendorIds, float $cartSubtotal, bool $forUpdate=false): array {
    $code=strtoupper(trim($code));
    if($code==='' || !preg_match('/^[A-Z0-9_-]{4,40}$/',$code)) {
        json_response(422,['success'=>false,'message'=>'Coupon code is invalid.']);
    }

    $sql="SELECT c.*,v.display_name AS vendor_name,v.status AS vendor_status,v.verification_status
          FROM coupons c
          LEFT JOIN vendors v ON v.id=c.vendor_id
          WHERE c.code=:code
            AND c.status='active'
            AND c.starts_at<=UTC_TIMESTAMP()
            AND c.ends_at>=UTC_TIMESTAMP()
            AND (c.vendor_id IS NULL OR (v.status='active' AND v.verification_status='approved'))";
    if($forUpdate)$sql.=" FOR UPDATE";

    $stmt=$db->prepare($sql);
    $stmt->execute(['code'=>$code]);
    $coupon=$stmt->fetch();
    if(!$coupon)json_response(422,['success'=>false,'message'=>'Coupon is expired, inactive or unavailable for this customer.']);

    if($coupon['vendor_id']!==null && !in_array((string)$coupon['vendor_id'],array_map('strval',$vendorIds),true)){
        json_response(422,['success'=>false,'message'=>'Coupon is not valid for this cart.']);
    }
    if($cartSubtotal < (float)$coupon['min_order_amount']){
        json_response(422,['success'=>false,'message'=>'Minimum order amount for this coupon has not been met.']);
    }

    $usage=$db->prepare("SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id=:coupon");
    $usage->execute(['coupon'=>$coupon['id']]);
    $totalUsage=(int)$usage->fetchColumn();
    if($coupon['usage_limit']!==null && $totalUsage >= (int)$coupon['usage_limit']){
        json_response(422,['success'=>false,'message'=>'This coupon has reached its total usage limit.']);
    }

    $customerUsage=$db->prepare("SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id=:coupon AND user_id=:user");
    $customerUsage->execute(['coupon'=>$coupon['id'],'user'=>$userId]);
    if($customerUsage->fetchColumn() >= (int)$coupon['per_customer_limit']){
        json_response(422,['success'=>false,'message'=>'You have reached the usage limit for this coupon.']);
    }

    return $coupon;
}

function find_customer_coupon_by_id(PDO $db, string $couponId, string $userId, array $vendorIds, float $cartSubtotal): array {
    $stmt=$db->prepare("SELECT code FROM coupons WHERE id=:id LIMIT 1 FOR UPDATE");
    $stmt->execute(['id'=>$couponId]);
    $code=$stmt->fetchColumn();
    if(!$code)json_response(422,['success'=>false,'message'=>'Coupon is no longer available.']);
    return find_customer_coupon($db,(string)$code,$userId,$vendorIds,$cartSubtotal,true);
}
