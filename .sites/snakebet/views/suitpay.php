<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], button {
            margin-bottom: 20px;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Configurações</h1>
        <form method="POST" action="../php/save_config.php">
            <label for="ci">CI:</label>
            <input type="text" name="ci" id="ci" required>

            <label for="cs">CS:</label>
            <input type="text" name="cs" id="cs" required>

            <label for="suitpay_url">Suitpay URL:</label>
            <input type="text" name="suitpay_url" id="suitpay_url" required>

            <label for="callback_url">Callback URL:</label>
            <input type="text" name="callback_url" id="callback_url" required>

            <button type="submit">Salvar Configurações</button>
        </form>
    </div>
</body>
</html>
