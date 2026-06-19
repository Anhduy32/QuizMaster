<?php
session_start();
// CHÚ Ý 1: Lùi 2 cấp ra ngoài để tìm đúng thư mục vendor ở gốc
require_once '../../vendor/autoload.php'; 
include '../../config/database.php';

// ==========================================================================
// THAY THẾ THÔNG TIN CỦA BẠN VÀO ĐÂY
// ==========================================================================
$clientID     = '794115850452-2imqu62unnksi6mvj857sn8mvpj54as7.apps.googleusercontent.com';
$clientSecret = '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';

// CHÚ Ý 2: Sửa lại link Redirect URI cho đúng vị trí mới (thêm /login/ vào đường dẫn)
$redirectUri  = 'http://localhost/WebTaoBoDeTuDong/funsion/login/google_login.php';

// Khởi tạo Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// TRƯỜNG HỢP 1: GOOGLE ĐÃ XÁC THỰC VÀ TRẢ MÃ 'CODE' VỀ QUA URL
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Đề phòng lỗi không lấy được Access Token
        if (isset($token['error'])) {
            header('Location: login.php?loi=TokenInvalid');
            exit();
        }
        
        $client->setAccessToken($token['access_token']);

        // Lấy thông tin chi tiết tài khoản từ Google API
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $oauth_uid = $google_account_info->id;       // ID định danh duy nhất của tài khoản Google
        $email     = $google_account_info->email;    // Địa chỉ Gmail
        $full_name = $google_account_info->name;     // Họ và tên người dùng
        $picture   = $google_account_info->picture;  // Link ảnh đại diện

        // Kiểm tra xem người dùng này đã từng đăng nhập bằng Google trước đây chưa
        $check_query = "SELECT * FROM users WHERE oauth_provider='google' AND oauth_uid = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $oauth_uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // ĐÃ CÓ TÀI KHOẢN: Lấy thông tin username nội bộ và thiết lập Session đăng nhập
            $user = $result->fetch_assoc();
            $_SESSION['username'] = $user['username'];
        } else {
            // CHƯA CÓ TÀI KHOẢN: Tự động tạo tài khoản mới
            $base_username = explode("@", $email)[0];
            $username = $base_username . '_g' . rand(1000, 9999);

            // Đăng ký thông tin vào database, mặc định role là 'user' dùng chung cho tất cả mọi người
            $insert_query = "INSERT INTO users (username, full_name, email, oauth_provider, oauth_uid, picture, role) VALUES (?, ?, ?, 'google', ?, ?, 'user')";
            $stmt_insert = $conn->prepare($insert_query);
            $stmt_insert->bind_param("sssss", $username, $full_name, $email, $oauth_uid, $picture);
            $stmt_insert->execute();
            
            $_SESSION['username'] = $username;
        }

        // CHÚ Ý 3: Đăng nhập thành công -> Lùi 2 cấp về thẳng trang chủ index.php ở gốc
        header('Location: ../../index.php');
        exit();

    } catch (Exception $e) {
        // Ghi nhận lỗi nếu có trục trặc hệ thống
        echo "Đã xảy ra lỗi hệ thống: " . $e->getMessage();
        exit();
    }
} 
// TRƯỜNG HỢP 2: NGƯỜI DÙNG MỚI BẤM NÚT -> ĐẨY SANG TRANG ĐĂNG NHẬP CỦA GOOGLE
else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
}
?>