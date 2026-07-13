<?php
/**
 * Header chung cho toàn bộ trang
 * Sử dụng: require_once 'includes/layouts/header.php';
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'QuizMaster - Nền Tảng Học Tập & Thi Trắc Nghiệm'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="/WebTaoBoDeTuDong/assets/css/<?php echo $page_css; ?>?v=<?php echo time(); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="/WebTaoBoDeTuDong/assets/css/index.css?v=<?php echo time(); ?>">
    <?php endif; ?>
    
    <?php if (isset($page_extra_css)): ?>
        <style><?php echo $page_extra_css; ?></style>
    <?php endif; ?>
</head>
<body>