-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2026 at 01:43 PM
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
-- Database: `feedback_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `assessment_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `assessment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`assessment_id`, `title`, `assessment_date`) VALUES
(1, 'Quiz 1 - HTML and CSS ', '2025-12-28'),
(2, 'Quiz 2 - For and While Loops', '2025-12-31'),
(3, 'Quiz 3 - Javascript Basics', '2026-01-07');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) UNSIGNED NOT NULL,
  `submission_id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED NOT NULL,
  `what_went_well` text NOT NULL,
  `needs_improvement` text NOT NULL,
  `next_steps` text NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `submission_id`, `teacher_id`, `what_went_well`, `needs_improvement`, `next_steps`, `created_at`, `is_read`) VALUES
(1, 1, 4, 'You managed to secure a 70% (first class) well done. Keep testing your knowledge with the in class quizzes that will be provided next week.', 'Being more confident in commenting HTML code, this is a crucial step to understanding code and design decisions such as why did you use a grid layout instead of a table structure?', 'Create a weekly plan to dive in JS (JavaScript) as this is essential for web development and understanding JSON formats as well as how to create a dynamic website, one that has interaction built into the core design and how to effectively use CSS with it e.g. hovering over a button.', '2026-01-27 16:19:33', 1),
(2, 4, 1, 'You did manage to answer some questions correct, you do have a small foundation but need to be more confident in how you use pixels/percentages in CSS.', 'Weakest Quiz score achieved, You can heavily improve this please use w3schools and understand the core concepts such as: how to apply borders, shadows when hovering and creating table with data inside.', 'Like previously mentioned you could use w3schools to improve and do some self studies, furthermore increase your confidence by using an online html editor, try doing these three: create a paragraph and make it bold, then try increasing the font size, and change it\'s actual font style. After doing this you could make it be underlined when hovered over.', '2026-02-11 15:38:52', 1),
(3, 2, 1, 'You did manage to grasp a lot of the core concepts especially well done for passing as this can be a tricky quiz and the score suggests that you nearly have the foundations.', 'Just below the average, try testing out for and while loops and becoming more familiar with them. You can use W3 schools or the actual documentation for your chosen programming language e.g. (Java, C++).', 'The next steps would be to practice more on why and when to use each loop for example. For loops are used when you know the amount of iterations you want to do e.g. 5, while loops are used for conditions where you do not know how many attempts will be needed an example could be a login system.\r\n\r\nGood try!', '2026-02-12 16:10:47', 1);

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `submission_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `assessment_id` int(11) UNSIGNED NOT NULL,
  `score` int(11) NOT NULL,
  `submitted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`submission_id`, `user_id`, `assessment_id`, `score`, `submitted_at`) VALUES
(1, 3, 1, 70, '2025-12-11 22:55:13'),
(2, 3, 2, 55, '2025-12-25 12:45:10'),
(3, 3, 3, 85, '2026-01-02 10:30:32'),
(4, 2, 1, 33, '2025-12-24 15:35:20'),
(5, 2, 2, 60, '2025-12-31 11:05:22'),
(6, 2, 3, 95, '2026-01-05 10:56:00');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password_hash`, `role`) VALUES
(1, 'Davis', '$2y$10$AX1yVZvjUn.la9FhXwuoIeo4nHQi.NEVBt1Al7AkeRLOCqHFehkDW', 'teacher'),
(2, 'Kevin', '$2y$10$RlZLuiRL5hBf/GncldQtUO1ilkq.meBgWYsWZiChVzly3aGYofzP6', 'student'),
(3, 'Jamie', '$2y$10$/nggn/8cOQWP85DZuHIiKuJ7yWUejA6mzJ2X1fEMWBtDgxdyvwlMC', 'student'),
(4, 'Jones', '$2y$10$qZLvy2dyQ1u5y6dsuIZO3eFXZt87NwwT60nPj87lDTdHTOw6FY87u', 'teacher');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`assessment_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD UNIQUE KEY `submission_id` (`submission_id`),
  ADD KEY `FK_feedback_userteacher` (`teacher_id`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `assessment_id` (`assessment_id`) USING BTREE;

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `Username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `submission_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `FK_feedback_submission` FOREIGN KEY (`submission_id`) REFERENCES `submission` (`submission_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_feedback_userteacher` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `FK_submission_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_submission_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
