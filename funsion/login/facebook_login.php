<?php
session_start();
// CHÚ Ý 1: Lùi 2 cấp ra ngoài để tìm đúng thư mục vendor của Composer ở gốc
require_once '../../vendor/autoload.php'; 
include '../../config/database.php';

// === ĐIỀN THÔNG TIN API TỪ META FOR DEVELOPERS ===
$appId = 'ĐIỀN_APP_ID_CỦA_BẠN_VÀO_ĐÂY';
$appSecret = 'ĐIỀN_APP_SECRET_CỦA_BẠN_VÀO_ĐÂY';

// CHÚ Ý 2: Sửa lại link Redirect URI cho đúng vị trí mới (bổ sung /login/ vào đường dẫn)
$redirectUri = 'http://localhost/WebTaoBoDeTuDong/funsion/login/facebook_login.php';

$fb = new \Facebook\Facebook([
  'app_id' => $appId,
  'app_secret' => $appSecret,
  'default_graph_version' => 'v15.0', // Phiên bản Graph API
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
    $email = $fb_user->getEmail() ?? ($oauth_uid . '@facebook.com'); // Đề phòng user ẩn email
    $picture = $fb_user->getPicture()->getUrl();

    // Kiểm tra tài khoản đã tồn tại trong DB chưa
    $check_query = "SELECT * FROM users WHERE oauth_provider='facebook' AND oauth_uid=?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $oauth_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Đã tồn tại tài khoản -> Đăng nhập luôn
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
    } else {
        // Chưa tồn tại -> Tạo tài khoản tự động
        $username = 'fb_' . $oauth_uid;
        
        $insert_query = "INSERT INTO users (username, full_name, email, oauth_provider, oauth_uid, picture, role) VALUES (?, ?, ?, 'facebook', ?, ?, 'user')";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("sssss", $username, $full_name, $email, $oauth_uid, $picture);
        $stmt_insert->execute();
        
        $_SESSION['username'] = $username;
    }

    // CHÚ Ý 3: Đăng nhập thành công -> Lùi 2 cấp để về thẳng trang chủ index.php ở gốc
    header('Location: ../../index.php');
    exit();
} 
// 2. NẾU CHƯA CÓ TOKEN -> ĐẨY NGƯỜI DÙNG SANG TRANG XÁC THỰC CỦA FACEBOOK
else {
    $permissions = ['email']; // Quyền lấy email
    $loginUrl = $helper->getLoginUrl($redirectUri, $permissions);
    header('Location: ' . $loginUrl);
    exit();
}
?>