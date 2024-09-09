<?php
// データベース接続
try {
    $pdo = new PDO('mysql:dbname=yellow31_interview_tools;charset=utf8;host=mysql57.yellow31.sakura.ne.jp', '****', '****');
    // $pdo = new PDO('mysql:dbname=interview_tools;charset=utf8;host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('DBError: ' . $e->getMessage());
}

$message = $error = '';

// 新しい回答者を追加
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_respondent'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO respondents (name) VALUES (:name)");
        $stmt->execute(['name' => $_POST['respondent_name']]);
        $message = "回答者が正常に追加されました。";
    } catch (PDOException $e) {
        $error = "回答者の追加中にエラーが発生しました: " . $e->getMessage();
    }
}

// 既存の回答者を取得
try {
    $stmt = $pdo->query("SELECT id, name FROM respondents ORDER BY name");
    $respondents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "回答者の取得中にエラーが発生しました: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>回答者管理</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        h1, h2 {
            color: #333;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .message {
            padding: 10px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 500;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #fff;
            padding: 12px 16px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 12px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            font-size: 16px;
            font-weight: 500;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h1>回答者管理</h1>
        
        <a href="home.php" class="btn">ホーム画面へ戻る</a>

        <?php
        if ($message) echo "<p class='message'>$message</p>";
        if ($error) echo "<p class='error'>$error</p>";
        ?>

        <h2>新しい回答者の追加</h2>
        <form method="POST">
            <label for="respondent_name">回答者名：</label>
            <input type="text" id="respondent_name" name="respondent_name" required>
            <button type="submit" name="add_respondent">追加</button>
        </form>

        <h2>登録済みの回答者</h2>
        <ul>
            <?php foreach ($respondents as $respondent): ?>
                <li><?php echo htmlspecialchars($respondent['name']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>