<?php
require_once __DIR__.'/layouts/guard.php';
$pageTitle=$vendorSellingReady?'Vendor Dashboard':'Store Setup';
$pageSubtitle=$vendorSellingReady?'Your store activity and next actions':'Complete your seller profile at your pace';
$activePage='dashboard';
$vendorPageScript='assets/js/dashboard-live.js';
$vendorPageCss='assets/css/vendor-setup.css';
require __DIR__.'/layouts/head.php';
if($vendorSellingReady){require __DIR__.'/pages/dashboard-live.php';}
else{require __DIR__.'/pages/dashboard-setup.php';$vendorPageScript='';}
require __DIR__.'/layouts/foot.php';
