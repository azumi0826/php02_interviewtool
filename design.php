<?php
// データベース接続
try {
    // $pdo = new PDO('mysql:dbname=interview_tools;charset=utf8;host=localhost', 'root', '');
    $pdo = new PDO('mysql:dbname=yellow31_interview_tools;charset=utf8;host=mysql57.yellow31.sakura.ne.jp', '****', '****');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('DBError: ' . $e->getMessage());
}

$message = $error = '';

// 新しい目的と質問を追加
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_purpose'])) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO purposes (purpose_text) VALUES (:purpose_text)");
        $stmt->execute(['purpose_text' => $_POST['purpose_text']]);
        $purpose_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO questions (purpose_id, question_text) VALUES (:purpose_id, :question_text)");
        foreach ($_POST['question_text'] as $question) {
            if (!empty(trim($question))) {
                $stmt->execute([
                    'purpose_id' => $purpose_id,
                    'question_text' => $question
                ]);
            }
        }

        $pdo->commit();
        $message = "目的と質問が正常に追加されました。";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "データの追加中にエラーが発生しました: " . $e->getMessage();
    }
}

// 既存の目的と質問を取得
try {
    $stmt = $pdo->query("
        SELECT p.id AS purpose_id, p.purpose_text, q.id AS question_id, q.question_text
        FROM purposes p
        LEFT JOIN questions q ON p.id = q.purpose_id
        ORDER BY p.id, q.id
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "データの取得中にエラーが発生しました: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>インタビューツール - 質問設計</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        h1, h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .btn {
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

        .btn:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 20px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>質問設計</h1>
        
        <a href="home.php" class="btn">ホーム画面へ戻る</a>

        <?php
        if ($message) echo "<p class='message'>$message</p>";
        if ($error) echo "<p class='error'>$error</p>";
        ?>

        <h2>新しい目的と質問の追加</h2>
        <form id="purposeForm" method="POST">
            <div class="form-group">
                <label for="purpose_text">把握したいこと：</label>
                <input type="text" id="purpose_text" name="purpose_text" required>
            </div>
            <div id="questions">
                <div class="form-group">
                    <label for="question_text[0]">質問1：</label>
                    <input type="text" name="question_text[]" required>
                </div>
            </div>
            <button type="button" id="addQuestion" class="btn">質問を追加</button>
            <button type="submit" name="add_purpose" class="btn">保存</button>
        </form>

        <h2>既存の目的と質問</h2>
        <table>
            <tr>
                <th>把握したいこと</th>
                <th>質問</th>
            </tr>
            <?php
            $current_purpose = null;
            foreach ($results as $row) {
                if ($current_purpose !== $row['purpose_id']) {
                    echo "<tr>";
                    echo "<td rowspan='". count(array_filter($results, function($r) use ($row) { return $r['purpose_id'] == $row['purpose_id']; })) ."'>" . htmlspecialchars($row['purpose_text']) . "</td>";
                    $current_purpose = $row['purpose_id'];
                } else {
                    echo "<tr>";
                }
                echo "<td>" . htmlspecialchars($row['question_text']) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <script>
    $(document).ready(function() {
        let questionCount = 1;

        $("#addQuestion").click(function() {
            questionCount++;
            let newQuestion = `
                <div class="form-group">
                    <label for="question_text[${questionCount-1}]">質問${questionCount}：</label>
                    <input type="text" name="question_text[]" required>
                </div>
            `;
            $("#questions").append(newQuestion);
        });
    });
    </script>
</body>
</html>