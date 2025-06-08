<link rel="stylesheet" href="style.css">

<?php
$current_page = basename($_SERVER['PHP_SELF']);
$notifFile = 'notifications.json';
$unread_count = 0;

// Hitung notifikasi yang belum dibaca HANYA jika kita punya info user
if (isset($_SESSION['username'])) {
    $current_user_for_menu = $_SESSION['username'];
    if (file_exists($notifFile)) {
        $all_notifications_for_menu = json_decode(file_get_contents($notifFile), true);
        if (is_array($all_notifications_for_menu)) {
            foreach ($all_notifications_for_menu as $notif) {
                if ($notif['recipient_username'] === $current_user_for_menu && $notif['is_read'] === false) {
                    $unread_count++;
                }
            }
        }
    }
}
?>

<div style="position: relative;"> <ul class="main-nav">
        <li>
            <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                Public Feed
            </a>
        </li>
        <li>
            <a href="kalender.php" class="<?= ($current_page == 'kalender.php') ? 'active' : '' ?>">
                Kalender
            </a>
        </li>
        <li>
            <a href="notifikasi.php" class="<?= ($current_page == 'notifikasi.php') ? 'active' : '' ?>">
                Notifikasi
            </a>
        </li>
    </ul>
    <?php if ($unread_count > 0): ?>
        <span class="notif-badge"><?= $unread_count ?></span>
    <?php endif; ?>
</div>