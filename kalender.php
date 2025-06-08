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
$notesFile = 'calendar_notes.json';

// --- DAFTAR HARI LIBUR NASIONAL 2025 ---
$nationalHolidays = [
    ['title' => 'Tahun Baru 2025 Masehi', 'start' => '2025-01-01', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Tahun Baru Imlek 2576', 'start' => '2025-01-29', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Isra Mikraj Nabi Muhammad SAW', 'start' => '2025-02-27', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Hari Suci Nyepi Tahun Baru Saka 1947', 'start' => '2025-03-29', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Idul Fitri 1446 H', 'start' => '2025-03-31', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Idul Fitri 1446 H', 'start' => '2025-04-01', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Wafat Isa Al Masih', 'start' => '2025-04-18', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Hari Buruh Internasional', 'start' => '2025-05-01', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Kenaikan Isa Al Masih', 'start' => '2025-05-29', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Hari Lahir Pancasila', 'start' => '2025-06-01', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Hari Raya Waisak 2569 BE', 'start' => '2025-05-12', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Idul Adha 1446 H', 'start' => '2025-06-07', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Tahun Baru Islam 1447 H', 'start' => '2025-06-27', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Hari Kemerdekaan RI', 'start' => '2025-08-17', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Maulid Nabi Muhammad SAW', 'start' => '2025-09-05', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Hari Raya Natal', 'start' => '2025-12-25', 'color' => '#d32f2f', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Imlek', 'start' => '2025-01-28', 'color' => '#f57c00', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Hari Raya Nyepi', 'start' => '2025-03-28', 'color' => '#f57c00', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Idul Fitri', 'start' => '2025-04-02', 'color' => '#f57c00', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Idul Fitri', 'start' => '2025-04-03', 'color' => '#f57c00', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Idul Fitri', 'start' => '2025-04-04', 'color' => '#f57c00', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Waisak', 'start' => '2025-05-13', 'color' => '#f57c00', 'textColor' => '#ffffff'],
    ['title' => 'Cuti Bersama Natal', 'start' => '2025-12-26', 'color' => '#f57c00', 'textColor' => '#ffffff'],
];

// Baris untuk menyiapkan $holidayDates sudah dihapus karena tidak lagi diperlukan.

// Logika untuk menangani POST request dari AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $notes = file_exists($notesFile) ? json_decode(file_get_contents($notesFile), true) : [];
    if (!is_array($notes)) $notes = [];

    if (isset($postData['action']) && $postData['action'] === 'save_note') {
        $newNote = ['id' => uniqid('note_'),'title' => $postData['title'],'start' => $postData['date'],'username' => $current_user,'backgroundColor' => '#3788d8','borderColor' => '#3788d8'];
        $notes[] = $newNote;
        file_put_contents($notesFile, json_encode($notes, JSON_PRETTY_PRINT));
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'note' => $newNote]);
        exit;
    }
    
    if (isset($postData['action']) && $postData['action'] === 'delete_note') {
        $noteIdToDelete = $postData['id'];
        $notes = array_filter($notes, function($note) use ($noteIdToDelete, $current_user) {
            return !($note['id'] === $noteIdToDelete && $note['username'] === $current_user);
        });
        file_put_contents($notesFile, json_encode(array_values($notes), JSON_PRETTY_PRINT));
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Menyiapkan data untuk dikirim ke kalender
$userNotes = file_exists($notesFile) ? json_decode(file_get_contents($notesFile), true) : [];
if (!is_array($userNotes)) $userNotes = [];
$allEvents = array_merge($nationalHolidays, $userNotes);
$calendar_events = json_encode($allEvents);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Kolaboratif - Mood Tracker</title>
    <link rel="stylesheet" href="style.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
</head>
<body>
    <div class="container">
        <div class="user-header">
            <span>Hi, <strong><?= htmlspecialchars($current_user) ?></strong>!</span>
            <a href="logout.php">Logout</a>
        </div>
        
        <?php include 'menu.php'; ?>

        <div class="card">
            <div id='calendar'></div>
        </div>
    </div>
    <script>
        // Variabel holidayDates dan fungsi dayCellDidMount dihapus karena tidak diperlukan lagi.
        const currentUser = '<?= $current_user ?>';

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                locale: 'id',
                events: JSON.parse('<?= addslashes($calendar_events) ?>'),
                editable: false,

                eventContent: function(info) {
                    let title = info.event.title;
                    let isListView = info.view.type === 'listWeek';
                    let textColor = info.event.textColor || (isListView ? '#333' : 'white');
                    let htmlContent = '';

                    if (info.event.extendedProps.username) {
                        let user = info.event.extendedProps.username;
                        htmlContent = `
                            <div class="event-title-wrapper" style="color: ${textColor};">
                                <div class="event-title">${title}</div>
                                <div class="event-user">oleh ${user}</div>
                            </div>
                        `;
                    } else {
                        textColor = info.event.textColor || '#ffffff';
                        htmlContent = `
                            <div class="event-title-wrapper" style="color: ${textColor};">
                                <div class="event-title">${title}</div>
                            </div>
                        `;
                    }
                    return { html: htmlContent };
                },
                
                dateClick: function(info) {
                    let noteTitle = prompt('Masukkan catatan untuk tanggal ' + info.dateStr + ':');
                    if (noteTitle) {
                        fetch('kalender.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({action: 'save_note', title: noteTitle, date: info.dateStr})
                        }).then(response => response.json()).then(data => {
                            if(data.status === 'success'){ calendar.addEvent(data.note); alert('Catatan berhasil disimpan!'); }
                        });
                    }
                },

                eventClick: function(info) {
                    let isUserNote = info.event.extendedProps.username ? true : false;
                    if (isUserNote) {
                        let eventUsername = info.event.extendedProps.username;
                        let message = "Catatan: " + info.event.title + "\nOleh: " + eventUsername;
                        if (eventUsername === currentUser) {
                            if (confirm(message + "\n\nApakah Anda ingin menghapus catatan ini?")) {
                                fetch('kalender.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/json'},
                                    body: JSON.stringify({action: 'delete_note', id: info.event.id})
                                }).then(response => response.json()).then(data => {
                                    if(data.status === 'success'){ info.event.remove(); alert('Catatan berhasil dihapus!'); }
                                });
                            }
                        } else { alert(message); }
                    } else { alert("Hari Libur Nasional: " + info.event.title); }
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>