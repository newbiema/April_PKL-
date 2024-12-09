<?php
session_start();

// Validasi jika sudah ada session identitas
if (isset($_SESSION['nama']) && isset($_SESSION['nim'])) {
    header('Location: quiz.php'); // Arahkan ke halaman kuis jika sudah ada data
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simpan data identitas dalam session
    $_SESSION['nama'] = $_POST['nama'];
    $_SESSION['nim'] = $_POST['nim'];
    header('Location: quiz.php'); // Arahkan ke halaman kuis
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Kuis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-100 to-blue-200 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-lg text-center relative">
        <!-- Decorative Elements -->
        <div class="absolute -top-5 -right-5 bg-blue-300 w-24 h-24 rounded-full opacity-50 blur-lg"></div>
        <div class="absolute -bottom-5 -left-5 bg-green-300 w-24 h-24 rounded-full opacity-50 blur-lg"></div>

        <!-- Hero Section -->
        <h1 class="text-4xl font-bold text-blue-700 mb-4">Selamat Datang</h1>
        <p class="text-gray-600 text-base mb-6">
            Harap masukkan nama dan NIM Anda sebelum memulai kuis.
        </p>

        <!-- Form Identitas -->
        <form method="POST" class="space-y-5">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-600">Nama</label>
                <input type="text" id="nama" name="nama" required 
                       class="mt-2 block w-full px-4 py-3 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="nim" class="block text-sm font-medium text-gray-600">NIM</label>
                <input type="text" id="nim" name="nim" required 
                       class="mt-2 block w-full px-4 py-3 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" 
                    class="w-full px-6 py-3 bg-blue-500 text-white text-sm font-semibold rounded-lg shadow hover:bg-blue-600 transform hover:scale-105 transition-all">
                Mulai Kuis
            </button>
        </form>

        <!-- Footer Note -->
        <p class="text-xs text-gray-400 mt-6">Data hanya akan digunakan untuk keperluan kuis ini.</p>
    </div>
</body>
</html>

