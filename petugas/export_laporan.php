<?php
session_start();
require_once('../config/config.php');
checkLevel([2]);

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        return date('d M Y', strtotime($tanggal));
    }
}

function getLaporanRows($conn, $tanggal_dari, $tanggal_sampai) {
    $tanggal_dari = mysqli_real_escape_string($conn, $tanggal_dari);
    $tanggal_sampai = mysqli_real_escape_string($conn, $tanggal_sampai);
    $result = mysqli_query(
        $conn,
        "SELECT l.*, b.nama_barang, b.harga_awal, u.nama_lengkap AS pemenang
         FROM tb_lelang l
         JOIN tb_barang b ON l.id_barang = b.id_barang
         LEFT JOIN tb_user u ON l.id_user = u.id_user
         WHERE l.tgl_lelang BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
         ORDER BY l.tgl_lelang DESC"
    );
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function pdfEscape($text) {
    $text = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $text);
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    return preg_replace('/[^\x20-\x7E\x80-\xFF]/', '', $text);
}

function buildSimplePdf(array $lines, $title) {
    $pageWidth = 842;
    $pageHeight = 595;
    $left = 30;
    $top = 560;
    $lineHeight = 14;
    $maxLinesPerPage = 36;
    $pages = array_chunk($lines, $maxLinesPerPage);
    $objects = [];
    $pageObjectIds = [];
    $fontId = 2;
    $pagesId = 1;
    $objects[$fontId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $nextId = 3;

    foreach ($pages as $pageLines) {
        $content = "BT\n/F1 11 Tf\n";
        $content .= sprintf("1 0 0 1 %d %d Tm\n", $left, $top);
        $content .= '(' . pdfEscape($title) . ") Tj\n";
        $content .= "0 -" . ($lineHeight + 4) . " Td\n";
        $content .= "/F1 9 Tf\n";
        foreach ($pageLines as $line) {
            $content .= '(' . pdfEscape($line) . ") Tj\n";
            $content .= "0 -$lineHeight Td\n";
        }
        $content .= "ET";
        $contentId = $nextId++;
        $pageId = $nextId++;
        $objects[$contentId] = "<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream";
        $objects[$pageId] = "<< /Type /Page /Parent $pagesId 0 R /MediaBox [0 0 $pageWidth $pageHeight] /Contents $contentId 0 R /Resources << /Font << /F1 $fontId 0 R >> >> >>";
        $pageObjectIds[] = $pageId;
    }

    $kidRefs = [];
    foreach ($pageObjectIds as $id) {
        $kidRefs[] = $id . ' 0 R';
    }
    $objects[$pagesId] = "<< /Type /Pages /Kids [ " . implode(' ', $kidRefs) . " ] /Count " . count($pageObjectIds) . " >>";
    $catalogId = $nextId++;
    $objects[$catalogId] = "<< /Type /Catalog /Pages $pagesId 0 R >>";

    ksort($objects);
    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $id => $object) {
        $offsets[$id] = strlen($pdf);
        $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . ($catalogId + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= $catalogId; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
    }
    $pdf .= "trailer << /Size " . ($catalogId + 1) . " /Root $catalogId 0 R >>\n";
    $pdf .= "startxref\n$xrefOffset\n%%EOF";
    return $pdf;
}

$tanggal_dari = $_GET['dari'] ?? date('Y-m-01');
$tanggal_sampai = $_GET['sampai'] ?? date('Y-m-d');
$format = strtolower($_GET['format'] ?? '');
$rows = getLaporanRows($conn, $tanggal_dari, $tanggal_sampai);

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan-lelang-petugas-' . $tanggal_dari . '-sampai-' . $tanggal_sampai . '.xls');
    ?>
    <table border="1">
        <tr><th colspan="7">Laporan Lelang Periode <?php echo htmlspecialchars(formatTanggal($tanggal_dari)); ?> s/d <?php echo htmlspecialchars(formatTanggal($tanggal_sampai)); ?></th></tr>
        <tr><th>No</th><th>Tanggal</th><th>Nama Barang</th><th>Harga Awal</th><th>Harga Akhir</th><th>Pemenang</th><th>Status</th></tr>
        <?php foreach ($rows as $index => $row): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo htmlspecialchars(formatTanggal($row['tgl_lelang'])); ?></td>
            <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
            <td><?php echo htmlspecialchars(formatRupiah($row['harga_awal'])); ?></td>
            <td><?php echo htmlspecialchars(formatRupiah($row['harga_akhir'])); ?></td>
            <td><?php echo htmlspecialchars($row['pemenang'] ?: '-'); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
    exit;
}

if ($format === 'pdf') {
    $lines = [];
    $lines[] = 'Periode: ' . formatTanggal($tanggal_dari) . ' s/d ' . formatTanggal($tanggal_sampai);
    $lines[] = str_repeat('-', 140);
    $lines[] = 'No | Tanggal | Nama Barang | Harga Awal | Harga Akhir | Pemenang | Status';
    $lines[] = str_repeat('-', 140);
    foreach ($rows as $index => $row) {
        $lines[] = sprintf(
            '%02d | %s | %s | %s | %s | %s | %s',
            $index + 1,
            formatTanggal($row['tgl_lelang']),
            substr($row['nama_barang'], 0, 28),
            formatRupiah($row['harga_awal']),
            formatRupiah($row['harga_akhir']),
            substr($row['pemenang'] ?: '-', 0, 20),
            strtoupper($row['status'])
        );
    }
    if (count($rows) === 0) {
        $lines[] = 'Tidak ada data laporan untuk periode ini.';
    }
    $pdf = buildSimplePdf($lines, 'Laporan Lelang Petugas');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=laporan-lelang-petugas-' . $tanggal_dari . '-sampai-' . $tanggal_sampai . '.pdf');
    echo $pdf;
    exit;
}

header('Location: laporan.php?dari=' . urlencode($tanggal_dari) . '&sampai=' . urlencode($tanggal_sampai));
exit;
