-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 12:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `create_questions`
--

CREATE TABLE `create_questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `answer_a` text NOT NULL,
  `answer_b` text NOT NULL,
  `answer_c` text NOT NULL,
  `answer_d` text NOT NULL,
  `correct_answer` varchar(1) NOT NULL,
  `difficulty` varchar(50) DEFAULT NULL,
  `teacher_name` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject` varchar(255) NOT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `create_questions`
--

INSERT INTO `create_questions` (`id`, `question_text`, `answer_a`, `answer_b`, `answer_c`, `answer_d`, `correct_answer`, `difficulty`, `teacher_name`, `category`, `created_at`, `subject`, `subject_id`) VALUES
(112, 'Firewall có chức năng chính là gì?', 'Lưu trữ dữ liệu', 'Bảo vệ khỏi virus', 'Ngăn chặn truy cập trái phép', 'Tăng tốc mạng', 'C', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 05:57:55', '', 10),
(113, 'Mã hóa (Encryption) là quá trình:', 'Giải mã dữ liệu', 'Xóa dữ liệu', 'Chuyển dữ liệu thành dạng không đọc được', 'Kiểm tra lỗi dữ liệu', 'C', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 05:58:25', '', 10),
(114, 'Đâu là ví dụ của phần mềm độc hại (malware)?', 'Microsoft Word', 'Linux', 'Ransomware', 'Google Chrome', 'C', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 05:58:54', '', 10),
(115, 'Giao thức HTTPS bảo mật hơn HTTP nhờ vào:', 'Giao thức TCP', 'Dùng cổng khác', 'Mã hóa dữ liệu truyền đi', 'Tốc độ cao hơn', 'C', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:08:31', '', 10),
(116, 'Thiết bị nào sau đây thường dùng để phát hiện tấn công mạng?', 'IDS', 'DNS', 'DHCP', 'FTP', 'A', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:08:53', '', 10),
(117, 'Chính sách bảo mật (security policy) là:', 'Luật của nhà nước', 'Một phần mềm bảo vệ', 'Tập hợp các quy định bảo vệ hệ thống', 'Mật khẩu bảo vệ máy tính', 'C', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:09:16', '', 10),
(118, 'Virus máy tính thường lây lan qua:', 'Tệp tin đính kèm email', 'Bàn phím', 'Màn hình', 'Bộ nhớ RAM', 'A', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:09:36', '', 10),
(119, 'Phần mềm diệt virus có chức năng:', 'Xóa tất cả dữ liệu', 'Làm đầy ổ cứng', 'Phát hiện và loại bỏ mã độc', 'Tăng hiệu suất mạng', 'C', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:09:57', '', 10),
(120, 'Một tài khoản có mật khẩu yếu dễ bị:', 'Giảm tốc độ mạng', 'Tấn công Brute Force', 'Sửa chữa phần cứng', 'Tối ưu hóa hệ điều hành', 'B', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:10:21', '', 10),
(121, 'Đâu là yếu tố cốt lõi của tam giác an ninh thông tin (CIA)?', 'Cookie, JavaScript, Proxy', 'Confidentiality, Integrity, Availability', 'Coding, Input, Analysis', 'Checksum, Identity, Algorithm', 'B', 'Dễ', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:10:46', '', 10),
(122, 'Phân biệt giữa IDS và IPS là:', 'IDS phòng ngừa, IPS chỉ phát hiện', 'IDS hoạt động thụ động, IPS hoạt động chủ động', 'IPS không thể ngăn chặn tấn công', 'IDS chỉ dùng trong mạng LAN', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:11:09', '', 10),
(123, 'Quá trình xác thực người dùng bao gồm:', 'Chỉ cần tên người dùng', 'Kiểm tra ID của máy tính', 'Kiểm tra tên đăng nhập và mật khẩu', 'Kiểm tra tốc độ gõ phím', 'C', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:13:02', '', 10),
(124, 'XSS (Cross-site scripting) tấn công chủ yếu vào:', 'Trình duyệt người dùng', 'Máy chủ cơ sở dữ liệu', 'Thiết bị mạng', 'Phần mềm diệt virus', 'A', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:14:35', '', 10),
(125, 'Quản trị log trong bảo mật giúp:', 'Lưu trữ video', 'Ghi nhận hoạt động hệ thống để kiểm tra', 'Làm sạch bộ nhớ RAM', 'Bảo vệ phần cứng', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:14:59', '', 10),
(126, 'Phần mềm keylogger nguy hiểm vì:', 'Ghi lại thao tác chuột', 'Xóa dữ liệu hệ thống', 'Ghi lại phím bấm, đánh cắp thông tin đăng nhập', 'Làm tràn bộ nhớ RAM', 'C', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:15:24', '', 10),
(127, 'Công cụ nmap thường dùng để:', 'Lập trình ứng dụng', 'Quét cổng và tìm lỗ hổng', 'Chạy tường lửa', 'Mã hóa dữ liệu', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:15:45', '', 10),
(128, 'Điều gì giúp tăng cường xác thực 2 lớp (2FA)?', 'Mật khẩu mạnh', 'Thêm một yếu tố xác thực khác như OTP', 'Giao thức UDP', 'Ẩn giao diện đăng nhập', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:16:09', '', 10),
(129, 'Social engineering là kỹ thuật tấn công:', 'Sử dụng công cụ mã hóa', 'Lợi dụng yếu tố con người để lấy thông tin', 'Cắt cáp mạng', 'Phá hoại phần cứng', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:16:33', '', 10),
(130, 'Để đảm bảo tính toàn vẹn dữ liệu, nên sử dụng:', 'Cơ sở dữ liệu tách biệt', 'Thuật toán hash (SHA, MD5)', 'Tăng tốc độ mạng', 'Đổi tên file thường xuyên', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:16:55', '', 10),
(131, 'NAT trong bảo mật giúp:', 'Mã hóa dữ liệu', 'Ẩn địa chỉ IP nội bộ', 'Giảm băng thông', 'Tăng dung lượng ổ cứng', 'B', 'Khá', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:17:17', '', 10),
(132, 'Khi thiết lập ACL trên router, luật nào nên được đặt sau cùng để đảm bảo bảo mật?', 'deny all', 'allow any', 'log accept', 'permit tcp', 'A', 'Khó', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:17:39', '', 10),
(133, 'Một Zero-day exploit là gì?', 'Tấn công mạng không dây', 'Tấn công xảy ra vào ngày đầu tiên của năm', 'Lỗ hổng chưa được công bố và chưa có bản vá', 'Mã độc tự nhân bản', 'C', 'Khó', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:18:06', '', 10),
(134, 'Giả lập môi trường tấn công để kiểm tra lỗ hổng hệ thống là kỹ thuật:', 'Social engineering', 'Penetration Testing', 'Reverse engineering', 'Packet sniffing', 'B', 'Khó', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:18:30', '', 10),
(135, 'Điều gì khó khăn nhất trong việc phát hiện APT (Advanced Persistent Threat)?', 'Giao thức mạng', 'Tốc độ tấn công chậm và liên tục, ẩn mình tinh vi', 'Lỗ hổng ứng dụng', 'Giao diện đồ họa', 'B', 'Khó', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:18:53', '', 10),
(136, 'Công cụ nào sau đây dùng để kiểm tra bảo mật web application?', 'Metasploit', 'Nmap', 'Wireshark', 'Burp Suite', 'D', 'Khó', 'Nguyễn thị thủy', 'Công nghệ thông tin', '2025-05-01 06:19:12', '', 10),
(137, 'á', 'd', 'b', 'c', 'd', 'A', 'Dễ', 'Nguyễn Anh Duy', 'Công nghệ thông tin', '2025-05-07 09:37:18', '', 14),
(138, 'á', 'd', 'b', 'c', 'd', 'B', 'Dễ', 'Nguyễn Anh Duy', 'ko', '2025-05-07 09:37:22', 'ko', 14),
(139, 'd', 'c', 's', 'ư', 'd', 'B', 'Dễ', 'Nguyễn Anh Duy', 'Công nghệ thông tin', '2025-05-07 09:37:53', '', 14),
(140, 'csa', 'sca', 'ăc', 'csw', 'vdd', 'D', 'Dễ', 'Nguyễn Anh Duy', 'Công nghệ thông tin', '2025-05-07 09:45:45', '', 14),
(141, 'c', 'c', 'c', 'cc', 'c', 'A', 'Dễ', 'Nguyễn Anh Duy', 'Công nghệ thông tin', '2025-05-07 10:32:15', '', 14),
(142, 'c', 'c', 'c', 'c', 'c', 'A', 'Dễ', 'Nguyễn Anh Duy', 'Công nghệ thông tin', '2025-05-07 10:34:07', '', 14);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `teacher_id`, `department`) VALUES
(14, 'Quản trị an ninh mạng', 'd', 32, 'Công nghệ thông tin');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('hieutruong','hieupho','giaovien') NOT NULL DEFAULT 'giaovien',
  `gender` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `birthdate`, `password`, `role`, `gender`, `address`, `department`, `note`) VALUES
(1, 'anhduy', 'Nguyễn Anh Duy', '2003-01-15', 'duy123', 'hieutruong', 'Nam', '1', NULL, NULL),
(32, 'Nguyễn Anh Duy', 'Nguyễn Anh Duy', '2003-01-15', 'duy123', 'giaovien', 'Nam', 's', NULL, NULL),
(35, 'Nguyễn thị thủy', 'Nguyễn Thị Thi', '1995-11-11', 'duy123', 'giaovien', 'Nam', '1', NULL, NULL),
(36, 'nguyenduy', NULL, NULL, 'duy123', 'giaovien', NULL, NULL, NULL, NULL),
(37, 'Nguyễn Anh Duyy', 'Nguyễn Hiệu Trưởng', '2000-12-11', 'duy123', 'hieutruong', 'Nam', 'khong có', NULL, NULL),
(38, 'kiet', 'Nguyễn Kiệt', '2000-10-15', 'duy123', 'giaovien', 'Nam', 'khong có', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `create_questions`
--
ALTER TABLE `create_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teacher` (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `create_questions`
--
ALTER TABLE `create_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
