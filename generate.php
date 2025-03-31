<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'libs/dompdf/autoload.inc.php';
require 'libs/fpdi/autoload.php'; // FPDI ohne Composer laden
require 'libs/fpdi/fpdf.php';

use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = $_POST;

    // Signatur-Option abrufen
    $signDocument = isset($data['sign_document']);
    
    // Rechnungssteller-Daten speichern
    $issuerData = [
        'name' => $data['issuer_name'],
        'address' => $data['issuer_address'],
        'city' => $data['issuer_city']
    ];
    file_put_contents('output/issuer.json', json_encode($issuerData));

    // Bankverbindung speichern
    $bankDetails = [
     'name' => $data['bank_name'],
     'iban' => $data['bank_iban'],
     'bic' => $data['bank_bic']
];
file_put_contents('output/bank.json', json_encode($bankDetails));

    // HTML-Vorlage laden
    $templatePath = __DIR__ . '/templates/invoice-template.php';
    $html = file_get_contents($templatePath);
    if (!$html) {
        die('Fehler: Die HTML-Vorlage konnte nicht geladen werden.');
    }

    // Artikel in Tabelle einfügen
    $itemRows = '';
    $totalAmount = 0;
    foreach ($data['items'] as $index => $item) {
        if (!empty($item['name']) && !empty($item['quantity']) && !empty($item['price'])) {
            $itemTotal = $item['quantity'] * $item['price'];
            $totalAmount += $itemTotal;
            $itemRows .= "
                <tr>
                    <td>" . ($index + 1) . "</td>
                    <td>{$item['quantity']}</td>
                    <td>{$item['name']}</td>
                    <td>" . number_format($itemTotal, 2, ',', '.') . " €</td>
                </tr>
            ";
        }
    }

   // Platzhalter ersetzen
$html = str_replace(
    ['{invoice_number}', '{invoice_date}', '{issuer_name}', '{issuer_address}', '{issuer_city}', '{customer_name}', '{customer_address}', '{customer_city}', '{customer_number}', '{bank_name}', '{bank_iban}', '{bank_bic}', '{items}', '{total}'],
    [
        $data['invoice_number'],
        $data['invoice_date'],
        $data['issuer_name'],
        $data['issuer_address'],
        $data['issuer_city'],
        $data['customer_name'],
        $data['customer_address'],
        $data['customer_city'],
        $data['customer_number'] ?? 'N/A',
        $bankDetails['name'] ?? 'N/A',
        $bankDetails['iban'] ?? 'N/A',
        $bankDetails['bic'] ?? 'N/A',
        $itemRows,
        number_format($totalAmount, 2, ',', '.')
    ],
    $html
);
    // PDF mit Dompdf erstellen
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();

    // PDF speichern
    $pdfFilePath = "output/invoices/{$data['invoice_number']}.pdf";
    file_put_contents($pdfFilePath, $pdfOutput);

    // JSON e-Rechnung erstellen
    $eInvoiceData = [
         'invoice_number' => $data['invoice_number'],
         'invoice_date' => $data['invoice_date'], // Rechnungsdatum hinzufügen
         'issuer' => $issuerData,
         'customer' => [
             'name' => $data['customer_name'],
             'address' => $data['customer_address'],
             'city' => $data['customer_city']
           ],
         'items' => $data['items'],
         'total' => $totalAmount,
         'bank_details' => $bankDetails
        ];
    $jsonFilePath = "output/e-invoices/{$data['invoice_number']}.json";
    file_put_contents($jsonFilePath, json_encode($eInvoiceData, JSON_PRETTY_PRINT));

    // Signatur-Logo verarbeiten (falls hochgeladen)
    $signatureLogoPath = '';
    if (!empty($_FILES['signature_logo']['name'])) {
        $uploadDir = __DIR__ . '/output/signatures/';
        $fileName = basename($_FILES['signature_logo']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['signature_logo']['tmp_name'], $targetPath)) {
            $signatureLogoPath = $targetPath;
        }
    }

    // Falls Signatur gewünscht ist
    if ($signDocument) {
        
        // Private Key und Zertifikat
        $privateKeyPath = __DIR__ . '/secure_keys/private_key.pem';
        $certificatePath = __DIR__ . '/secure_keys/certificate.pem';

        if (!file_exists($privateKeyPath)) {
            die("Fehler: Private Key nicht gefunden unter $privateKeyPath");
        }
        
        if (!file_exists($certificatePath)) {
            die("Fehler: Zertifikat nicht gefunden unter $certificatePath");
        }

        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
        if ($privateKey === false) {
             die("Fehler beim Laden des privaten Schlüssels: " . openssl_error_string());
}   
        

        if (file_exists($privateKeyPath) && file_exists($certificatePath)) {
            // PDF-Hash erstellen
            $pdfHash = hash_file('sha256', $pdfFilePath, true);

            // Hash mit OpenSSL signieren
            $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
            openssl_sign($pdfHash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            //openssl_free_key($privateKey);
            //opnessl_pkey_free($privateKey);

            // Signatur speichern
            $signatureFile = "output/invoices/{$data['invoice_number']}.sig";
            file_put_contents($signatureFile, $signature);

            // Signatur visuell einfügen mit FPDI
            $pdf = new Fpdi();
            $pdf->AddPage();
            $pdf->setSourceFile($pdfFilePath);
            $templateId = $pdf->importPage(1);
            $pdf->useTemplate($templateId, 0, 0, 210);

            // Signatur-Logo einfügen (unten rechts)
            if (!empty($signatureLogoPath)) {
                $pdf->Image($signatureLogoPath, 150, 240, 40);
            }

            // Signatur-Info als Text hinzufügen
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(140, 260);
            $pdf->Cell(50, 10, 'Signiert am: ' . date('d.m.Y'), 0, 0, 'C');

            // Neue signierte PDF speichern
            $signedPdfFilePath = "output/invoices-signed/{$data['invoice_number']}-signed.pdf";
            $pdf->Output($signedPdfFilePath, 'F');
        }
    }




    // Weiterleitung zum Dashboard
    header("Location: dashboard.php");
    exit;
}
?>
