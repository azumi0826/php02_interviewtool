<?php
// データベース接続
try {
    // $pdo = new PDO('mysql:dbname=interview_tools;charset=utf8;host=localhost', 'root', '');
    $pdo = new PDO('mysql:dbname=yellow31_interview_tools;charset=utf8;host=mysql57.yellow31.sakura.ne.jp', '****', '****');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('DBError: ' . $e->getMessage());
}

// データベース接続処理は省略

// データベースから質問と回答、回答者を取得
try {
    $stmt = $pdo->query("
        SELECT p.purpose_text, q.question_text, a.answer_text, r.name
        FROM purposes p
        JOIN questions q ON p.id = q.purpose_id
        LEFT JOIN answers a ON q.id = a.question_id
        LEFT JOIN respondents r ON a.respondent_id = r.id
        ORDER BY p.id, q.id, r.name
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // データが空の場合、警告を表示
    if (empty($results)) {
        $results = [];
        echo "データが見つかりません。";
    }
} catch (PDOException $e) {
    echo "クエリエラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>結果分析</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
        }

        h1, h2 {
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>結果分析</h1>
        <a href="home.php" class="btn">ホーム画面へ戻る</a>

        <?php if (!empty($results)): ?>
            <?php 
            // 利用シーンごとにデータをグループ化
            $groupedResults = [];
            foreach ($results as $row) {
                $groupedResults[$row['purpose_text']][] = $row;
            }
            ?>

            <?php foreach ($groupedResults as $purpose => $rows): ?>
                <h2><?php echo htmlspecialchars($purpose); ?></h2>
                <table id="purposeTable-<?php echo preg_replace('/\s+/', '-', $purpose); ?>" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>質問</th>
                            <th>回答</th>
                            <th>回答者</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($row['answer_text']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php else: ?>
            <p>データがありません。</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php foreach ($groupedResults as $purpose => $rows): ?>
                $('#purposeTable-<?php echo preg_replace('/\s+/', '-', $purpose); ?>').DataTable({
                    paging: true,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    responsive: true
                });
            <?php endforeach; ?>
        });
    </script>
</body>
</html>