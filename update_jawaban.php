<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_soal = intval($_POST['current_soal'] ?? -1);
    $jawaban = $_POST['jawaban'] ?? '';

    if ($current_soal >= 0 && !empty($jawaban)) {
        $_SESSION['jawaban_user'][$current_soal] = strtoupper($jawaban);
    }
}
