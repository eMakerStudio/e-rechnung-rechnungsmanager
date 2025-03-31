<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Barbara Hohensee">
    <meta name="version" content="1.0.0">
    <meta name="last-updated" content="2024-12-10">

    <title>Rechnung erstellen</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: 0 auto; }
        label { font-weight: bold; margin-top: 10px; display: block; }
        input, textarea, button { width: 100%; margin-top: 5px; padding: 10px; font-size: 16px; }
        .item-container { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>Rechnung erstellen</h1>
    <form action="generate.php" method="post" enctype="multipart/form-data">
        <!-- Rechnungsnummer und Datum -->
        <label for="invoice_number">Rechnungsnummer:</label>
        <input type="text" id="invoice_number" name="invoice_number" required>
        <label for="invoice_date">Rechnungsdatum:</label>
        <input type="date" id="invoice_date" name="invoice_date" required>

        <!-- Rechnungssteller -->
        <h2>Rechnungssteller</h2>
        <?php
        $issuerData = file_exists('output/issuer.json') ? json_decode(file_get_contents('output/issuer.json'), true) : [];
        $bankDetails = file_exists('output/bank.json') ? json_decode(file_get_contents('output/bank.json'), true) : [];
        ?>
        <label for="issuer_name">Name:</label>
        <input type="text" id="issuer_name" name="issuer_name" value="<?php echo $issuerData['name'] ?? ''; ?>" required>
        <label for="issuer_address">Straße:</label>
        <input type="text" id="issuer_address" name="issuer_address" value="<?php echo $issuerData['address'] ?? ''; ?>" required>
        <label for="issuer_city">Postleitzahl und Stadt:</label>
        <input type="text" id="issuer_city" name="issuer_city" value="<?php echo $issuerData['city'] ?? ''; ?>" required>

        <!-- Kunde -->
        <h2>Kunde</h2>
        <label for="customer_name">Kundenname:</label>
        <input type="text" id="customer_name" name="customer_name" required>
        <label for="customer_address">Straße:</label>
        <input type="text" id="customer_address" name="customer_address" required>
        <label for="customer_city">Postleitzahl und Stadt:</label>
        <input type="text" id="customer_city" name="customer_city" required>
        <label for="customer_number">Kundennummer:</label>
        <input type="text" id="customer_number" name="customer_number">

        <!-- Artikel -->
        <h2>Rechnungsartikel</h2>
        <div id="items-container">
            <div class="item-container">
                <label>Artikelname:</label>
                <input type="text" name="items[0][name]" required>
                <label>Menge:</label>
                <input type="number" name="items[0][quantity]" min="1" required>
                <label>Preis pro Stück (€):</label>
                <input type="number" step="0.01" name="items[0][price]" required>
            </div>
        </div>
        <button type="button" id="add-item">Weitere Artikel hinzufügen</button>

        <!-- Bankverbindung -->
        <h2>Bankverbindung</h2>
        <label for="bank_name">Bankname:</label>
        <input type="text" id="bank_name" name="bank_name" value="<?php echo $bankDetails['name'] ?? ''; ?>" required>
        <label for="bank_iban">IBAN:</label>
        <input type="text" id="bank_iban" name="bank_iban" value="<?php echo $bankDetails['iban'] ?? ''; ?>" required>
        <label for="bank_bic">BIC:</label>
        <input type="text" id="bank_bic" name="bank_bic" value="<?php echo $bankDetails['bic'] ?? ''; ?>" required>


    <!-- Signatur-Option -->
    <h2>Optionale Signatur</h2>
    <label for="signature_logo">Signatur-Logo hochladen (optional):</label>
    <input type="file" name="signature_logo" accept="image/*"><br>
    <label for="sign_document">Dokument signieren?</label>
    <input type="checkbox" name="sign_document" value="yes">

     <!-- Absenden -->
     <button type="submit">Rechnung erstellen</button>

    </form>


    <script>
        document.getElementById('add-item').addEventListener('click', function () {
            const container = document.getElementById('items-container');
            const itemIndex = container.children.length;
            const itemDiv = document.createElement('div');
            itemDiv.className = 'item-container';
            itemDiv.innerHTML = `
                <label>Artikelname:</label>
                <input type="text" name="items[${itemIndex}][name]" required>
                <label>Menge:</label>
                <input type="number" name="items[${itemIndex}][quantity]" min="1" required>
                <label>Preis pro Stück (€):</label>
                <input type="number" step="0.01" name="items[${itemIndex}][price]" required>
            `;
            container.appendChild(itemDiv);
        });
    </script>
</body>
</html>
