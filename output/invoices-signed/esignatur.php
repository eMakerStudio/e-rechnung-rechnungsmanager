<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF signieren</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        form { display: inline-block; text-align: left; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        input, button { display: block; margin-top: 10px; padding: 10px; width: 100%; }
        button { background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>

    <h1>PDF-Dokument signieren</h1>

    <form action="process_sign.php" method="post" enctype="multipart/form-data">
        <label for="pdf_file">PDF hochladen:</label>
        <input type="file" name="pdf_file" accept="application/pdf" required>

        <label for="signature_logo">Signatur-Logo hochladen (optional):</label>
        <input type="file" name="signature_logo" accept="image/*">

        <label for="sign_date">Nur mit Datum signieren?</label>
        <input type="checkbox" name="sign_date" value="yes">

        <button type="submit">PDF signieren</button>
    </form>

</body>
</html>
