<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechnungsliste</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        .button-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-container a {
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #4CAF50;
            border-radius: 5px;
            border: none;
        }
        .button-container a:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        .search-bar input {
            padding: 10px;
            font-size: 16px;
            width: 300px;
        }
        .search-bar button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #45a049;
        }
        .delete-link {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        .delete-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Rechnungsliste</h1>

     <!-- Button "Neue Rechnung erstellen oder importieren" -->
    <div class="button-container">
    <a href="index.php">Übersicht</a>
    <!-- <a href="upload.php" style="background-color: #007BFF;">Eingangsrechnung importieren</a> -->
    </div>

    <!-- Suchfunktion -->
    <div class="search-bar">
        <form method="get" action="dashboard.php">
            <input type="text" name="search" placeholder="Rechnungsnummer oder Kunde" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit">Suchen</button>
        </form>
    </div>

    <!-- Tabelle der Rechnungen -->
    <table>
        <thead>
            <tr>
                <th>Rechnungsnummer</th>
                <th>Datum</th>
                <th>Kunde</th>
                <th>Gesamtbetrag (€)</th>
                <th>PDF</th>
                <th>JSON (e-Rechnung)</th>
                <th>Signierte PDF</th>
                <th>Löschen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            ob_start();

            // Rechnungen aus dem Verzeichnis 'output/e-invoices/' laden
            $invoicesDir = __DIR__ . '/output/e-invoices/';
            $pdfDir = __DIR__ . '/output/invoices/';
            $searchTerm = $_GET['search'] ?? '';
            $files = glob($invoicesDir . '*.json');

            $monthlyTotals = []; // Zwischensummen pro Monat

            // Prüfen, ob eine Rechnung gelöscht werden soll
            if (isset($_GET['delete'])) {
                $invoiceNumber = $_GET['delete'];
                $jsonFile = $invoicesDir . $invoiceNumber . '.json';
                $pdfFile = $pdfDir . $invoiceNumber . '.pdf';

                // Dateien löschen
                if (file_exists($jsonFile)) {
                    unlink($jsonFile);
                }
                if (file_exists($pdfFile)) {
                    unlink($pdfFile);
                }


        echo '<script>window.location.href="dashboard.php";</script>';
        exit;
                
            }


            if (!$files) {
                echo '<tr><td colspan="7">Keine Rechnungen gefunden.</td></tr>';
            } else {
                foreach ($files as $file) {
                    $data = json_decode(file_get_contents($file), true);
                    $invoiceNumber = $data['invoice_number'] ?? 'N/A';
                    $date = $data['invoice_date'] ?? null;
                    $customerName = $data['customer']['name'] ?? 'N/A';
                    $total = $data['total'] ?? 0;

                    // Formatierung und Zwischensumme pro Monat
                    if ($date) {
                        $monthYear = date('Y-m', strtotime($date));
                        if (!isset($monthlyTotals[$monthYear])) {
                            $monthlyTotals[$monthYear] = 0; // Monat initialisieren
                        }
                        $monthlyTotals[$monthYear] += $total; // Betrag zum Monat hinzufügen
                    }

                    // Suchfilter anwenden
                    if ($searchTerm && stripos($invoiceNumber . $customerName, $searchTerm) === false) {
                        continue;
                    }

                    $pdfPath = 'output/invoices/' . $invoiceNumber . '.pdf';
                    $signedPdfFilePath = 'output/invoices-signed/' . $invoiceNumber . '-signed.pdf';
                    $jsonPath = 'output/e-invoices/' . basename($file);

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($invoiceNumber) . '</td>';
                    echo '<td>' . htmlspecialchars($date) . '</td>';
                    echo '<td>' . htmlspecialchars($customerName) . '</td>';
                    echo '<td>' . number_format($total, 2, ',', '.') . '</td>';
                    echo '<td><a href="' . htmlspecialchars($pdfPath) . '" download>PDF</a></td>';
                    echo '<td><a href="' . htmlspecialchars($jsonPath) . '" download>JSON</a></td>';
                    echo '<td><a href="' . htmlspecialchars($signedPdfFilePath) . '" download>Signierte PDF</a></td>';
                    echo '<td><a href="?delete=' . htmlspecialchars($invoiceNumber) . '" class="delete-link">Löschen</a></td>';
                    echo '</tr>';
                }

                // Zwischensummen anzeigen
                echo '<tr><td colspan="7"><strong>Zwischensummen pro Monat:</strong></td></tr>';
                foreach ($monthlyTotals as $monthYear => $subtotal) {
                    echo '<tr>';
                    echo '<td colspan="3"><strong>' . htmlspecialchars($monthYear) . '</strong></td>';
                    echo '<td colspan="4"><strong>' . number_format($subtotal, 2, ',', '.') . ' €</strong></td>';
                    echo '</tr>';
                }
            }
            ob_end_flush();
            ?>
        </tbody>
    </table>


</body>
</html>
