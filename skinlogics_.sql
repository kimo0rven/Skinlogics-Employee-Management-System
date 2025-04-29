-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 05:14 AM
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
-- Database: `skinlogics`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `date_created` date DEFAULT NULL,
  `clock_in_time` datetime DEFAULT NULL,
  `clock_out_time` datetime DEFAULT NULL,
  `status` enum('Present','Absent','Late','On Leave') DEFAULT NULL,
  `worked_hours` decimal(10,2) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `date_created`, `clock_in_time`, `clock_out_time`, `status`, `worked_hours`, `date_modified`) VALUES
(1, 2, '2025-03-29', '2025-04-07 08:50:37', '2025-04-07 18:01:03', 'Present', NULL, '2025-04-15 09:02:38'),
(2, 2, '2025-03-29', '2025-04-07 08:50:37', '2025-04-07 18:01:03', 'Present', NULL, '2025-04-15 09:02:38'),
(3, 1, '2025-03-29', '2025-04-07 09:15:22', '2025-04-07 21:56:39', 'Late', 6.18, '2025-04-15 09:02:38'),
(4, 3, '2025-03-29', NULL, NULL, 'Absent', NULL, '2025-04-15 09:02:38'),
(5, 5, '2025-03-29', '2025-04-07 08:45:05', '2025-04-07 17:55:42', 'Present', NULL, '2025-04-15 09:02:38'),
(6, 4, '2025-03-29', '2025-04-07 09:05:33', '2025-04-07 18:10:15', 'Late', NULL, '2025-04-15 09:02:38'),
(7, 6, '2025-03-29', NULL, NULL, 'Absent', NULL, '2025-04-15 09:02:38'),
(8, 1, '2025-03-28', '2025-04-07 08:55:10', '2025-04-07 21:56:39', 'Present', 6.18, '2025-04-15 09:02:38'),
(9, 3, '2025-03-28', '2025-04-07 09:25:45', '2025-04-07 18:05:39', 'Late', NULL, '2025-04-15 09:02:38'),
(30, 1, '2025-04-06', NULL, NULL, 'Absent', 0.00, '2025-04-15 09:02:38'),
(31, 1, '2025-04-07', '2025-04-07 23:02:03', '2025-04-07 23:02:04', 'Late', 0.00, '2025-04-15 09:02:38'),
(32, 1, '2025-04-08', '2025-04-08 01:27:30', '2025-04-08 01:27:33', 'Present', 0.00, '2025-04-15 09:02:38'),
(33, 2, '2025-04-07', NULL, NULL, 'Absent', 0.00, '2025-04-15 09:02:38'),
(34, 2, '2025-04-08', '2025-04-08 01:30:29', '2025-04-08 01:31:05', 'Present', 0.01, '2025-04-15 09:02:38'),
(35, 1, '2025-04-14', NULL, NULL, 'Absent', 0.00, '2025-04-15 10:27:29'),
(36, 1, '2025-04-15', '2025-04-15 18:27:29', '2025-04-15 18:27:31', 'Late', 0.00, '2025-04-15 10:27:31');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `team_leader_id` int(11) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `manager_id`, `team_leader_id`, `branch`, `status`, `date_modified`, `date_created`) VALUES
(1, 'Sales', 1, 2, 'Main Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(2, 'Marketing', 3, 1, 'Downtown Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(3, 'Finance', 2, 4, 'Uptown Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(4, 'HR', 4, 3, 'Main Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(5, 'IT', 5, 5, 'Tech Park Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(6, 'Operations', 1, 3, 'Industrial Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(7, 'Customer Service', 2, 5, 'Online Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(8, 'Research', 3, 2, 'University Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(9, 'Development', 4, 1, 'Innovation Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06'),
(10, 'Legal', 5, 4, 'Headquarters Branch', 'Active', '2025-04-15 09:02:22', '2025-04-15 09:11:06');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `user_account_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT 'Iligan City',
  `province` varchar(100) DEFAULT 'Lanao del Norte',
  `status` enum('Active','Inactive','Terminated','Resigned') NOT NULL DEFAULT 'Active',
  `email` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `hire_date` date NOT NULL DEFAULT current_timestamp(),
  `civil_status` enum('Single','Married','Widowed','Separated') DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `sss_number` varchar(20) DEFAULT NULL,
  `philhealth_number` varchar(20) DEFAULT NULL,
  `pagibig_number` varchar(20) DEFAULT NULL,
  `tin_number` varchar(20) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `team_leader_id` int(11) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(100) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `setup` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `user_account_id`, `first_name`, `last_name`, `middle_name`, `mobile`, `gender`, `street`, `barangay`, `city`, `province`, `status`, `email`, `dob`, `hire_date`, `civil_status`, `department_id`, `job_id`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `manager_id`, `team_leader_id`, `emergency_contact_name`, `emergency_contact_number`, `emergency_contact_relationship`, `date_created`, `date_modified`, `setup`) VALUES
(1, 1, 'Test', 'Admin', 'Tester', '09355891758', 'Male', 'Test', 'Test', 'Iligan City', 'Lanao del Norte', 'Active', 'kimo0rven@gmail.com', '1999-03-30', '2025-03-01', 'Single', 7, 7, '', '', 'asd', '', 1, 1, '', '', '', '2025-03-25 03:11:27', '2025-04-21 12:03:16', 1),
(2, 2, 'Peter', 'Griffin', '', '09355891759', 'Male', '123', '123', '123', '123', 'Active', 'kimorvenvalencia+resigned@gmail.com', '1999-07-18', '2025-03-01', NULL, 7, 5, '', '', '', '', NULL, NULL, 'Jollibee', '8700', 'Mother', '2025-03-24 19:11:27', '2025-03-30 15:14:24', 1),
(3, 2, 'Joe', 'Standard', 'Tester', '09355891759', 'Female', 'Test', 'Test', 'Iligan City', 'Lanao del Norte', 'Active', 'kimorvenvalencia+inactive@gmail.com', '1999-07-18', '2025-03-01', 'Single', 1, 8, '', '', '', '', NULL, NULL, '', '', '', '2025-03-24 19:11:27', '2025-04-09 17:05:03', 0),
(4, 2, 'Peter', 'Standard', 'Tester', '09355891759', 'Female', 'Test', 'Test', 'Iligan City', 'Lanao del Norte', 'Terminated', 'kimorvenvalencia+resigned@gmail.com', '1999-07-18', '2025-03-01', 'Single', 6, 8, '', '', '', NULL, NULL, NULL, '', '', '', '2025-03-24 19:11:27', '2025-03-29 17:16:52', 0),
(5, 3, 'Judas1', 'Standard', 'Tester', '09355891759', 'Female', 'Test', 'Test', 'Iligan City', 'Lanao del Norte', 'Inactive', 'kimorvenvalencia+terminated@gmail.com', '1999-07-18', '2025-03-01', 'Single', 6, 8, '', '', '', '', NULL, NULL, '', '', '', '2025-03-24 19:11:27', '2025-03-30 01:09:07', 0),
(6, 32, '', 'Novablast Five', 'Novablast', '9355891759', 'Other', 'Purok 17, Hilltop', 'Tominobo Proper', 'Iligan City', 'Lanao del Norte', 'Resigned', 'test123@mail.com', '1999-03-29', '0000-00-00', 'Single', NULL, NULL, '77-657567-56756', '', '4234-4-42-342344', '', NULL, NULL, 'McDO', '8700', 'Mother', '2025-03-28 07:25:57', '2025-03-30 00:38:22', 0),
(7, 32, 'Kim', 'Valencia', 'asd', '', 'Male', 'asdasd', 'asdasdasd', 'asdasd', 'asdasdasd', 'Active', 'kim+123321123@gmail.com', '1999-01-30', '2025-03-30', 'Married', NULL, NULL, '', '', 'asdasd', '', NULL, NULL, 'asdas', 'dasd', 'as', '2025-03-30 05:22:09', '2025-03-30 07:26:13', 0),
(8, 32, 'Jackson', 'SON', 'Nero', NULL, 'Male', 'asdasd', 'asdasd', 'asdasd', 'sadasd', 'Active', 'jackson@mail.com', '1993-03-30', '2025-03-30', 'Single', NULL, NULL, 'asd', 'sad', 'dsad', NULL, NULL, NULL, 'sad', 'sdsad', 'sad', '2025-03-30 05:26:27', '2025-03-30 05:26:27', 0),
(9, 32, 'asdasd', 'asdasd', 'asdasd', '', 'Male', 'asdasd', 'sadasd', 'sadsad', 'asdasd', 'Active', 'soap@mail.com', '1993-03-03', '2025-03-30', 'Married', NULL, NULL, 'asd', 'sad', '', '', NULL, NULL, '', '', '', '2025-03-30 05:28:43', '2025-03-30 06:54:49', 0),
(10, 36, 'Duke', 'Grey', 'Mann', '09355891759', 'Female', '', '', '', '', 'Active', 'asd2@mail.com', '0000-00-00', '0000-00-00', 'Single', NULL, NULL, '', '', '', '', NULL, NULL, '', '', '', '2025-03-30 06:39:23', '2025-03-30 07:25:56', 0),
(11, 37, 'John', 'Stewart', 'Brown', '09355891759', 'Male', 'fdasdasd', 'asdasd', 'sdasdsa', 'dsad', 'Inactive', 'john.stewart@skinlogics.com', '1987-03-30', '2025-03-30', 'Single', NULL, NULL, '', '', '', '', NULL, NULL, '', '', '', '2025-03-30 06:40:24', '2025-03-30 06:54:28', 0),
(12, 38, 'dsa', 'sadsad', 'asdasd', '09355891759', 'Male', 'asdasd', 'asdasd', 'asdas', 'dasdas', 'Active', 'sda@mail.com', '1993-03-30', '2025-03-30', 'Single', NULL, NULL, 'sadsad', 'sad', '', NULL, NULL, NULL, '', '', '', '2025-03-30 06:46:09', '2025-03-30 07:56:29', 0),
(13, 38, 'dsa', 'sadsad', 'asdasd', '09355891759', 'Male', 'asdasd', 'asdasd', 'asdas', 'dasdas', 'Active', 'sda1@mail.com', '1993-07-18', '2025-03-30', 'Single', NULL, NULL, 'sadsad', 'sad', '', NULL, NULL, NULL, '', '', '', '2025-03-30 06:46:09', '2025-03-30 06:46:09', 0),
(14, 38, 'dsa', 'sadsad', 'asdasd', '09355891759', 'Male', 'asdasd', 'asdasd', 'asdas', 'dasdas', 'Active', 'sda1@mail.com', '1993-07-18', '2025-03-30', 'Single', NULL, NULL, 'sadsad', 'sad', '', NULL, NULL, NULL, '', '', '', '2025-03-30 06:46:09', '2025-03-30 06:46:09', 0),
(15, 32, 'asdasd', 'asdasd', 'asdasd', '', 'Male', 'asdasd', 'sadasd', 'sadsad', 'asdasd', 'Active', 'soap@mail.com', '1993-03-03', '2025-03-30', 'Married', NULL, NULL, 'asd', 'sad', '', '', NULL, NULL, '', '', '', '2025-03-30 05:28:43', '2025-03-30 06:54:49', 0),
(16, 32, 'Jackson', 'SON', 'Nero', NULL, 'Male', 'asdasd', 'asdasd', 'asdasd', 'sadasd', 'Active', 'jackson@mail.com', '1993-03-30', '2025-03-30', 'Single', 3, 7, 'asd', 'sad', 'dsad', NULL, NULL, NULL, 'sad', 'sdsad', 'sad', '2025-03-30 05:26:27', '2025-04-09 16:53:37', 0);

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `job_id` int(11) NOT NULL,
  `job_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `salary_frequency` enum('Hourly','Weekly','Bi-Weekly','Monthly','Annually') DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `department_id` int(11) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job`
--

INSERT INTO `job` (`job_id`, `job_name`, `description`, `salary`, `salary_frequency`, `status`, `department_id`, `date_modified`, `date_created`) VALUES
(1, 'Software Engineer', 'Develop and maintain software applications.', 75000.00, 'Monthly', 'Inactive', 5, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(2, 'Data Analyst', 'Analyze and interpret complex data sets.', 50000.00, 'Monthly', 'Inactive', 8, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(3, 'Project Manager', 'Plan, execute, and close projects.', 85000.00, 'Monthly', 'Active', 9, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(4, 'Marketing Specialist', 'Develop and implement marketing strategies.', 55000.00, 'Monthly', 'Active', 2, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(5, 'Customer Support Representative', 'Provide technical and customer support.', 40000.00, 'Monthly', 'Active', 7, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(6, 'Web Developer', 'Design and build websites and web applications.', 70000.00, 'Monthly', 'Active', 5, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(7, 'Financial Analyst', 'Analyze financial data and provide recommendations.', 80000.00, 'Monthly', 'Active', 3, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(8, 'Human Resources Manager', 'Manage employee relations and HR functions.', 90000.00, 'Monthly', 'Active', 4, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(9, 'Sales Representative', 'Sell products or services to customers.', 45000.00, 'Monthly', 'Inactive', 1, '2025-04-15 12:44:50', '2025-04-15 09:25:52'),
(10, 'Graphic Designer', 'Create visual concepts and designs.', 50000.00, 'Monthly', 'Inactive', 6, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(11, 'Cybersecurity Analyst', 'Protect systems and networks from cyber threats.', 85000.00, 'Monthly', 'Active', 5, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(12, 'AI Research Scientist', 'Develop and research artificial intelligence algorithms.', 120000.00, 'Monthly', 'Active', 9, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(13, 'Event Coordinator', 'Plan and organize corporate and social events.', 60000.00, 'Monthly', 'Active', 6, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(14, 'UX/UI Designer', 'Design user-friendly interfaces for apps and websites.', 75000.00, 'Monthly', 'Active', 5, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(15, 'Health and Wellness Coach', 'Provide guidance on personal health and wellness practices.', 50000.00, 'Monthly', 'Active', 6, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(16, 'Environmental Consultant', 'Advise businesses on sustainable practices.', 70000.00, 'Monthly', 'Active', 9, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(17, 'Robotics Engineer', 'Design and build robots for industrial and personal use.', 95000.00, 'Monthly', 'Active', 9, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(18, 'Social Media Manager', 'Manage social media strategies and online brand presence.', 55000.00, 'Monthly', 'Active', 6, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(19, 'Medical Laboratory Technician', 'Perform lab tests and analyze samples for medical purposes.', 65000.00, 'Monthly', 'Active', 8, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(20, 'Legal Assistant', 'Support attorneys with research and administrative tasks.', 48000.00, 'Monthly', 'Active', 10, '2025-04-15 09:05:21', '2025-04-15 09:25:52'),
(33, 'VA', 'Support attorneys with research and administrative tasks.', 48000.00, 'Monthly', 'Active', 6, '2025-04-15 09:05:21', '2025-04-15 09:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `leave_request`
--

CREATE TABLE `leave_request` (
  `leave_id` int(11) NOT NULL,
  `leave_type` enum('Vacation','Sick','Personal','Emergency','Bereavement','Maternity','Paternity','Sabbatical') DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `tl_approval` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `hr_manager_approval` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `date_created` datetime DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tl_approval_date` datetime DEFAULT NULL,
  `hr_manager_approval_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_request`
--

INSERT INTO `leave_request` (`leave_id`, `leave_type`, `start_date`, `end_date`, `reason`, `employee_id`, `remarks`, `tl_approval`, `hr_manager_approval`, `date_created`, `date_modified`, `tl_approval_date`, `hr_manager_approval_date`) VALUES
(1, 'Vacation', '2025-03-29', '2025-03-31', 'Vacation leave on Singapore', 2, NULL, 'Approved', 'Approved', '2025-03-21 17:21:16', '2025-04-15 09:05:34', NULL, NULL),
(2, 'Sick', '2025-03-25', '2025-03-25', 'Not feeling well today', 3, NULL, 'Approved', 'Approved', '2025-03-25 17:39:50', '2025-04-15 09:05:34', NULL, NULL),
(3, 'Sick', '2025-03-29', '2025-03-29', 'Caught a cold', 1, NULL, 'Approved', 'Approved', '2025-03-29 17:53:46', '2025-04-15 09:05:34', NULL, NULL),
(4, 'Emergency', '2025-03-31', '2025-03-31', 'Test', 1, NULL, 'Approved', 'Approved', '2025-03-31 17:59:29', '2025-04-15 09:05:34', NULL, NULL),
(5, 'Sick', '2025-03-29', '2025-03-29', 'Caught a cold', 1, NULL, 'Approved', 'Approved', '2025-03-29 17:53:46', '2025-04-15 09:05:34', NULL, NULL),
(6, 'Sick', '2025-03-25', '2025-03-25', 'Not feeling well today', 3, NULL, 'Approved', 'Approved', '2025-03-25 17:39:50', '2025-04-15 09:05:34', NULL, NULL),
(7, 'Emergency', '2025-03-25', '2025-03-25', 'My daughter is sick', 3, NULL, 'Approved', 'Approved', '2025-03-25 17:39:50', '2025-04-15 09:05:34', NULL, NULL),
(8, 'Paternity', '2025-03-25', '2025-03-25', 'My wife is on labor', 3, NULL, 'Approved', 'Approved', '2025-03-25 17:39:50', '2025-04-15 09:05:34', NULL, NULL),
(9, 'Paternity', '2025-03-25', '2025-03-25', 'My wife is on labor', 3, NULL, 'Approved', 'Approved', '2025-03-25 17:39:50', '2025-04-15 09:05:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `overtime`
--

CREATE TABLE `overtime` (
  `overtime_id` int(11) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp(),
  `shift_for_the_day` enum('Morning','Afternoon','Night','Day Off') DEFAULT NULL,
  `start_time` date DEFAULT NULL,
  `end_time` date DEFAULT NULL,
  `ot_type` enum('Weekday','Weekend','Holiday') DEFAULT NULL,
  `ot_reason` text DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `tl_remarks` text DEFAULT NULL,
  `tl_approval` enum('Pending','Approved','Rejected') DEFAULT NULL,
  `hr_manager_approval` enum('Pending','Approved','Rejected') DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tl_approval_date` datetime DEFAULT NULL,
  `hr_manager_approval_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `bonus` decimal(10,2) DEFAULT NULL,
  `overtime_id` int(11) DEFAULT NULL,
  `net_pay` decimal(10,2) DEFAULT NULL,
  `rate_id` int(11) DEFAULT NULL,
  `payroll_start_date` date NOT NULL,
  `payroll_end_date` date NOT NULL,
  `sss_deduction` decimal(10,2) DEFAULT NULL,
  `philhealth_deduction` decimal(10,2) DEFAULT NULL,
  `pagibig_deduction` decimal(10,2) DEFAULT NULL,
  `tax_deduction` decimal(10,2) DEFAULT NULL,
  `other_deduction` decimal(10,2) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `rates_id` int(11) NOT NULL,
  `sss_rate` decimal(10,4) DEFAULT NULL,
  `philhealth_rate` decimal(10,4) DEFAULT NULL,
  `tax` decimal(10,4) DEFAULT NULL,
  `pagibig_rate` decimal(10,4) DEFAULT NULL,
  `overtime_rate` decimal(10,4) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_account_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `pass` varchar(255) NOT NULL,
  `account_type` enum('Admin','User') DEFAULT NULL,
  `avatar` varchar(256) DEFAULT 'default.jpg',
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_account_id`, `username`, `email`, `pass`, `account_type`, `avatar`, `date_modified`) VALUES
(1, 'admin', 'kimo0rven@gmail.com', 'admin', 'Admin', '1.jpg', '2025-04-15 09:08:17'),
(2, 'user', 'kimorvenvalencia@gmail.com', 'user', 'User', '2.jpg', '2025-04-15 09:08:17'),
(3, 'user1', 'kimorvenvalencia+user1@gmail.com', 'user1', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(4, 'Guideau_', 'test@mail.com', '$2y$10$FrgKmyfqP6M6uDNfFSKfDO/Iky3ooZh32IN2rFHdrRSMfyo6y6MlG', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(22, '123', 'test1@mail.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(25, 'C23-0520', 'kimo0rven+zoro@gmail.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(26, 'admin1', 'kimorvenvalencia+tester@gmail.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(27, 'admin1', 'kimorvenvalencia+tester1@gmail.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(29, '3312', 'kimo0rven+testing123123@gmail.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(32, 'lol', 'test123@mail.com', 'lol', 'User', '321.png', '2025-04-15 09:08:17'),
(33, 'kimklajda', 'kim@gmai.com', 'pilotmod', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(34, 'testxyz', 'xyz@mail.com', 'xyz', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(35, 'visit', 'visit@mail.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(36, 'asdas', 'asd2@mail.com', 'asdasdasd', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(37, 'john', 'john.stewart@skinlogics.com', '123', 'User', 'default.jpg', '2025-04-15 09:08:17'),
(38, 'asdasd', 'sda@mail.com', 'dsad', 'User', 'default.jpg', '2025-04-15 09:08:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `FK_DepartmentManager` (`manager_id`),
  ADD KEY `FK_DepartmentTeamLeader` (`team_leader_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `fk_employee_user_account` (`user_account_id`),
  ADD KEY `FK_Manager` (`manager_id`),
  ADD KEY `FK_TeamLeader` (`team_leader_id`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `fk_job_department` (`department_id`);

--
-- Indexes for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `overtime`
--
ALTER TABLE `overtime`
  ADD PRIMARY KEY (`overtime_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `overtime_id` (`overtime_id`),
  ADD KEY `rate_id` (`rate_id`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`rates_id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `leave_request`
--
ALTER TABLE `leave_request`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `overtime`
--
ALTER TABLE `overtime`
  MODIFY `overtime_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `rates_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `FK_DepartmentManager` FOREIGN KEY (`manager_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `FK_DepartmentTeamLeader` FOREIGN KEY (`team_leader_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `department_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `department_ibfk_2` FOREIGN KEY (`team_leader_id`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `FK_Manager` FOREIGN KEY (`manager_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `FK_TeamLeader` FOREIGN KEY (`team_leader_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job` (`job_id`),
  ADD CONSTRAINT `employee_ibfk_3` FOREIGN KEY (`manager_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `employee_ibfk_4` FOREIGN KEY (`team_leader_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `employee_ibfk_5` FOREIGN KEY (`user_account_id`) REFERENCES `user_account` (`user_account_id`),
  ADD CONSTRAINT `fk_employee_user_account` FOREIGN KEY (`user_account_id`) REFERENCES `user_account` (`user_account_id`);

--
-- Constraints for table `job`
--
ALTER TABLE `job`
  ADD CONSTRAINT `fk_job_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`);

--
-- Constraints for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD CONSTRAINT `leave_request_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `overtime`
--
ALTER TABLE `overtime`
  ADD CONSTRAINT `overtime_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`),
  ADD CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`overtime_id`) REFERENCES `overtime` (`overtime_id`),
  ADD CONSTRAINT `payroll_ibfk_3` FOREIGN KEY (`rate_id`) REFERENCES `rates` (`rates_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
