<?php
session_start();
include 'db.php';

// Validasi jika Nama dan NIM belum dimasukkan
if (!isset($_SESSION['nama']) || !isset($_SESSION['nim'])) {
    header('Location: index.php'); // Arahkan ke halaman form jika belum ada
    exit;
}

// Validasi jawaban
if (!isset($_SESSION['jawaban_user']) || empty($_SESSION['jawaban_user'])) {
    header('Location: quiz.php'); // Arahkan kembali ke kuis jika tidak ada jawaban
    exit;
}

$totalSoal = $conn->query("SELECT COUNT(*) AS total FROM soal")->fetch_assoc()['total'];
$correctAnswers = 0;

foreach ($_SESSION['jawaban_user'] as $soalId => $jawabanUser) {
    // Mengambil data soal dari database
    $query = $conn->query("SELECT jawaban FROM soal WHERE id = $soalId");

    // Pastikan query berhasil
    if ($query && $query->num_rows > 0) {
        $soal = $query->fetch_assoc();
        
        // Perbandingan jawaban yang diinputkan dengan jawaban yang benar (case-insensitive)
        if (strtoupper($jawabanUser) === strtoupper($soal['jawaban'])) {
            $correctAnswers++;
        }
    } else {
        // Skip jika soal tidak ditemukan
        continue;
    }
}

// Menghitung skor
$score = ($correctAnswers / $totalSoal) * 100;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Kuis</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-2xl rounded-xl p-8 w-11/12 sm:w-2/3 lg:w-1/2 transition-all transform hover:scale-105">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-5xl font-extrabold text-pink-600 mb-6">Hasil Kuis</h1>
            <div class="text-lg bg-pink-100 text-pink-800 rounded-lg px-6 py-3 inline-block shadow-sm">
                <p>Nama: <span class="font-bold"><?= htmlspecialchars($_SESSION['nama']) ?></span></p>
                <p>NIM: <span class="font-bold"><?= htmlspecialchars($_SESSION['nim']) ?></span></p>
            </div>
        </div>

        <!-- Hasil Skor -->
        <div class="text-center mb-10">
            <p class="text-xl font-semibold text-gray-700">
                Anda menjawab <span class="text-pink-600 font-bold"><?= $correctAnswers ?></span> dari <span class="text-pink-600 font-bold"><?= $totalSoal ?></span> soal dengan benar.
            </p>
            <div class="text-4xl font-extrabold text-gradient bg-gradient-to-r from-green-400 via-blue-500 to-purple-500 text-transparent bg-clip-text mt-3">
                Skor Anda: <?= round($score, 2) ?>%
            </div>
        </div>

        <!-- Daftar Jawaban -->
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Daftar Jawaban Anda</h2>
            <div class="space-y-6">
                <?php foreach ($_SESSION['jawaban_user'] as $soalId => $jawabanUser): ?>
                    <?php
                    $query = $conn->query("SELECT * FROM soal WHERE id = $soalId");
                    if ($query && $query->num_rows > 0) {
                        $soal = $query->fetch_assoc();
                        $jawabanBenar = $soal['jawaban'];
                    } else {
                        continue;
                    }
                    ?>
                    <div class="p-6 bg-white border border-gray-300 shadow-lg rounded-lg flex justify-between items-center">
                        <div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($soal['pertanyaan']) ?></p>
                            <p class="text-sm text-gray-500">Jawaban Anda: 
                                <span class="<?= strtoupper($jawabanUser) === strtoupper($jawabanBenar) ? 'text-green-500' : 'text-red-500' ?>">
                                    <?= strtoupper($jawabanUser) ?>
                                </span>
                            </p>
                            <p class="text-sm text-gray-500">Jawaban Benar: 
                                <span class="text-green-500"><?= strtoupper($jawabanBenar) ?></span>
                            </p>
                        </div>
                        <div class="ml-4">
                            <span class="inline-block w-10 h-10 flex items-center justify-center rounded-full <?= strtoupper($jawabanUser) === strtoupper($jawabanBenar) ? 'bg-green-500' : 'bg-red-500' ?> text-white text-lg font-bold">
                                <?= strtoupper($jawabanUser) === strtoupper($jawabanBenar) ? '✔' : '✘' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tombol Navigasi -->
        <div class="text-center space-x-4">
            <a href="quiz.php" class="px-6 py-3 bg-pink-500 text-white font-semibold rounded-lg hover:bg-pink-600 shadow-md transition duration-300">Kembali ke Kuis</a>
            <a href="index.php" class="px-6 py-3 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 shadow-md transition duration-300">Coba Lagi</a>
        </div>
    </div>
</body>
</html>
