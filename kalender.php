<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$current_user = $_SESSION['username'];
$dataFile = 'public_feed.json';
$mood_entries_by_date = [];

// 1. Fungsi untuk mengambil semua mood milik user dan mengelompokkannya per tanggal
function getUserMoods($file, $username) {
    if (!file_exists($file)) return [];
    
    $all_entries = json_decode(file_get_contents($file), true);
    if (!is_array($all_entries)) return [];

    $user_moods = [];
    foreach ($all_entries as $entry) {
        if ($entry['username'] === $username) {
            $date = date('Y-m-d', strtotime($entry['date']));
            // Hanya simpan mood pertama jika ada beberapa di hari yang sama
            if (!isset($user_moods[$date])) {
                $user_moods[$date] = [
                    'mood' => $entry['mood'],
                    'id' => $entry['id']
                ];
            }
        }
    }
    return $user_moods;
}

$mood_entries_by_date = getUserMoods($dataFile, $current_user);

// 2. Logika untuk menentukan bulan dan tahun kalender
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$num_days_in_month = date('t', $first_day_of_month);
$day_of_week_start = date('w', $first_day_of_month); // 0=Minggu, 6=Sabtu
$month_name = date('F', $first_day_of_month);

// 3. Logika untuk navigasi bulan sebelumnya dan berikutnya
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year = $year - 1;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13) {
    $next_month = 1;
    $next_year = $year + 1;
}

$days_of_week = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Mood - Mood Tracker</title>
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

    <div class="main-nav-container">
        <?php include 'menu.php'; ?>
    </div>
    
    <div class="card">
        <div class="calendar-container">
            <div class="calendar-toolbar">
                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-arrow" title="Bulan Sebelumnya">&lt;</a>
                <div class="current-month-year">
                    <?= htmlspecialchars($month_name) ?> <?= $year ?>
                </div>
                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-arrow" title="Bulan Berikutnya">&gt;</a>
                <a href="kalender.php" class="today-button">Hari Ini</a>
            </div>

            <div class="calendar-grid">
                <?php foreach ($days_of_week as $day): ?>
                    <div class="day-header"><?= $day ?></div>
                <?php endforeach; ?>

                <?php
                // Sel kosong sebelum tanggal 1
                for ($i = 0; $i < $day_of_week_start; $i++) {
                    echo '<div class="day-cell other-month"></div>';
                }

                // Sel untuk setiap tanggal
                for ($day = 1; $day <= $num_days_in_month; $day++) {
                    $current_date_str = sprintf('%d-%02d-%02d', $year, $month, $day);
                    $is_today = ($current_date_str == date('Y-m-d'));
                    $class = $is_today ? 'today' : '';
                    
                    echo "<div class='day-cell {$class}'>";
                    echo "<div class='day-number'>{$day}</div>";
                    
                    // Cek jika ada mood di tanggal ini
                    if (isset($mood_entries_by_date[$current_date_str])) {
                        $mood_data = $mood_entries_by_date[$current_date_str];
                        $mood_emoji = htmlspecialchars($mood_data['mood']);
                        $mood_id = htmlspecialchars($mood_data['id']);
                        echo "<a href='index.php#post-{$mood_id}' class='mood-emoji' title='Lihat postingan'>{$mood_emoji}</a>";
                    }
                    
                    echo "</div>";
                }

                // Sel kosong setelah tanggal terakhir
                $total_cells = $day_of_week_start + $num_days_in_month;
                $remaining_cells = (7 - ($total_cells % 7)) % 7;
                for ($i = 0; $i < $remaining_cells; $i++) {
                     echo '<div class="day-cell other-month"></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>