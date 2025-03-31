<?php
$uploadDir = __DIR__ . '/uploads/invoices/';
$invoicesFile = $uploadDir . 'invoices.json';
$invoices = [];

if (file_exists($invoicesFile)) {
    $invoices = json_decode(file_get_contents($invoicesFile), true);
}

// Toggle invoice status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $index = $_POST['toggle_status'];
    $invoices[$index]['status'] = $invoices[$index]['status'] === 'bezahlt' ? 'unbezahlt' : 'bezahlt';
    file_put_contents($invoicesFile, json_encode($invoices, JSON_PRETTY_PRINT));
    header('Location: uploads.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Invoices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
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
        .navigation-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .navigation-buttons .button {
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #007BFF;
            border-radius: 5px;
            margin: 0 10px;
        }
        .navigation-buttons .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Eingangsrechnungen</h1>

    <!-- Navigation Buttons -->
    <div class="navigation-buttons">
        <a href="index.php" class="button">Übersicht</a>
        <!-- <a href="rechnung.php" class="button">Neue Rechnung erstellen</a>
        <a href="dashboard.php" class="button">Liste Ausgangsrechnungen</a> -->
    </div>

    <!-- Invoice Table -->
    <table>
        <thead>
            <tr>
                <th>Dateiname</th>
                <th>Rechnungsnummer</th>
                <th>Rechnungsdatum</th>
                <th>Status</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($invoices as $index => $invoice) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($invoice['filename']) . '</td>';
                echo '<td>' . htmlspecialchars($invoice['invoice_number']) . '</td>';
                echo '<td>' . htmlspecialchars($invoice['invoice_date']) . '</td>';

                // Status toggle button
                $statusText = $invoice['status'] === 'bezahlt' ? 'bezahlt' : 'unbezahlt';
                echo '<td>';
                echo '<form method="post" style="display: inline;">';
                echo '<input type="hidden" name="toggle_status" value="' . $index . '">';
                echo '<button type="submit">' . htmlspecialchars($statusText) . '</button>';
                echo '</form>';
                echo '</td>';

                // echo '<td><a href="' . $uploadDir . $invoice['filename'] . '" download>Download</a></td>';
                echo '<td><a href="generate_upload_pdf.php?id=' . $index . '">Download PDF</a></td>';

                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</body>
</html>
