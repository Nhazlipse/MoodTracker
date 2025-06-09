<?php
// Memaksa PHP untuk menampilkan semua error
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$current_user = $_SESSION['username'];
$dataFile = 'public_feed.json';

// --- FUNGSI-FUNGSI ---
// Membaca file dengan urutan asli (paling lama di atas)
function getMoodEntries($file) {
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    if (empty($json)) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

// Menyimpan file dengan urutan asli
function saveMoodEntries($file, $entries) {
    // Re-index array setelah unset untuk memastikan format JSON benar
    file_put_contents($file, json_encode(array_values($entries), JSON_PRETTY_PRINT));
}

// --- LOGIKA UTAMA (POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entries = getMoodEntries($dataFile);
    $action = $_POST['action'] ?? 'create_mood';

    switch ($action) {
        case 'delete_mood':
            $moodIdToDelete = $_POST['id_to_delete'];
            $entries = array_filter($entries, function($entry) use ($moodIdToDelete, $current_user) {
                return !($entry['id'] === $moodIdToDelete && $entry['username'] === $current_user);
            });
            saveMoodEntries($dataFile, $entries);
            header('Location: dashboard.php');
            exit;

        case 'delete_reply':
            $moodId = $_POST['mood_id'];
            $replyIdToDelete = $_POST['id_to_delete'];
            foreach ($entries as $m_idx => &$mood) { // Gunakan & (reference) agar bisa diubah
                if ($mood['id'] === $moodId) {
                    $mood['replies'] = array_filter($mood['replies'], function($reply) use ($replyIdToDelete, $current_user) {
                        return !($reply['reply_id'] === $replyIdToDelete && $reply['username'] === $current_user);
                    });
                    break;
                }
            }
            saveMoodEntries($dataFile, $entries);
            header('Location: dashboard.php');
            exit;

        case 'update_mood':
            $moodIdToUpdate = $_POST['id_to_update'];
            $newNote = $_POST['note'];
            foreach ($entries as &$entry) { // Gunakan & (reference)
                if ($entry['id'] === $moodIdToUpdate && $entry['username'] === $current_user) {
                    $entry['note'] = $newNote;
                    break;
                }
            }
            saveMoodEntries($dataFile, $entries);
            header('Location: dashboard.php');
            exit;

        case 'create_reply':
            $replyToId = $_POST['reply_to_id'];
            $replyText = $_POST['reply_text'] ?? '';
            $post_owner = '';
            $replied_post_index = -1;
            $replied_post_data = null;

            if (!empty($replyText)) {
                foreach ($entries as $index => &$entry) { // Gunakan & (reference)
                    if ($entry['id'] === $replyToId) {
                        $newReply = ['reply_id' => uniqid('reply_'), 'username' => $current_user, 'reply_text' => $replyText, 'date' => date('Y-m-d H:i:s')];
                        $entry['replies'][] = $newReply;
                        
                        $post_owner = $entry['username'];
                        $replied_post_index = $index;
                        $replied_post_data = $entry; // Simpan data post yang sudah diperbarui
                        break;
                    }
                }
            }
            
            if ($replied_post_index !== -1) {
                unset($entries[$replied_post_index]);
                $entries[] = $replied_post_data;
            }

            saveMoodEntries($dataFile, $entries); 

            if (!empty($post_owner) && $post_owner !== $current_user) {
                $notifFile = 'notifications.json';
                $notifications = file_exists($notifFile) ? json_decode(file_get_contents($notifFile), true) : [];
                if (!is_array($notifications)) $notifications = [];
                $newNotification = ['id' => uniqid('notif_'),'recipient_username' => $post_owner,'actor_username' => $current_user,'type' => 'reply','post_id' => $replyToId,'message' => "membalas postingan mood Anda.",'is_read' => false,'timestamp' => date('Y-m-d H:i:s')];
                $notifications[] = $newNotification;
                file_put_contents($notifFile, json_encode($notifications, JSON_PRETTY_PRINT));
            }
            header('Location: dashboard.php#post-' . $replyToId);
            exit;

        case 'create_mood':
        default:
            $newEntry = ['id' => uniqid('mood_'), 'username' => $current_user, 'mood' => $_POST['mood'] ?? '😐', 'note' => $_POST['note'] ?? '', 'date' => date('Y-m-d H:i:s'), 'replies' => []];
            $entries[] = $newEntry;
            saveMoodEntries($dataFile, $entries);
            header('Location: dashboard.php');
            exit;
    }
}

// Ambil data dan balik urutannya HANYA untuk ditampilkan
$moodEntries = array_reverse(getMoodEntries($dataFile));
$moodDescriptions = ['😄' => 'Sangat Senang', '😊' => 'Senang', '😐' => 'Biasa Saja', '😕' => 'Kurang Baik', '😠' => 'Marah'];
$editing_mood_id = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_mood' && isset($_GET['id'])) {
    $editing_mood_id = $_GET['id'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Public Feed - Mood Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="user-header">
        <span>Selamat datang, <strong><?= htmlspecialchars($current_user) ?></strong>!</span>
        <a href="logout.php">Logout</a>
    </div>

    <?php include 'menu.php'; ?>

    <?php if (!$editing_mood_id): ?>
    <div class="card">
        <h2>Bagaimana Perasaanmu Hari Ini?</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_mood">
            <div class="mood-selector">
                <?php foreach ($moodDescriptions as $emoji => $desc): ?>
                    <div class="mood-option">
                        <input type="radio" name="mood" value="<?= $emoji ?>" id="mood-<?= md5($emoji) ?>" <?= ($emoji == '😐') ? 'checked' : '' ?>>
                        <label for="mood-<?= md5($emoji) ?>">
                            <span class="emoji-icon"><?= $emoji ?></span>
                            <span class="emoji-text"><?= htmlspecialchars($desc) ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <textarea name="note" placeholder="Tulis catatan singkat di sini..."></textarea>
            <button type="submit">Simpan Catatan Mood</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>Public Mood Feed</h2>
        <ul class="history-list">
            <?php foreach ($moodEntries as $entry): ?>
                <li class="history-item" id="post-<?= $entry['id'] ?>">
                    <?php if ($editing_mood_id === $entry['id'] && $entry['username'] === $current_user): ?>
                        <form method="POST" class="edit-form">
                            <input type="hidden" name="action" value="update_mood">
                            <input type="hidden" name="id_to_update" value="<?= $entry['id'] ?>">
                            <h3>Edit Catatan Mood:</h3>
                            <textarea name="note" required><?= htmlspecialchars($entry['note']) ?></textarea>
                            <button type="submit">Simpan Perubahan</button>
                            <a href="dashboard.php">Batal</a>
                        </form>
                    <?php else: ?>
                        <div class="post-main">
                            <div class="mood-entry-header">
                                <div class="history-mood">
                                <?php
                                // Ambil emoji dari data entri
                                $emoji = $entry['mood'];
                                
                                // Ambil deskripsi dari array $moodDescriptions, jika tidak ada, kosongkan
                                $description = isset($moodDescriptions[$emoji]) ? $moodDescriptions[$emoji] : '';
                                ?>
                                
                                
                                <span class="mood-emoji-in-feed"><?= htmlspecialchars($emoji) ?></span>
                                <span class="mood-text-in-feed"><?= htmlspecialchars($description) ?></span>
                            </div>
                                <div class="history-content">
                                    <p class="history-note"><?= nl2br(htmlspecialchars($entry['note'])) ?></p>
                                    <br>
                                    <div class="history-meta">Ditulis Oleh <strong><?= htmlspecialchars($entry['username']) ?></strong> <?= date('l, d F Y', strtotime($entry['date'])) ?></div>
                                    
                                </div>
                            </div>
                            <?php if ($entry['username'] === $current_user): ?>
                                <!-- <div class="actions">
                                    <a href="?action=edit_mood&id=<?= $entry['id'] ?>" title="Edit Catatan">✏️</a>
                                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan ini?');">
                                        <input type="hidden" name="action" value="delete_mood">
                                        <input type="hidden" name="id_to_delete" value="<?= $entry['id'] ?>">
                                        <button type="submit" title="Hapus Catatan">🗑️</button>
                                    </form>
                                </div> -->
                            <?php endif; ?>
                        </div>
                        <div class="reply-section">
                            <?php if (!empty($entry['replies'])): ?>
                                <ul class="reply-list">
                                <?php foreach($entry['replies'] as $reply): ?>
                                    <li class="reply-item">
                                        <div class="reply-meta"><strong><?= htmlspecialchars($reply['username']) ?></strong> membalas:</div>
                                        <p class="reply-text"><?= nl2br(htmlspecialchars($reply['reply_text'])) ?></p>
                                        <?php if ($reply['username'] === $current_user): ?>
                                            <div class="actions reply-actions">
                                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus balasan ini?');">
                                                    <input type="hidden" name="action" value="delete_reply">
                                                    <input type="hidden" name="mood_id" value="<?= $entry['id'] ?>">
                                                    <input type="hidden" name="id_to_delete" value="<?= $reply['reply_id'] ?>">
                                                    <button type="submit" title="Hapus Balasan">🗑️</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <form method="POST" class="reply-form">
                                <input type="hidden" name="action" value="create_reply">
                                <input type="hidden" name="reply_to_id" value="<?= $entry['id'] ?>">
                                <textarea name="reply_text" placeholder="Tulis balasan..."></textarea>
                                <button type="submit">Balas</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
</body>
</html>