
-- Create database
CREATE DATABASE IF NOT EXISTS `ict_task_tracking`;
USE `ict_task_tracking`;

-- Table structure for table `officers`
CREATE TABLE `officers` (
  `officer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `employment_type` enum('Attaché','Intern','Employee') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `tasks`
CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `task_detail` text NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `date_reported` datetime DEFAULT current_timestamp(),
  `issue_solved` enum('Yes','No') DEFAULT 'No',
  `date_solved` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes for table `officers`
ALTER TABLE `officers`
  ADD PRIMARY KEY (`officer_id`),
  ADD UNIQUE KEY `email` (`email`);

-- Indexes for table `tasks`
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `assigned_to` (`assigned_to`);

-- AUTO_INCREMENT settings
ALTER TABLE `officers`
  MODIFY `officer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

-- Foreign key constraints
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `officers` (`officer_id`) ON DELETE SET NULL;
