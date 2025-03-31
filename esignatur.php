<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF signieren</title>
    <style>
        <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
        text-align: center;
    }
    .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
        color: #333;
    }
    label {
        font-weight: bold;
        display: block;
        margin: 10px 0 5px;
    }
    input, select {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }
    button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 18px;
        cursor: pointer;
        margin-top: 15px;
        width: 100%;
        border-radius: 5px;
    }
    button:hover {
        background-color: #218838;
    }
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
