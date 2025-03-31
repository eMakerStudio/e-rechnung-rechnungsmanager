<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'libs/fpdf/fpdf.php';
require 'libs/fpdi/autoload.php';

use setasign\Fpdi\Fpdi;

$uploadDir = __DIR__ . '/upload_pdf/';


// Prüfen, ob eine PDF-Datei hochgeladen wurde
if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    die("Fehler: Keine PDF-Datei hochgeladen.");
}

$pdfFile = $_FILES['pdf_file'];
$uploadedFilePath = $uploadDir . basename($pdfFile['name']);

// Datei speichern
if (!move_uploaded_file($pdfFile['tmp_name'], $uploadedFilePath)) {
    die("Fehler: PDF konnte nicht gespeichert werden.");
}

// Prüfen, ob ein Logo hochgeladen wurde
$logoPath = null;
if (isset($_FILES['signature_logo']) && $_FILES['signature_logo']['error'] === UPLOAD_ERR_OK) {
    $logoPath = $uploadDir . basename($_FILES['signature_logo']['name']);
    move_uploaded_file($_FILES['signature_logo']['tmp_name'], $logoPath);
}

// Prüfen, ob nur das Datum verwendet werden soll
$signWithDateOnly = isset($_POST['sign_date']) && $_POST['sign_date'] === 'yes';

// Signierte Datei speichern als "-signed.pdf"
$signedFilePath = str_replace('.pdf', '-signed.pdf', $uploadedFilePath);

// PDF öffnen und signieren
$pdf = new FPDI();
$pageCount = $pdf->setSourceFile($uploadedFilePath);

for ($i = 1; $i <= $pageCount; $i++) {
    $pdf->AddPage();
    $tplIdx = $pdf->importPage($i);
    $pdf->useTemplate($tplIdx, 0, 0);

    // Signatur setzen
    if ($signWithDateOnly) {
        // Nur Datum als Signatur
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(255, 0, 0); // Rote Schrift
        $pdf->SetXY(150, 260); // Position auf der PDF
        $pdf->Cell(40, 10, "Signiert am: " . date('d.m.Y'));
    } elseif ($logoPath) {
        // Logo als Signatur
        $pdf->Image($logoPath, 150, 250, 40); // x = 150, y = 250, Breite = 40 mm
    }
}

// Signierte PDF speichern
$pdf->Output($signedFilePath, 'F');
// Relativer URL-Pfad für den Download-Link erstellen
$relativeSignedFilePath = 'upload_pdf/' . urlencode(basename($signedFilePath));

// Erfolgsseite ausgeben
echo '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokument signiert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 50px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #28a745;
        }
        .button {
            display: inline-block;
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            transition: 0.3s;
            margin: 10px;
        }
        .button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✅ Dokument erfolgreich signiert!</h2>
        <p><strong>Download:</strong></p>
        <a href="' . $relativeSignedFilePath . '" download class="button">📄 Signierte PDF herunterladen</a>
        <br><br>
        <a href="esignatur.php" class="button" style="background-color: #007bff;">🔄 Neues Dokument signieren</a>
        <a href="index.php" class="button" style="background-color: #007bff;"> Hauptmenü</a>
    </div>
</body>
</html>';
exit;

?>
