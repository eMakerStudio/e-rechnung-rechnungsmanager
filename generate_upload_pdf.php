<?php
require 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$uploadDir = __DIR__ . '/uploads/invoices/';
$invoicesFile = $uploadDir . 'invoices.json';

if (!isset($_GET['id']) || !file_exists($invoicesFile)) {
    die("Invalid request");
}

$id = $_GET['id'];
$invoices = json_decode(file_get_contents($invoicesFile), true);

if (!isset($invoices[$id])) {
    die("Invoice not found");
}

$invoice = $invoices[$id];

if ($invoice['type'] === 'xml') {
    // Lade XML-Datei
    $xmlFile = $uploadDir . $invoice['filename'];
    if (!file_exists($xmlFile)) {
        die("Invoice XML file not found");
    }
    $xml = simplexml_load_file($xmlFile);

    // Extrahiere Daten aus der XML
    $issuer = (array) $xml->issuer;
    $customer = (array) $xml->customer;
    $items = [];
    foreach ($xml->items->item as $item) {
        $items[] = (array) $item;
    }
    $bankDetails = (array) $xml->bank_details;
} elseif ($invoice['type'] === 'json') {
    // Lade JSON-Datei
    $jsonFile = $uploadDir . $invoice['filename'];
    if (!file_exists($jsonFile)) {
        die("Invoice JSON file not found");
    }
    $jsonData = json_decode(file_get_contents($jsonFile), true);

    // Extrahiere Daten aus der JSON
    $issuer = $jsonData['issuer'] ?? [];
    $customer = $jsonData['customer'] ?? [];
    $items = $jsonData['items'] ?? [];
    $bankDetails = $jsonData['bank_details'] ?? [];
} else {
    die("Unsupported file type");
}

// Generiere HTML für die Rechnung
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { text-align: center; }
        .section { margin-bottom: 20px; }
        .details, .bank { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Invoice</h1>
    <h2>Invoice Number: ' . htmlspecialchars($invoice['invoice_number']) . '</h2>

    <!-- Rechnungsersteller und Kunde -->
    <div class="section">
        <div class="details">
            <strong>From:</strong><br>
            ' . htmlspecialchars($issuer['name'] ?? '') . '<br>
            ' . htmlspecialchars($issuer['address'] ?? '') . '<br>
            ' . htmlspecialchars($issuer['city'] ?? '') . '
        </div>
        <div class="details">
            <strong>To:</strong><br>
            ' . htmlspecialchars($customer['name'] ?? '') . '<br>
            ' . htmlspecialchars($customer['address'] ?? '') . '<br>
            ' . htmlspecialchars($customer['city'] ?? '') . '
        </div>
    </div>

    <!-- Rechnungsdetails -->
    <div class="section">
        <strong>Invoice Date:</strong> ' . htmlspecialchars($invoice['invoice_date'] ?? '') . '<br>
        <strong>Status:</strong> ' . htmlspecialchars($invoice['status'] ?? '') . '
    </div>

    <!-- Artikel -->
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price (€)</th>
                <th>Total (€)</th>
            </tr>
        </thead>
        <tbody>';

$total = 0;
foreach ($items as $item) {
    $totalItem = $item['quantity'] * $item['price'];
    $total += $totalItem;
    $html .= '
        <tr>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td>' . htmlspecialchars($item['quantity']) . '</td>
            <td>' . htmlspecialchars(number_format($item['price'], 2, ',', '.')) . '</td>
            <td>' . htmlspecialchars(number_format($totalItem, 2, ',', '.')) . '</td>
        </tr>';
}

$html .= '
        </tbody>
    </table>

    <!-- Gesamtsumme -->
    <p class="total">Total: ' . htmlspecialchars(number_format($total, 2, ',', '.')) . ' €</p>

    <!-- Bankverbindung -->
    <div class="bank">
        <strong>Bank Details:</strong><br>
        Bank: ' . htmlspecialchars($bankDetails['name'] ?? 'N/A') . '<br>
        IBAN: ' . htmlspecialchars($bankDetails['iban'] ?? 'N/A') . '<br>
        BIC: ' . htmlspecialchars($bankDetails['bic'] ?? 'N/A') . '
    </div>
</body>
</html>
';

// PDF erstellen
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// PDF an den Browser senden
$dompdf->stream("invoice_" . $invoice['invoice_number'] . ".pdf", ["Attachment" => true]);
?>
