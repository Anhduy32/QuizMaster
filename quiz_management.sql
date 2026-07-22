-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 08:05 AM
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
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL COMMENT 'ID của đề thi chứa câu hỏi này',
  `content` text NOT NULL COMMENT 'Nội dung câu hỏi',
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `opt_a` varchar(255) NOT NULL COMMENT 'Đáp án A',
  `opt_b` varchar(255) NOT NULL COMMENT 'Đáp án B',
  `opt_c` varchar(255) NOT NULL COMMENT 'Đáp án C',
  `opt_d` varchar(255) NOT NULL COMMENT 'Đáp án D',
  `correct_opt` char(1) NOT NULL COMMENT 'Đáp án đúng: A, B, C hoặc D',
  `status` varchar(20) NOT NULL DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `quiz_type` enum('multiple_choice','file_based') DEFAULT 'multiple_choice',
  `creator_username` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `num_questions` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'draft' COMMENT 'Trạng thái đề thi: draft, completed',
  `time_limit` int(11) DEFAULT 60 COMMENT 'Tính bằng phút',
  `description` text DEFAULT NULL COMMENT 'Mô tả chi tiết đề thi',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `target_audience` varchar(20) DEFAULT 'hoc_sinh' COMMENT 'hoc_sinh hoặc sinh_vien',
  `major` varchar(100) DEFAULT NULL COMMENT 'Ngành học nếu là sinh viên',
  `has_answers` tinyint(1) DEFAULT 1 COMMENT '1 là có đáp án, 0 là không có',
  `file_path` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn lưu file PDF đề thi gốc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `quiz_type`, `creator_username`, `title`, `subject`, `num_questions`, `status`, `time_limit`, `description`, `views`, `created_at`, `target_audience`, `major`, `has_answers`, `file_path`) VALUES
(46, 'file_based', 'anhduy', 'Toán', 'Tiếng Anh', 0, 'completed', 60, NULL, 35, '2026-07-22 05:30:40', 'Sinh viên năm 2', 'Công nghệ thông tin', 0, 'uploads/pdfs/quiz_6a6055809e630_1784698240.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_files`
--

CREATE TABLE `quiz_files` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL COMMENT 'ID của đề thi',
  `file_type` varchar(50) DEFAULT 'question_paper' COMMENT 'Loại file: question_paper, solution, reference',
  `file_path` varchar(255) NOT NULL COMMENT 'Đường dẫn vật lý tới file',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_history`
--

CREATE TABLE `quiz_history` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` float NOT NULL,
  `total_score` float DEFAULT 10,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `gender` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `oauth_provider` varchar(50) DEFAULT NULL COMMENT 'google hoặc facebook',
  `oauth_uid` varchar(255) DEFAULT NULL COMMENT 'ID định danh từ mạng xã hội',
  `picture` varchar(255) DEFAULT NULL COMMENT 'Link ảnh đại diện',
  `favorite_subjects` varchar(255) DEFAULT NULL COMMENT 'Các môn học cách nhau bằng dấu phẩy',
  `last_login_date` date DEFAULT NULL COMMENT 'Ngày đăng nhập cuối cùng',
  `login_streak` int(11) DEFAULT 0 COMMENT 'Chuỗi đăng nhập liên tiếp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `birthdate`, `password`, `role`, `gender`, `address`, `department`, `note`, `email`, `oauth_provider`, `oauth_uid`, `picture`, `favorite_subjects`, `last_login_date`, `login_streak`) VALUES
(44, 'aduy9214_g5979', 'Anh Duy', NULL, '$2y$10$73DlHJDnODYpOlze5MTIyuJ8ipWnvA7t2AYz0mgdJQ92F2pzxm/pi', 'admin', NULL, NULL, NULL, NULL, 'aduy9214@gmail.com', 'google', '112380018410539496034', 'https://lh3.googleusercontent.com/a/ACg8ocIViCvcVxLJfirlQ-1g6kRlp9mCgfzUzbQRQmwi-dieVe5giFU=s96-c', NULL, '2026-06-28', 1),
(45, 'anhduy', NULL, NULL, '$2y$10$Gpyv2.JjRwbyJFGRXRUPTOYpmt68cH08n4NhLG3fqB/tSkJ5SCrMC', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-22', 1),
(46, 'dsad', NULL, NULL, '$2y$10$7FSlC6dduI52Vn8ymERCeurTRdT51e/.Sax.a4ZtQ3axeM1NX4xSu', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_question_quiz` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quiz_user` (`creator_username`);

--
-- Indexes for table `quiz_files`
--
ALTER TABLE `quiz_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_history`
--
ALTER TABLE `quiz_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_user` (`username`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `quiz_files`
--
ALTER TABLE `quiz_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_history`
--
ALTER TABLE `quiz_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_question_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_user` FOREIGN KEY (`creator_username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_files`
--
ALTER TABLE `quiz_files`
  ADD CONSTRAINT `quiz_files_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_history`
--
ALTER TABLE `quiz_history`
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
