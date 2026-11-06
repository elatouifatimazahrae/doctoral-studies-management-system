<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_painting'])) {
    $original_price = $_POST['original_price'];
    $promo_price = $_POST['promo_price'] ?: null;
    
    $target_dir = "../uploads/";
    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/" . $image_name;
        $stmt = $pdo->prepare("INSERT INTO paintings (image_path, original_price, promo_price) VALUES (?, ?, ?)");
        $stmt->execute([$image_path, $original_price, $promo_price]);
        $painting_id = $pdo->lastInsertId();

        foreach (['ar', 'en', 'fr'] as $l) {
            $title = $_POST["title_$l"];
            $desc = $_POST["desc_$l"];
            $stmt = $pdo->prepare("INSERT INTO paintings_translations (painting_id, language, title, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$painting_id, $l, $title, $desc]);
        }
        header("Location: index.php?success=1");
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM paintings WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?deleted=1");
    exit;
}

$paintings = get_paintings($pdo, 'ar');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - إدارة اللوحات</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f0f2f5; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background: #2c3e50; color: white; }
        .form-section { background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 10px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        .btn-add { background: #27ae60; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .btn-delete { color: red; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>إدارة اللوحات الفنية</h1>
    <p><a href="../index.php">العودة للموقع</a></p>
    
    <div class="form-section">
        <h2>إضافة لوحة جديدة</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>صورة اللوحة:</label>
                <input type="file" name="image" required>
            </div>
            <div class="form-group">
                <label>السعر الأصلي:</label>
                <input type="number" step="0.01" name="original_price" required>
            </div>
            <div class="form-group">
                <label>سعر العرض (اختياري):</label>
                <input type="number" step="0.01" name="promo_price">
            </div>
            
            <hr>
            <?php foreach (['ar' => 'العربية', 'en' => 'الإنجليزية', 'fr' => 'الفرنسية'] as $code => $name): ?>
                <h4>البيانات بـ <?php echo $name; ?></h4>
                <div class="form-group">
                    <input type="text" name="title_<?php echo $code; ?>" placeholder="العنوان" required>
                    <textarea name="desc_<?php echo $code; ?>" placeholder="الوصف" required></textarea>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" name="add_painting" class="btn-add">حفظ اللوحة</button>
        </form>
    </div>

    <h2>اللوحات الحالية</h2>
    <table>
        <thead>
            <tr>
                <th>الصورة</th>
                <th>العنوان (عربي)</th>
                <th>السعر</th>
                <th>العمليات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paintings as $p): ?>
                <tr>
                    <td><img src="../<?php echo $p['image_path']; ?>" width="50"></td>
                    <td><?php echo $p['title']; ?></td>
                    <td><?php echo $p['original_price']; ?> DH</td>
                    <td>
                        <a href="?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('هل أنت متأكد؟')">حذف</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
