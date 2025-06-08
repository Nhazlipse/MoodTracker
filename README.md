# MoodTracker Diary 📔✨

Sebuah aplikasi web sederhana untuk mencatat mood harian dengan tampilan seperti buku diary. Aplikasi ini bersifat kolaboratif di mana pengguna bisa melihat dan berinteraksi dengan catatan di kalender bersama. Proyek ini dibangun sepenuhnya dengan PHP native untuk logika backend dan penyimpanan data berbasis file JSON.

<blockquote class="imgur-embed-pub" lang="en" data-id="a/Id7sonn"  ><a href="//imgur.com/a/Id7sonn">MoodTracker</a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>



## 🚀 Fitur Utama

- **Sistem Autentikasi**: Pengguna dapat mendaftar (`register.php`) dan masuk (`login.php`) ke akun pribadi mereka.
- **Public Mood Feed**: Halaman utama (`dashboard.php`) menampilkan semua postingan mood dari semua pengguna, diurutkan dari yang terbaru.
- **Sistem Balasan**: Setiap postingan mood dapat dibalas oleh pengguna lain.
- **CRUD Fungsional**: Pengguna dapat meng-**Edit** dan meng-**Hapus** postingan serta balasan **milik mereka sendiri**.
- **Notifikasi Dalam Aplikasi**: Pengguna akan mendapatkan notifikasi jika postingannya dibalas oleh pengguna lain, lengkap dengan lencana angka untuk notifikasi yang belum dibaca.
- **Kalender Kolaboratif**: Halaman kalender (`kalender.php`) yang menampilkan:
    - **Hari Libur Nasional & Cuti Bersama Indonesia 2025** yang sudah dimuat sebelumnya.
    - Catatan pribadi yang bisa ditambahkan oleh setiap pengguna pada tanggal tertentu.
    - Penanda visual untuk hari Minggu.
- **Desain Tematik**: Tampilan aplikasi dirancang dengan tema "Buku Diary" yang lembut dan menarik, menggunakan kombinasi warna pink teduh dan font yang estetik.


## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP (Native)
- **Frontend**: HTML5, CSS3 (Flexbox), Vanilla JavaScript
- **Penyimpanan Data**: Flat-file JSON (tanpa database)
- **Library Eksternal**:
    - [FullCalendar.js](https://fullcalendar.io/) untuk menampilkan kalender interaktif.


## ⚙️ Cara Instalasi dan Menjalankan

Untuk menjalankan proyek ini di komputer lokal Anda, ikuti langkah-langkah berikut:

1.  **Clone Repositori**:
    ```bash
    git clone https://github.com/Nhazlipse/MoodTracker.git
    ```

2.  **Pindahkan ke Server Lokal**:
    Pindahkan folder proyek yang sudah di-clone ke dalam direktori `htdocs` (jika menggunakan XAMPP) atau `www` (jika menggunakan Laragon).

3.  **Jalankan Server**:
    Nyalakan layanan Apache dari control panel XAMPP/Laragon Anda.

4.  **Buka Aplikasi**:
    Buka browser dan akses alamat `http://localhost/[nama_folder_proyek]/`. Contoh: `http://localhost/MoodTracker/register.php` untuk mendaftar akun pertama.

5.  **Selesai!**
    Aplikasi siap digunakan. File-file data seperti `users.json`, `public_feed.json`, `calendar_notes.json`, dan `notifications.json` akan dibuat secara otomatis saat aplikasi digunakan.


Dibuat dengan ❤️ PFFFTTTTTTTT!!!!