<?php
session_start();

// Kiểm tra và nạp thư viện Google API qua Autoload của Composer (Lùi 2 cấp ra gốc)
if (!file_exists('../../vendor/autoload.php')) {
    die("<h3 style='text-align:center;margin-top:50px;font-family:sans-serif;color:#e53e3e;'>Thiếu thư viện Google SDK. Hãy chạy 'composer install' tại thư mục gốc!</h3>");
}
require_once '../../vendor/autoload.php'; 

use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;

include '../../config/database.php';

// ==========================================================================
// THÔNG TIN CONFIG GOOGLE API CỦA BẠN
// ==========================================================================
$clientID     = '794115850452-2imqu62unnksi6mvj857sn8mvpj54as7.apps.googleusercontent.com';
$clientSecret = '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';
// [QUAN TRỌNG] Đã cập nhật lại đường dẫn sang thư mục modules/auth/
$redirectUri  = 'http://localhost/WebTaoBoDeTuDong/modules/auth/google_login.php';

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
        
        // Phòng ngừa lỗi không lấy được Access Token hoặc người dùng hủy quyền
        if (isset($token['error'])) {
            header('Location: login.php?loi=TokenInvalid');
            exit();
        }
        
        $client->setAccessToken($token['access_token']);

        // Lấy thông tin chi tiết tài khoản từ Google API
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $oauth_uid = $google_account_info->id;       
        $email     = $google_account_info->email;    
        $full_name = $google_account_info->name;     
        $picture   = $google_account_info->picture;  

        // Kiểm tra xem người dùng này đã từng đăng nhập bằng Google trước đây chưa
        $check_query = "SELECT username, full_name, picture FROM users WHERE oauth_provider='google' AND oauth_uid = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $oauth_uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // ĐÃ CÓ TÀI KHOẢN: Cập nhật lại thông tin mới nhất từ Google
            $user = $result->fetch_assoc();
            
            $update_query = "UPDATE users SET full_name = ?, picture = ? WHERE oauth_provider='google' AND oauth_uid = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("sss", $full_name, $picture, $oauth_uid);
            $stmt_update->execute();

            $_SESSION['username'] = $user['username'];
        } else {
            // CHƯA CÓ TÀI KHOẢN: Tự động tạo tài khoản mới
            $base_username = explode("@", $email)[0];
            $username = $base_username . '_g' . rand(1000, 9999);

            $dummy_password = password_hash(uniqid(), PASSWORD_BCRYPT);
            $oauth_provider = 'google'; 

            // Cấp quyền Admin nếu là Email của chủ hệ thống
            $role = ($email === 'aduy9214@gmail.com') ? 'admin' : 'user';

            $insert_query = "INSERT INTO users (username, password, full_name, email, oauth_provider, oauth_uid, picture, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_query);
            
            $stmt_insert->bind_param("ssssssss", $username, $dummy_password, $full_name, $email, $oauth_provider, $oauth_uid, $picture, $role);
            $stmt_insert->execute();
            
            $_SESSION['username'] = $username;
        }

        // Đăng nhập thành công -> Về thẳng Bảng điều khiển (home.php)
        header('Location: ../../home.php');
        exit();

    } catch (Exception $e) {
        die("<h3 style='text-align:center;margin-top:50px;font-family:sans-serif;color:#e53e3e;'>Đã xảy ra lỗi hệ thống khi đăng nhập Google: " . htmlspecialchars($e->getMessage()) . "</h3>");
    }
} 
// TRƯỜNG HỢP 2: NGƯỜI DÙNG MỚI BẤM NÚT -> ĐẨY SANG TRANG ĐĂNG NHẬP CỦA GOOGLE
else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
}
?>