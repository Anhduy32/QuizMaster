<?php
session_start();
// Lùi 2 cấp ra ngoài để tìm đúng thư mục vendor của Composer ở gốc
require_once '../../vendor/autoload.php'; 
include '../../config/database.php';

// === ĐIỀN THÔNG TIN API TỪ META FOR DEVELOPERS ===
$appId = 'ĐIỀN_APP_ID_CỦA_BẠN_VÀO_ĐÂY';
$appSecret = 'ĐIỀN_APP_SECRET_CỦA_BẠN_VÀO_ĐÂY';

// [QUAN TRỌNG] Đã cập nhật lại đường dẫn sang thư mục modules/auth/
$redirectUri = 'http://localhost/WebTaoBoDeTuDong/modules/auth/facebook_login.php';

$fb = new \Facebook\Facebook([
  'app_id' => $appId,
  'app_secret' => $appSecret,
  'default_graph_version' => 'v15.0',
]);

$helper = $fb->getRedirectLoginHelper();

// 1. NẾU FACEBOOK TRẢ VỀ ACCESS TOKEN (Đăng nhập thành công)
try {
    $accessToken = $helper->getAccessToken($redirectUri);
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Lỗi Graph API: ' . $e->getMessage();
    exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Lỗi Facebook SDK: ' . $e->getMessage();
    exit;
}

if (isset($accessToken)) {
    // Lấy thông tin cơ bản của người dùng từ Facebook
    $response = $fb->get('/me?fields=id,name,email,picture', $accessToken);
    $fb_user = $response->getGraphUser();

    $oauth_uid = $fb_user->getId();
    $full_name = $fb_user->getName();
    $email = $fb_user->getEmail() ?? ($oauth_uid . '@facebook.com'); 
    $picture = $fb_user->getPicture()->getUrl();

    // Kiểm tra tài khoản đã tồn tại trong DB chưa
    $check_query = "SELECT * FROM users WHERE oauth_provider='facebook' AND oauth_uid=?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $oauth_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
    } else {
        $username = 'fb_' . $oauth_uid;
        
        $insert_query = "INSERT INTO users (username, full_name, email, oauth_provider, oauth_uid, picture, role) VALUES (?, ?, ?, 'facebook', ?, ?, 'user')";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("sssss", $username, $full_name, $email, $oauth_uid, $picture);
        $stmt_insert->execute();
        
        $_SESSION['username'] = $username;
    }

    // Đăng nhập thành công -> Về thẳng Bảng điều khiển (home.php)
    header('Location: ../../home.php');
    exit();
} 
// 2. NẾU CHƯA CÓ TOKEN -> ĐẨY NGƯỜI DÙNG SANG TRANG XÁC THỰC CỦA FACEBOOK
else {
    $permissions = ['email']; 
    $loginUrl = $helper->getLoginUrl($redirectUri, $permissions);
    header('Location: ' . $loginUrl);
    exit();
}
?>