<?php
$current_page = basename($_SERVER['PHP_SELF']);
$submenu_pages = [
    'bookingSubMenu' => ['my_booking.php', 'add_booking.php', 'reorder_booking.php'],
];
$open_submenus = [];
foreach ($submenu_pages as $submenu_id => $pages) {
    if (in_array($current_page, $pages)) {
        $open_submenus[$submenu_id] = true;
    }
}
?>

<div class="menu" id="menu">
    <!-- <div class="close-menu" id="closeMenu">Close Menu</div> -->
    <h3>Customer Menu</h3>
    <ul>
        <!-- <li>
            <a href="#" id="bookingMenu">Booking <span id="bookingIcon">+</span></a>
            <ul id="bookingSubMenu" style="display: none;">
                <li><a href="add_booking.php">New Booking</a></li>
                <li><a href="reorder_booking.php">Reorder Booking</a></li>
            </ul>
        </li> -->
        <li><a href="add_booking.php">New Booking</a></li>

        <!-- <li><a href="my_booking.php">My Booking</a></li> -->
        <li>
            <a href="#" id="bookingMenu">My Booking <span id="bookingIcon"><?php echo !empty($open_submenus['bookingSubMenu']) ? '-' : '+'; ?></span></a>
            <ul id="bookingSubMenu" style="display: <?php echo !empty($open_submenus['bookingSubMenu']) ? 'block' : 'none'; ?>;">
                <li><a href="my_booking.php">All Bookings</a></li>
                <li><a href="my_booking.php?search_date=&search_status=pending">To Pay</a></li>
                <li><a href="my_booking.php?search_date=&search_status=confirm">Confirmed</a></li>
                <li><a href="my_booking.php?search_date=&search_status=done">To Review</a></li>
                <li><a href="my_booking.php?search_date=&search_status=rated">Rated</a></li>
            </ul>
        </li>
        
        <li><a href="manage_profile.php">Manage Profile</a></li>
    </ul>
</div>

<div class="floating-toggle" id="menuToggle"><</div>

<script>
document.getElementById('bookingMenu').addEventListener('click', function() {
    var subMenu = document.getElementById('bookingSubMenu');
    var icon = document.getElementById('bookingIcon');
    if (subMenu.style.display === 'none') {
        subMenu.style.display = 'block';
        icon.textContent = '-';
    } else {
        subMenu.style.display = 'none';
        icon.textContent = '+';
    }
});

document.getElementById('menuToggle').addEventListener('click', function() {
    var menu = document.getElementById('menu');
    var mainContainer = document.querySelector('.main-container');
    var header = document.querySelector('.header');
    var toggleIcon = document.getElementById('menuToggle');
    if (menu.style.transform === 'translateX(0px)') {
        menu.style.transform = 'translateX(-100%)';
        mainContainer.style.marginLeft = '0';
        header.style.width = '100%';
        toggleIcon.textContent = '>';
        toggleIcon.style.left = '10px';
    } else {
        menu.style.transform = 'translateX(0px)';
        mainContainer.style.marginLeft = '200px';
        header.style.width = 'calc(100% - 200px)';
        toggleIcon.textContent = '<';
        toggleIcon.style.left = '210px';
    }
});

document.getElementById('closeMenu').addEventListener('click', function() {
    var menu = document.getElementById('menu');
    var mainContainer = document.querySelector('.main-container');
    var header = document.querySelector('.header');
    var toggleIcon = document.getElementById('menuToggle');
    menu.style.transform = 'translateX(-100%)';
    mainContainer.style.marginLeft = '0';
    header.style.width = '100%';
    toggleIcon.textContent = '>';
    toggleIcon.style.left = '10px';
});
</script>