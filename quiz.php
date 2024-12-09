<?php
session_start();
include 'db.php';

// Fungsi untuk memuat soal dari file teks ke database (dijalankan hanya sekali)
function loadSoal($conn, $filename = 'soal.txt') {
    if (!file_exists($filename)) return;

    $content = file_get_contents($filename);
    $questions = preg_split('/\n\s*\n/', trim($content));

    foreach ($questions as $q) {
        $lines = explode("\n", $q);
        $question = '';
        $options = [];
        $answer = '';
        $gambar = '';

        foreach ($lines as $line) {
            if (strpos($line, 'Pertanyaan:') === 0) {
                $question = trim(substr($line, strlen('Pertanyaan:')));
            } elseif (strpos($line, 'A:') === 0) {
                $options['A'] = trim(substr($line, 2));
            } elseif (strpos($line, 'B:') === 0) {
                $options['B'] = trim(substr($line, 2));
            } elseif (strpos($line, 'C:') === 0) {
                $options['C'] = trim(substr($line, 2));
            } elseif (strpos($line, 'D:') === 0) {
                $options['D'] = trim(substr($line, 2));
            } elseif (strpos($line, 'Jawaban:') === 0) {
                $answer = trim(substr($line, strlen('Jawaban:')));
            } elseif (strpos($line, 'Gambar:') === 0) {
                $gambar = trim(substr($line, strlen('Gambar:')));
            }
        }

        if ($question && $answer && count($options) === 4) {
            $stmtCheck = $conn->prepare("SELECT id FROM soal WHERE pertanyaan = ?");
            $stmtCheck->bind_param("s", $question);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows === 0) {
                $stmtInsert = $conn->prepare("INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtInsert->bind_param(
                    "sssssss",
                    $question,
                    $options['A'],
                    $options['B'],
                    $options['C'],
                    $options['D'],
                    strtoupper($answer),
                    $gambar
                );
                $stmtInsert->execute();
            }
        }
    }
}

// Jalankan loadSoal sekali (jika perlu, bisa dihapus setelah selesai digunakan)
loadSoal($conn);

// Randomisasi soal jika sesi baru dimulai
if (!isset($_SESSION['soal_ids'])) {
    $result = $conn->query("SELECT id FROM soal");
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    shuffle($ids);
    $_SESSION['soal_ids'] = $ids;
    $_SESSION['current_soal'] = 0;
    $_SESSION['jawaban_user'] = [];
}

// Navigasi soal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['jawaban'])) {
        $_SESSION['jawaban_user'][$_SESSION['current_soal']] = $_POST['jawaban'];
    }

    if (isset($_POST['next'])) {
        $_SESSION['current_soal'] = min($_SESSION['current_soal'] + 1, count($_SESSION['soal_ids']) - 1);
    } elseif (isset($_POST['prev'])) {
        $_SESSION['current_soal'] = max($_SESSION['current_soal'] - 1, 0);
    } elseif (isset($_POST['finish'])) {
        header('Location: result.php'); // Pengalihan setelah selesai
        exit;
    } elseif (isset($_POST['reset'])) {
        session_destroy();
        header('Location: quiz.php');
        exit;
    }
}

// Cek soal yang akan ditampilkan
$currentIndex = $_SESSION['current_soal'];
$currentId = $_SESSION['soal_ids'][$currentIndex] ?? null;

if ($currentId) {
    $query = $conn->query("SELECT * FROM soal WHERE id = $currentId");
    $soal = $query->fetch_assoc();
}

// Navigasi soal berdasarkan GET (jika nomor soal dipilih langsung)
if (isset($_GET['soal'])) {
    $newSoal = (int) $_GET['soal'];
    if ($newSoal >= 0 && $newSoal < count($_SESSION['soal_ids'])) {
        $_SESSION['current_soal'] = $newSoal;
        header('Location: quiz.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis Pilihan Ganda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-50 to-green-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="bg-blue-600 shadow-lg text-white py-4">
        <div class="container mx-auto flex justify-between items-center px-4">
            <h1 class="text-2xl font-bold">Kuis Pilihan Ganda</h1>
            <p class="text-sm">Soal <?= $currentIndex + 1 ?> dari <?= count($_SESSION['soal_ids']) ?></p>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container mx-auto mt-6 px-4 flex flex-col lg:flex-row gap-8">
        <!-- Navigation Sidebar -->
        <div class="bg-white shadow-md rounded-lg p-4 lg:w-1/4">
            <h2 class="text-lg font-semibold mb-4">Navigasi Soal</h2>
            <div class="grid grid-cols-5 gap-2">
                <?php for ($i = 0; $i < count($_SESSION['soal_ids']); $i++): ?>
                    <a href="quiz.php?soal=<?= $i ?>" 
                       class="block text-center p-2 rounded-lg text-white font-semibold 
                       <?= isset($_SESSION['jawaban_user'][$i]) ? 'bg-green-500' : 'bg-blue-400' ?> 
                       hover:bg-green-600 transition">
                        <?= $i + 1 ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Question Section -->
        <div class="bg-white shadow-md rounded-lg p-6 lg:w-3/4">
            <?php if (isset($soal)): ?>
                <?php if (!empty($soal['gambar'])): ?>
                    <div class="mb-4">
                        <img src="<?= htmlspecialchars($soal['gambar']) ?>" alt="Gambar Soal" class="rounded-md shadow-md">
                    </div>
                <?php endif; ?>

                <p class="text-xl font-semibold text-gray-700 mb-6"><?= $soal['pertanyaan'] ?></p>
                <form method="POST" class="space-y-4">
                    <?php foreach (['a', 'b', 'c', 'd'] as $opsi): ?>
                        <label class="flex items-center space-x-3">
                            <input type="radio" name="jawaban" value="<?= strtoupper($opsi) ?>" 
                                <?= (isset($_SESSION['jawaban_user'][$currentIndex]) && $_SESSION['jawaban_user'][$currentIndex] === strtoupper($opsi)) ? 'checked' : '' ?> 
                                class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-gray-600"><?= strtoupper($opsi) ?>. <?= $soal["opsi_$opsi"] ?></span>
                        </label>
                    <?php endforeach; ?>

                    <!-- Buttons -->
                    <div class="flex justify-between mt-6">
                        <button type="submit" name="prev" 
                                class="px-6 py-2 bg-gray-400 text-white rounded-lg shadow-md hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed" 
                                <?= $currentIndex == 0 ? 'disabled' : '' ?>>
                            Sebelumnya
                        </button>
                        <?php if ($currentIndex < count($_SESSION['soal_ids']) - 1): ?>
                            <button type="submit" name="next" 
                                    class="px-6 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600">
                                Selanjutnya
                            </button>
                        <?php else: ?>
                            <button type="submit" name="finish" 
                                    class="px-6 py-2 bg-green-500 text-white rounded-lg shadow-md hover:bg-green-600">
                                Selesai
                            </button>
                        <?php endif; ?>
                        <button type="submit" name="reset" 
                                class="px-6 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600">
                            Reset
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

