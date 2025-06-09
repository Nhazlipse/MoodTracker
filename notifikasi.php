<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$current_user = $_SESSION['username'];
$notifFile = 'notifications.json';

// --- LOGIKA UTAMA ---
$all_notifications = file_exists($notifFile) ? json_decode(file_get_contents($notifFile), true) : [];
if (!is_array($all_notifications)) $all_notifications = [];

// 1. Ambil semua notifikasi untuk user yang sedang login
$user_notifications = [];
foreach ($all_notifications as $notification) {
    if ($notification['recipient_username'] === $current_user) {
        $user_notifications[] = $notification;
    }
}
// Urutkan dari yang terbaru
rsort($user_notifications);


// 2. Tandai semua notifikasi yang belum dibaca sebagai "sudah dibaca"
$notifications_updated = false;
foreach ($all_notifications as $index => $notification) {
    if ($notification['recipient_username'] === $current_user && $notification['is_read'] === false) {
        $all_notifications[$index]['is_read'] = true;
        $notifications_updated = true;
    }
}

// 3. Jika ada pembaruan, simpan kembali ke file
if ($notifications_updated) {
    file_put_contents($notifFile, json_encode($all_notifications, JSON_PRETTY_PRINT));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Mood Tracker</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="user-header">
        <span>Hi, <strong><?= htmlspecialchars($current_user) ?></strong>!</span>
        <a href="logout.php">Logout</a>
    </div>
    
    <?php include 'menu.php'; // Memasukkan menu navigasi ?>

    <div class="card">
        <h2>Notifikasi Anda</h2>
        <ul class="notif-list">
            <?php if (empty($user_notifications)): ?>
                <p class="no-notif">Tidak ada notifikasi baru.</p>
            <?php else: ?>
                <?php foreach($user_notifications as $notif): ?>
                    <li class="notif-item">
                        <a href="index.php#post-<?= htmlspecialchars($notif['post_id']) ?>">
                            <div class="notif-message">
                                <strong><?= htmlspecialchars($notif['actor_username']) ?></strong>
                                <?= htmlspecialchars($notif['message']) ?>
                            </div>
                            <div class="notif-time">
                                <?= date('d M Y, H:i', strtotime($notif['timestamp'])) ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
</body>
</html>