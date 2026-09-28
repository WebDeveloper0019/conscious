<?php
$accountItems = [
    ['index.php','dashboard','fa-house','Overview'],
    ['orders.php','orders','fa-box','My orders'],
    ['tracking.php','tracking','fa-route','Track delivery'],
    ['returns.php','returns','fa-arrow-rotate-left','Returns & refunds'],
    ['events.php','events','fa-calendar-days','Events & bookings'],
    ['wishlist.php','wishlist','fa-heart','Wishlist'],
];
$consciousItems = [
    ['vendors.php','vendors','fa-people-group','Saved makers'],
    ['impact.php','impact','fa-seedling','My impact'],
    ['rewards.php','rewards','fa-gift','Rewards & coupons'],
];
$supportItems = [
    ['communication.php','communication','fa-bell','Messages'],
    ['support.php','support','fa-headset','Help & support'],
    ['account.php','account','fa-user-gear','Profile & privacy'],
];
function customerNavLinks(array $items, string $activePage): void {
    foreach ($items as [$href,$key,$icon,$label]) {
        $active = $activePage === $key ? ' active' : '';
        echo '<a class="cc-nav-link'.$active.'" href="'.htmlspecialchars($href,ENT_QUOTES,'UTF-8').'"><i class="fa-solid '.htmlspecialchars($icon,ENT_QUOTES,'UTF-8').'"></i><span>'.htmlspecialchars($label,ENT_QUOTES,'UTF-8').'</span>';
        if ($key === 'orders') echo '<b>3</b>';
        echo '</a>';
    }
}
?>
<aside class="cc-sidebar" aria-label="Customer account navigation">
    <div class="cc-user"><span class="cc-avatar">RS</span><div><b>Rahul Sharma</b><small>Gold member · 2,840 points</small></div><a href="account.php" aria-label="Edit profile"><i class="fa-solid fa-chevron-right"></i></a></div>
    <nav>
        <p class="cc-nav-label">My account</p><?php customerNavLinks($accountItems,$activePage); ?>
        <p class="cc-nav-label">Conscious journey</p><?php customerNavLinks($consciousItems,$activePage); ?>
        <p class="cc-nav-label">Support & settings</p><?php customerNavLinks($supportItems,$activePage); ?>
    </nav>
    <div class="cc-side-foot"><a href="../../../index.html"><i class="fa-solid fa-arrow-left"></i> Back to storefront</a><a href="auth/login.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div>
</aside>
<nav class="cc-mobile-bottom" aria-label="Mobile customer navigation">
    <a class="<?=$activePage==='dashboard'?'active':''?>" href="index.php"><i class="fa-solid fa-house"></i><span>Home</span></a>
    <a class="<?=$activePage==='shop'?'active':''?>" href="shop.php"><i class="fa-solid fa-store"></i><span>Shop</span></a>
    <a class="<?=$activePage==='events'?'active':''?>" href="events.php"><i class="fa-solid fa-calendar-days"></i><span>Events</span></a>
    <a class="<?=$activePage==='orders'?'active':''?>" href="orders.php"><i class="fa-solid fa-box"></i><span>Orders</span></a>
    <a class="<?=$activePage==='account'?'active':''?>" href="account.php"><i class="fa-solid fa-user"></i><span>Account</span></a>
</nav>
