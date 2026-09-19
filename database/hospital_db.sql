-- =======================================================
-- Hospital Patient Management System (HPMS)
-- Database Schema & Sample Seed Data
-- Subject: IT22013 - Web System Technologies
-- Compatible with MySQL 5.7+ / MySQL 8.0+ / MariaDB
-- =======================================================

CREATE DATABASE IF NOT EXISTS `hospital_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hospital_db`;

-- -------------------------------------------------------
-- Table structure for table `patients`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `patients`;

CREATE TABLE `patients` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_code` VARCHAR(20) NOT NULL UNIQUE,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `nic_passport` VARCHAR(25) NULL,
  `date_of_birth` DATE NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `blood_group` ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown') DEFAULT 'Unknown',
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) DEFAULT 'Colombo',
  `emergency_contact_name` VARCHAR(100) NOT NULL,
  `emergency_contact_relation` VARCHAR(50) NOT NULL,
  `emergency_contact_phone` VARCHAR(20) NOT NULL,
  `department` VARCHAR(50) NOT NULL,
  `assigned_doctor` VARCHAR(100) NOT NULL,
  `admission_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `patient_status` ENUM('Inpatient', 'Outpatient', 'Discharged', 'Emergency', 'Transferred') NOT NULL DEFAULT 'Outpatient',
  `room_bed_no` VARCHAR(30) NULL,
  `allergies` TEXT NULL,
  `medical_history` TEXT NULL,
  `symptoms_diagnosis` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_patient_code` (`patient_code`),
  INDEX `idx_name` (`first_name`, `last_name`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_department` (`department`),
  INDEX `idx_status` (`patient_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Dumping realistic seed data for table `patients`
-- -------------------------------------------------------

INSERT INTO `patients` (
  `patient_code`, `first_name`, `last_name`, `nic_passport`, `date_of_birth`, `gender`, `blood_group`,
  `phone`, `email`, `address`, `city`,
  `emergency_contact_name`, `emergency_contact_relation`, `emergency_contact_phone`,
  `department`, `assigned_doctor`, `admission_date`, `patient_status`, `room_bed_no`,
  `allergies`, `medical_history`, `symptoms_diagnosis`
) VALUES
(
  'PAT-2026-0001', 'Kasun', 'Perera', '199523401234', '1995-04-12', 'Male', 'O+',
  '0771234567', 'kasun.perera@example.com', '45/2 Galle Road, Bambalapitiya', 'Colombo',
  'Sunil Perera', 'Father', '0779876543',
  'Cardiology', 'Dr. Nihal Senanayake', '2026-09-15 09:30:00', 'Inpatient', 'Ward 3B - Bed 12',
  'Penicillin', 'Mild hypertension diagnosed in 2022', 'Acute chest pain, shortness of breath, elevated blood pressure. Monitored for arrhythmia.'
),
(
  'PAT-2026-0002', 'Nadeesha', 'Fernando', '199876504321', '1998-11-23', 'Female', 'B+',
  '0714567890', 'nadeesha.f@example.com', '128 Temple Road, Mount Lavinia', 'Dehiwala',
  'Chamara Fernando', 'Spouse', '0712345678',
  'Pediatrics', 'Dr. Anoma Jayawardena', '2026-09-17 14:15:00', 'Outpatient', NULL,
  'None reported', 'Asthma in childhood', 'Seasonal influenza, persistent dry cough, high fever for 3 days. Prescribed antipyretics and inhaler.'
),
(
  'PAT-2026-0003', 'Mohamed', 'Rizwan', '198834509876', '1988-08-05', 'Male', 'A+',
  '0768901234', 'm.rizwan@example.com', '78 Main Street, Pettah', 'Colombo',
  'Fathima Rizwan', 'Spouse', '0761239876',
  'Orthopedics', 'Dr. Rohan De Silva', '2026-09-18 11:00:00', 'Inpatient', 'Ward 1A - Bed 04',
  'Sulfa drugs', 'Previous fracture in left wrist (2018)', 'Right tibia hairline fracture after a motorcycle slip. Cast applied, admitted for pain management.'
),
(
  'PAT-2026-0004', 'Sanduni', 'Jayasinghe', '200156708765', '2001-02-17', 'Female', 'AB+',
  '0723456789', 'sanduni.j@example.com', '19 Kandy Road, Kadawatha', 'Gampaha',
  'Kamani Jayasinghe', 'Mother', '0729876543',
  'Dermatology', 'Dr. Priyantha Weerasinghe', '2026-09-16 10:45:00', 'Outpatient', NULL,
  'Dust mites, Seafood', 'Atopic dermatitis', 'Severe contact dermatitis with skin flare-ups on arms and neck. Prescribed topical corticosteroids.'
),
(
  'PAT-2026-0005', 'Dinesh', 'Kumar', '198245601982', '1982-06-30', 'Male', 'O-',
  '0756789012', 'dinesh.k@example.com', '88 Sea Street, Negombo', 'Gampaha',
  'Priya Kumar', 'Spouse', '0754321098',
  'Neurology', 'Dr. Sarath Wijeratne', '2026-09-14 08:00:00', 'Inpatient', 'ICU - Bed 02',
  'Aspirin', 'Type 2 Diabetes since 2017', 'Sudden onset dizziness, slurred speech, mild left-sided weakness. Under stroke protocol observation.'
),
(
  'PAT-2026-0006', 'Dilani', 'Bandara', '199267803456', '1992-09-10', 'Female', 'A-',
  '0701237890', 'dilani.b@example.com', '210 Peradeniya Road, Kandy', 'Kandy',
  'Upul Bandara', 'Brother', '0709871234',
  'General Medicine', 'Dr. Malini Ratnayake', '2026-09-12 15:20:00', 'Discharged', NULL,
  'None', 'No major surgical history', 'Acute viral gastroenteritis, dehydration. Rehydrated with IV fluids and discharged in stable condition.'
),
(
  'PAT-2026-0007', 'Chathura', 'Wickramasinghe', '197934208912', '1979-12-03', 'Male', 'B-',
  '0778901245', 'chathura.w@example.com', '55 Hospital Road, Kalubowila', 'Colombo',
  'Anula Wickramasinghe', 'Spouse', '0773456712',
  'ENT', 'Dr. Gamini Gunawardena', '2026-09-19 09:00:00', 'Emergency', 'Emergency Room - Bed 06',
  'Ibuprofen', 'Chronic sinusitis', 'Severe epistaxis (nasal bleeding) not responding to local pressure. Nasal packing performed.'
),
(
  'PAT-2026-0008', 'Tharushi', 'Alwis', '200389012345', '2003-05-19', 'Female', 'O+',
  '0789012345', 'tharushi.a@example.com', '67 High Level Road, Maharagama', 'Colombo',
  'Soma Alwis', 'Mother', '0781234567',
  'General Medicine', 'Dr. Malini Ratnayake', '2026-09-19 10:30:00', 'Outpatient', NULL,
  'None', 'Migraine history', 'Recurrent tension headaches with nausea. Prescribed prophylactic medication and scheduled eye examination.'
);
