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

// 回答者の取得
try {
    $stmt = $pdo->query("SELECT id, name FROM respondents ORDER BY name");
    $respondents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "回答者の取得中にエラーが発生しました: " . $e->getMessage();
}

// 回答を保存する処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_answers'])) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO answers (question_id, respondent_id, answer_text) 
                               VALUES (:question_id, :respondent_id, :answer_text) 
                               ON DUPLICATE KEY UPDATE answer_text = VALUES(answer_text)");
        
        foreach ($_POST['answer'] as $question_id => $respondent_answers) {
            foreach ($respondent_answers as $respondent_id => $answer_text) {
                if (!empty(trim($answer_text))) {
                    $stmt->execute([
                        'question_id' => $question_id,
                        'respondent_id' => $respondent_id,
                        'answer_text' => $answer_text
                    ]);
                }
            }
        }
        
        $pdo->commit();
        $message = "回答が正常に保存されました。";
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "回答の保存中にエラーが発生しました: " . $e->getMessage();
    }
}

// 質問と回答を取得
try {
    $stmt = $pdo->query("
        SELECT p.purpose_text, q.id AS question_id, q.question_text, 
               a.answer_text, a.respondent_id, r.name AS respondent_name
        FROM purposes p
        JOIN questions q ON p.id = q.purpose_id
        LEFT JOIN answers a ON q.id = a.question_id
        LEFT JOIN respondents r ON a.respondent_id = r.id
        ORDER BY p.id, q.id, r.name
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];
    foreach ($results as $row) {
        $question_id = $row['question_id'];
        if (!isset($questions[$question_id])) {
            $questions[$question_id] = [
                'purpose_text' => $row['purpose_text'],
                'question_text' => $row['question_text'],
                'answers' => []
            ];
        }
        if ($row['respondent_id']) {
            $questions[$question_id]['answers'][$row['respondent_id']] = [
                'respondent_name' => $row['respondent_name'],
                'answer_text' => $row['answer_text']
            ];
        }
    }
} catch(PDOException $e) {
    $error = "質問と回答の取得中にエラーが発生しました: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>インタビューツール - 回答の入力</title>
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

        h1 {
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
            margin: 30px 0px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .message,
        .error {
            padding: 10px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
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

        input[type="text"] {
            width: 100%;
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
  
    <h1>回答の入力</h1>
        <a href="design.php" class="btn">質問設計</a>
        <a href="home.php" class="btn">ホーム画面へ戻る</a>
  

        <?php
        if ($message) echo "<p class='message'>$message</p>";
        if ($error) echo "<p class='error'>$error</p>";
        ?>

        <form method="post">
            <table>
                <thead>
                    <tr>
                        <th>把握したいこと</th>
                        <th>質問</th>
                        <?php foreach ($respondents as $respondent): ?>
                            <th><?php echo htmlspecialchars($respondent['name']); ?>の回答</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question_id => $question): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($question['purpose_text']); ?></td>
                            <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                            <?php foreach ($respondents as $respondent): ?>
                                <td>
                                    <input type="text" 
                                           name="answer[<?php echo $question_id; ?>][<?php echo $respondent['id']; ?>]" 
                                           value="<?php echo htmlspecialchars($question['answers'][$respondent['id']]['answer_text'] ?? ''); ?>">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="submit_answers" class="btn">回答を送信</button>
        </form>
    </div>
</body>
</html>