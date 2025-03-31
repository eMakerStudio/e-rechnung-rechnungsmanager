<?php
// Speicherort für hochgeladene Dateien und Metadaten
$uploadDir = __DIR__ . '/uploads/invoices/';
$invoicesFile = $uploadDir . 'invoices.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = $file['name'];
        $fileType = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Speicherort für die hochgeladene Datei vorbereiten
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFilename = time() . '-' . $filename; // Vermeidung von Konflikten durch Zeitstempel
        $filePath = $uploadDir . $newFilename;
        move_uploaded_file($file['tmp_name'], $filePath);

        // Metadaten vorbereiten
        $metadata = [];
        if ($fileType === 'json') {
            $data = json_decode(file_get_contents($filePath), true);
            $metadata = [
                'filename' => $newFilename,
                'type' => 'json',
                'invoice_number' => $data['invoice_number'] ?? 'N/A',
                'invoice_date' => $data['invoice_date'] ?? 'N/A',
                'status' => 'unpaid'
            ];
        } elseif ($fileType === 'xml') {
            $xml = simplexml_load_file($filePath);
            $data = json_decode(json_encode($xml), true);
            $metadata = [
                'filename' => $newFilename,
                'type' => 'xml',
                'invoice_number' => $data['invoice_number'] ?? 'N/A',
                'invoice_date' => $data['invoice_date'] ?? 'N/A',
                'status' => 'unpaid'
            ];
        }

        // Metadaten in JSON-Datei speichern
        $invoices = [];
        if (file_exists($invoicesFile)) {
            $invoices = json_decode(file_get_contents($invoicesFile), true);
        }
        $invoices[] = $metadata;
        file_put_contents($invoicesFile, json_encode($invoices, JSON_PRETTY_PRINT));

        echo "<p>Datei erfolgreich hochgeladen und gespeichert.</p>";
    } else {
        echo "<p>Fehler beim Hochladen der Datei.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eingangsrechnungen importieren</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        form {
            margin: 20px auto;
            text-align: center;
        }
        input[type="file"] {
            padding: 10px;
            margin: 10px 0;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-button {
            margin-top: 20px;
            text-align: center;
        }
        .back-button a {
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #007BFF;
            border-radius: 5px;
        }
        .back-button a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Eingangsrechnungen importieren</h1>

    <!-- Upload-Formular -->
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".json,.xml" required>
        <button type="submit">Datei hochladen</button>
    </form>

    <div class="back-button">
        <a href="index.php">Übersicht</a>
    </div>

    <div class="back-button">
        <a href="uploads.php">Eingangsrechnungen anzeigen</a>
    </div>
</body>
</html>