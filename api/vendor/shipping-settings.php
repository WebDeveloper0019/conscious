<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/india.php';
send_cors_headers();
$vendorId = require_vendor(true)['vendor']['id'];
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $settings = $db->prepare('SELECT * FROM vendor_shipping_settings WHERE vendor_id=:vendor');
    $settings->execute(['vendor'=>$vendorId]);
    $zonesQuery = $db->prepare('SELECT * FROM vendor_shipping_zones WHERE vendor_id=:vendor ORDER BY created_at');
    $zonesQuery->execute(['vendor'=>$vendorId]);
    $zones = $zonesQuery->fetchAll();
    $ratesQuery = $db->prepare('SELECT * FROM vendor_shipping_rates WHERE vendor_id=:vendor ORDER BY created_at');
    $ratesQuery->execute(['vendor'=>$vendorId]);
    $byZone = [];
    foreach ($ratesQuery->fetchAll() as $rate) $byZone[$rate['zone_id']][] = $rate;
    foreach ($zones as &$zone) $zone['rates'] = $byZone[$zone['id']] ?? [];
    unset($zone);
    json_response(200,['success'=>true,'settings'=>$settings->fetch() ?: null,'zones'=>$zones]);
}

if ($method === 'PUT') {
    $body = get_json_body();
    $coverage = (string)($body['coverage_model'] ?? '');
    $min = filter_var($body['processing_min_days'] ?? null,FILTER_VALIDATE_INT);
    $max = filter_var($body['processing_max_days'] ?? null,FILTER_VALIDATE_INT);
    $base = filter_var($body['base_charge'] ?? null,FILTER_VALIDATE_FLOAT);
    $free = $body['free_above'] ?? null;
    $freeAmount = $free === null ? null : filter_var($free,FILTER_VALIDATE_FLOAT);
    if (!in_array($coverage,['all_india','selected_locations'],true) || $min === false || $max === false || $min < 0 || $max < $min || $max > 90 || $base === false || $base < 0 || $base > 100000 || ($free !== null && ($freeAmount === false || $freeAmount < 0 || $freeAmount > 10000000))) json_response(422,['success'=>false,'message'=>'Enter valid coverage, processing days and charges.']);
    $couriers = array_slice(array_filter(array_map('trim',(array)($body['courier_partners'] ?? []))),0,20);
    $db->prepare('INSERT INTO vendor_shipping_settings (vendor_id,coverage_model,processing_min_days,processing_max_days,courier_partners,base_charge,free_above,cod_enabled,fragile_handling,created_at,updated_at) VALUES (:vendor,:coverage,:min,:max,:couriers,:base,:free,:cod,:fragile,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE coverage_model=VALUES(coverage_model),processing_min_days=VALUES(processing_min_days),processing_max_days=VALUES(processing_max_days),courier_partners=VALUES(courier_partners),base_charge=VALUES(base_charge),free_above=VALUES(free_above),cod_enabled=VALUES(cod_enabled),fragile_handling=VALUES(fragile_handling),updated_at=UTC_TIMESTAMP()')->execute(['vendor'=>$vendorId,'coverage'=>$coverage,'min'=>$min,'max'=>$max,'couriers'=>implode(', ',$couriers),'base'=>round((float)$base,2),'free'=>$freeAmount === null ? null : round((float)$freeAmount,2),'cod'=>!empty($body['cod_enabled'])?1:0,'fragile'=>!empty($body['fragile_handling'])?1:0]);
    json_response(200,['success'=>true,'message'=>'India delivery settings saved.']);
}

if ($method === 'POST') {
    $body = get_json_body();
    $rule = (string)($body['rule_type'] ?? '');
    $name = trim((string)($body['zone_name'] ?? ''));
    $state = trim((string)($body['state'] ?? ''));
    $city = trim((string)($body['city'] ?? ''));
    $from = trim((string)($body['pincode_from'] ?? ''));
    $to = trim((string)($body['pincode_to'] ?? ''));
    $excluded = !empty($body['is_exclusion']);
    $rates = (array)($body['rates'] ?? []);
    if (in_array($rule,['state','city'],true)) {
        $canonicalState = canonical_india_state($state);
        if ($canonicalState === null) json_response(422,['success'=>false,'message'=>'Choose a valid Indian state or union territory.']);
        $state = $canonicalState;
    }
    if (!in_array($rule,['all_india','state','city','pincode'],true) || mb_strlen($name)<3 || mb_strlen($name)>100 || ($rule==='state' && ($state==='' || mb_strlen($state)>100)) || ($rule==='city' && ($state==='' || $city==='' || mb_strlen($state)>100 || mb_strlen($city)>100)) || ($rule==='pincode' && (!preg_match('/^[1-9][0-9]{5}$/',$from) || !preg_match('/^[1-9][0-9]{5}$/',$to) || $from>$to)) || (!$excluded && !$rates)) json_response(422,['success'=>false,'message'=>'Complete the delivery rule, location and price.']);
    foreach ($rates as $rate) {
        $charge = filter_var($rate['charge'] ?? null,FILTER_VALIDATE_FLOAT);
        if ($charge===false || $charge<0 || $charge>100000) json_response(422,['success'=>false,'message'=>'Delivery charge must be between ₹0 and ₹100,000.']);
    }
    $zoneId = uuid_string();
    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO vendor_shipping_zones (id,vendor_id,zone_name,rule_type,country_code,state,city,pincode_from,pincode_to,is_exclusion,is_active,created_at,updated_at) VALUES (:id,:vendor,:name,:rule,"IN",:state,:city,:from_pin,:to_pin,:excluded,1,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$zoneId,'vendor'=>$vendorId,'name'=>$name,'rule'=>$rule,'state'=>in_array($rule,['state','city'],true)?$state:null,'city'=>$rule==='city'?$city:null,'from_pin'=>$rule==='pincode'?$from:null,'to_pin'=>$rule==='pincode'?$to:null,'excluded'=>$excluded?1:0]);
        if (!$excluded) foreach ($rates as $rate) $db->prepare('INSERT INTO vendor_shipping_rates (id,vendor_id,zone_id,calculation_type,min_weight_grams,max_weight_grams,min_order_value,max_order_value,charge,additional_unit_charge,is_active,created_at,updated_at) VALUES (:id,:vendor,:zone,:type,:min_weight,:max_weight,:min_value,:max_value,:charge,:additional,1,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>uuid_string(),'vendor'=>$vendorId,'zone'=>$zoneId,'type'=>in_array($rate['calculation_type'] ?? '',['flat','weight','order_value'],true)?$rate['calculation_type']:'flat','min_weight'=>$rate['min_weight_grams']??null,'max_weight'=>$rate['max_weight_grams']??null,'min_value'=>$rate['min_order_value']??null,'max_value'=>$rate['max_order_value']??null,'charge'=>round((float)$rate['charge'],2),'additional'=>$rate['additional_unit_charge']??null]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }
    json_response(201,['success'=>true,'message'=>'Delivery rule created.','zone_id'=>$zoneId]);
}

if ($method === 'DELETE') {
    $zoneId = trim((string)($_GET['zone_id'] ?? ''));
    if ($zoneId==='') json_response(422,['success'=>false,'message'=>'Select a delivery rule.']);
    $delete = $db->prepare('DELETE FROM vendor_shipping_zones WHERE id=:id AND vendor_id=:vendor');
    $delete->execute(['id'=>$zoneId,'vendor'=>$vendorId]);
    if (!$delete->rowCount()) json_response(404,['success'=>false,'message'=>'Delivery rule not found.']);
    json_response(200,['success'=>true,'message'=>'Delivery rule removed.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
