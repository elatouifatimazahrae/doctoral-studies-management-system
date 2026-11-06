<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$painting_id = $_GET['id'] ?? null;
$subject = "";

if ($painting_id) {
    $stmt = $pdo->prepare("SELECT pt.title FROM paintings_translations pt WHERE pt.painting_id = ? AND pt.language = ?");
    $stmt->execute([$painting_id, $lang]);
    $painting = $stmt->fetch();
    if ($painting) {
        $subject = "Inquiry about: " . $painting['title'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo get_text('contact', $lang); ?></title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        header { background: #2c3e50; color: white; padding: 1rem; text-align: center; }
        .container { max-width: 600px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; }
        input, textarea { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background: #2c3e50; color: white; padding: .75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<header>
    <h1><?php echo get_text('contact', $lang); ?></h1>
    <nav><a href="index.php" style="color:white;"><?php echo get_text('home', $lang); ?></a></nav>
</header>
<div class="container">
    <h2><?php echo get_text('contact_msg', $lang); ?></h2>
    <form action="#" method="POST">
        <div class="form-group">
            <label><?php echo get_text('email', $lang); ?></label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label><?php echo get_text('title', $lang); ?></label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
        </div>
        <div class="form-group">
            <label><?php echo get_text('message', $lang); ?></label>
            <textarea name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn"><?php echo get_text('send', $lang); ?></button>
    </form>
</div>
</body>
</html>
