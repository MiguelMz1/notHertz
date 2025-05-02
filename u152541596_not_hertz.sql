-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- 생성 시간: 25-05-02 04:13
-- 서버 버전: 10.11.10-MariaDB
-- PHP 버전: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `u152541596_not_hertz`
--

DELIMITER $$
--
-- 프로시저
--
CREATE DEFINER=`u152541596_notme`@`127.0.0.1` PROCEDURE `FindCarsByCategory` (IN `in_category_name` VARCHAR(255))   BEGIN
    SELECT
        c.car_id,
        c.brand,
        c.model,
        c.year,
        c.license_plate,
        c.rental_rate,
        c.status,
        c.mileage,
        c.fuel_type,
        cat.category_name
    FROM cars c
    JOIN car_categories cat ON c.category_id = cat.category_id
    WHERE cat.category_name = in_category_name
    ORDER BY c.brand, c.model;
END$$

CREATE DEFINER=`u152541596_notme`@`127.0.0.1` PROCEDURE `GetActiveRentalsForCustomer` (IN `in_customer_id` INT)   BEGIN
    SELECT
        ra.rental_id,
        ra.rental_start,
        ra.rental_end,
        ra.total_cost,
        c.brand AS car_brand,
        c.model AS car_model,
        c.license_plate,
        loc_pickup.location_name AS pickup_location,
        loc_dropoff.location_name AS dropoff_location
    FROM rental_agreements ra
    JOIN cars c ON ra.car_id = c.car_id
    JOIN locations loc_pickup ON ra.pickup_location_id = loc_pickup.location_id
    JOIN locations loc_dropoff ON ra.dropoff_location_id = loc_dropoff.location_id
    WHERE ra.customer_id = in_customer_id AND ra.status = 'active'
    ORDER BY ra.rental_start;
END$$

--
-- 함수
--
CREATE DEFINER=`u152541596_notme`@`127.0.0.1` FUNCTION `CalculateRentalCost` (`in_car_id` INT, `in_start_date` DATETIME, `in_end_date` DATETIME) RETURNS DECIMAL(10,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE calculated_days INT;
    DECLARE daily_rate DECIMAL(10,2);
    DECLARE total_cost DECIMAL(10,2);

    IF in_car_id IS NULL OR in_start_date IS NULL OR in_end_date IS NULL OR in_end_date <= in_start_date THEN
        RETURN 0.00; 
    END IF;

    SELECT rental_rate INTO daily_rate
    FROM cars
    WHERE car_id = in_car_id;

    IF daily_rate IS NULL THEN
        RETURN 0.00;
    END IF;
    SET calculated_days = CalculateRentalDays(in_start_date, in_end_date);
    SET total_cost = daily_rate * calculated_days;

    RETURN total_cost;
END$$

CREATE DEFINER=`u152541596_notme`@`127.0.0.1` FUNCTION `CalculateRentalDays` (`start_date` DATETIME, `end_date` DATETIME) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE total_minutes INT;
    DECLARE rental_days INT;

    IF start_date IS NULL OR end_date IS NULL OR end_date <= start_date THEN
        RETURN 0; 
    END IF;

    SET total_minutes = TIMESTAMPDIFF(MINUTE, start_date, end_date);
    SET rental_days = CEILING(total_minutes / 1440.0); -- (60min*24hr)

    IF rental_days = 0 AND total_minutes > 0 THEN
        SET rental_days = 1;
    END IF;

    RETURN rental_days;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `AvailableCarsDetails`
-- (See below for the actual view)
--
CREATE TABLE `AvailableCarsDetails` (
`car_id` int(11)
,`brand` varchar(255)
,`model` varchar(255)
,`year` int(11)
,`license_plate` varchar(255)
,`rental_rate` decimal(10,2)
,`mileage` int(11)
,`fuel_type` enum('gasoline','diesel','electric','hybrid')
,`category_name` varchar(255)
,`category_description` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `CarMaintenanceSummary`
-- (See below for the actual view)
--
CREATE TABLE `CarMaintenanceSummary` (
`car_id` int(11)
,`brand` varchar(255)
,`model` varchar(255)
,`year` int(11)
,`license_plate` varchar(255)
,`current_car_status` enum('available','rented','maintenance')
,`mileage` int(11)
,`last_service_date` datetime
);

-- --------------------------------------------------------

--
-- 테이블 구조 `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `license_plate` varchar(255) NOT NULL,
  `rental_rate` decimal(10,2) NOT NULL,
  `status` enum('available','rented','maintenance') DEFAULT 'available',
  `mileage` int(11) NOT NULL,
  `fuel_type` enum('gasoline','diesel','electric','hybrid') NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `cars`
--

INSERT INTO `cars` (`car_id`, `brand`, `model`, `year`, `license_plate`, `rental_rate`, `status`, `mileage`, `fuel_type`, `category_id`) VALUES
(35, 'Toyota', 'Camry', 2023, '9ABC123', 60.00, 'rented', 15500, 'gasoline', 17),
(36, 'Honda', 'CR-V', 2022, '8XYZ789', 70.00, 'available', 22500, 'gasoline', 17),
(37, 'Tesla', 'Model 3', 2024, 'TESLA01', 95.00, 'available', 8500, 'electric', 19),
(38, 'BMW', 'X5', 2023, 'LUXCAR1', 115.00, 'available', 11500, 'hybrid', 17),
(39, 'Ford', 'Fusion', 2021, 'HYB456', 55.00, 'rented', 45500, 'hybrid', 17),
(40, 'Nissan', 'Altima', 2022, 'SEDAN7', 58.00, 'rented', 31500, 'gasoline', 20),
(41, 'Jeep', 'Wrangler', 2023, 'OFFRD1', 80.00, 'available', 19000, 'gasoline', 17),
(42, 'Chevrolet', 'Malibu', 2023, 'CHEVY01', 59.00, 'available', 17200, 'gasoline', 17),
(43, 'Volvo', 'XC90 Recharge', 2024, 'VOLVOHYB', 130.00, 'available', 6100, 'hybrid', 18);

-- --------------------------------------------------------

--
-- 테이블 구조 `car_categories`
--

CREATE TABLE `car_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `car_categories`
--

INSERT INTO `car_categories` (`category_id`, `category_name`, `description`) VALUES
(17, 'Sedan', 'Standard 4-door sedan, suitable for families or business.'),
(18, 'SUV', 'Sport Utility Vehicle, good for space and versatility.'),
(19, 'Electric', 'Fully electric vehicle, zero emissions.'),
(20, 'Luxury', 'High-end vehicle with premium features.');

-- --------------------------------------------------------

--
-- Stand-in structure for view `CurrentRentalsSummary`
-- (See below for the actual view)
--
CREATE TABLE `CurrentRentalsSummary` (
`rental_id` int(11)
,`rental_start` datetime
,`rental_end` datetime
,`customer_name` varchar(511)
,`customer_phone` varchar(255)
,`car_details` text
,`employee_name` varchar(511)
,`pickup_location` varchar(255)
,`dropoff_location` varchar(255)
,`total_cost` decimal(10,2)
);

-- --------------------------------------------------------

--
-- 테이블 구조 `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `license_number` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `phone`, `license_number`, `address`, `date_of_birth`) VALUES
(14, 'Ethan', 'Miller', 'ethan.m@nothertz.com', '213-555-1001', 'CADL98765', '100 Rodeo Dr, Beverly Hills, CA 90210', '1988-07-25'),
(15, 'Olivia', 'Jones', 'olivia.j@nothertz.com', '646-555-2002', 'NYDL54321', '200 Park Ave, New York, NY 10166', '1992-03-12'),
(16, 'Liam', 'Wilson', 'liam.w@nothertz.com', '310-555-3003', 'CADL12300', '300 Santa Monica Pier, Santa Monica, CA 90401', '1995-09-01'),
(17, 'Sophia', 'Brown', 'sophia.b@nothertz.com', '212-555-4004', 'NYDL67800', '400 Wall St, New York, NY 10005', '1983-12-05'),
(18, 'Lucas', 'Garcia', 'lucas.g@nothertz.com', '646-555-1122', 'NYDL5566X', '110 Livingston St, Brooklyn, NY 11201', '1989-04-14');

-- --------------------------------------------------------

--
-- 테이블 구조 `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `role`, `email`) VALUES
(18, 'Alice', 'Smith', 'Manager', 'alice.smith@nothertz.com'),
(19, 'Bob', 'Johnson', 'Rental Agent', 'bob.johnson@nothertz.com'),
(20, 'Charlie', 'Davis', 'Mechanic', 'charlie.davis@nothertz.com'),
(21, 'Diana', 'Ross', 'Rental Agent', 'diana.ross@nothertz.com');

-- --------------------------------------------------------

--
-- 테이블 구조 `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `locations`
--

INSERT INTO `locations` (`location_id`, `location_name`, `address`, `phone`) VALUES
(14, 'Downtown LA', '500 W Temple St, Los Angeles, CA 90012', '213-100-1000'),
(15, 'LAX Airport', '1 World Way, Los Angeles, CA 90045', '310-200-2000'),
(16, 'Midtown NYC', '150 W 50th St, New York, NY 10019', '212-300-3000'),
(17, 'JFK Airport', 'JFK Access Rd, Jamaica, NY 11430', '718-400-4000'),
(18, 'Santa Monica Pier', '200 Santa Monica Pier, Santa Monica, CA 90401', '310-500-5000'),
(19, 'Brooklyn Downtown', '210 Joralemon St, Brooklyn, NY 11201', '718-600-6000');

-- --------------------------------------------------------

--
-- 테이블 구조 `maintenance`
--

CREATE TABLE `maintenance` (
  `maintenance_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `service_date` datetime NOT NULL,
  `details` text NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `performed_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `maintenance`
--

INSERT INTO `maintenance` (`maintenance_id`, `car_id`, `service_date`, `details`, `cost`, `performed_by`) VALUES
(1, 39, '2025-05-02 10:30:00', 'cleaning', 45.00, 'Michael M'),
(2, 42, '2025-05-02 01:50:00', 'Breaks Inspections', 500.00, 'Bill M');

-- --------------------------------------------------------

--
-- Stand-in structure for view `PastRentalsSummary`
-- (See below for the actual view)
--
CREATE TABLE `PastRentalsSummary` (
`rental_id` int(11)
,`rental_start` datetime
,`rental_end` datetime
,`total_cost` decimal(10,2)
,`status` enum('active','completed','cancelled')
,`customer_name` varchar(511)
,`customer_phone` varchar(255)
,`car_details` text
,`employee_name` varchar(511)
,`pickup_location` varchar(255)
,`dropoff_location` varchar(255)
);

-- --------------------------------------------------------

--
-- 테이블 구조 `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `payment_date` datetime NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('credit_card','debit_card','cash','paypal') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `payments`
--

INSERT INTO `payments` (`payment_id`, `rental_id`, `payment_date`, `amount`, `method`) VALUES
(6, 29, '2025-04-24 11:05:00', 340.00, 'paypal');

-- --------------------------------------------------------

--
-- 테이블 구조 `rental_agreements`
--

CREATE TABLE `rental_agreements` (
  `rental_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `pickup_location_id` int(11) NOT NULL,
  `dropoff_location_id` int(11) NOT NULL,
  `rental_start` datetime NOT NULL,
  `rental_end` datetime NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `insurance_included` tinyint(1) DEFAULT 0,
  `status` enum('active','completed','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 테이블의 덤프 데이터 `rental_agreements`
--

INSERT INTO `rental_agreements` (`rental_id`, `customer_id`, `car_id`, `employee_id`, `pickup_location_id`, `dropoff_location_id`, `rental_start`, `rental_end`, `total_cost`, `insurance_included`, `status`) VALUES
(10, 17, 39, 19, 14, 14, '2025-04-29 17:52:00', '2025-05-03 17:52:00', 220.00, 0, 'active'),
(17, 15, 36, 19, 16, 17, '2025-04-26 10:00:00', '2025-04-29 10:00:00', 210.00, 0, 'completed'),
(18, 16, 41, 21, 14, 18, '2025-05-03 14:00:00', '2025-05-07 14:00:00', 320.00, 1, 'cancelled'),
(19, 17, 40, 18, 15, 15, '2025-05-04 09:30:00', '2025-05-09 10:00:00', 825.00, 1, 'active'),
(29, 15, 38, 19, 18, 19, '2025-05-04 10:00:00', '2025-05-10 10:00:00', 324.00, 1, 'completed'),
(32, 17, 35, 18, 17, 16, '2025-04-20 13:00:00', '2025-04-24 11:00:00', 340.00, 0, 'completed'),
(34, 18, 42, 19, 17, 17, '2025-04-30 21:30:00', '2025-05-09 21:30:00', 531.00, 0, 'cancelled'),
(35, 18, 35, 20, 16, 16, '2025-04-29 21:32:00', '2025-05-03 21:33:00', 300.00, 0, 'active');

--
-- 트리거 `rental_agreements`
--
DELIMITER $$
CREATE TRIGGER `rental_agreements_after_insert` AFTER INSERT ON `rental_agreements` FOR EACH ROW BEGIN
    IF NEW.status = 'active' THEN
        UPDATE cars SET status = 'rented'
        WHERE car_id = NEW.car_id AND status = 'available';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `rental_agreements_after_update` AFTER UPDATE ON `rental_agreements` FOR EACH ROW BEGIN
    IF OLD.status = 'active' AND (NEW.status = 'completed' OR NEW.status = 'cancelled') THEN
        UPDATE cars 
        SET status = 'available'
        WHERE car_id = NEW.car_id;
	END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 뷰 구조 `AvailableCarsDetails`
--
DROP TABLE IF EXISTS `AvailableCarsDetails`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u152541596_notme`@`127.0.0.1` SQL SECURITY DEFINER VIEW `AvailableCarsDetails`  AS SELECT `c`.`car_id` AS `car_id`, `c`.`brand` AS `brand`, `c`.`model` AS `model`, `c`.`year` AS `year`, `c`.`license_plate` AS `license_plate`, `c`.`rental_rate` AS `rental_rate`, `c`.`mileage` AS `mileage`, `c`.`fuel_type` AS `fuel_type`, `ct`.`category_name` AS `category_name`, `ct`.`description` AS `category_description` FROM (`cars` `c` join `car_categories` `ct` on(`c`.`category_id` = `ct`.`category_id`)) WHERE `c`.`status` = 'available' ;

-- --------------------------------------------------------

--
-- 뷰 구조 `CarMaintenanceSummary`
--
DROP TABLE IF EXISTS `CarMaintenanceSummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u152541596_notme`@`127.0.0.1` SQL SECURITY DEFINER VIEW `CarMaintenanceSummary`  AS SELECT `c`.`car_id` AS `car_id`, `c`.`brand` AS `brand`, `c`.`model` AS `model`, `c`.`year` AS `year`, `c`.`license_plate` AS `license_plate`, `c`.`status` AS `current_car_status`, `c`.`mileage` AS `mileage`, max(`m`.`service_date`) AS `last_service_date` FROM (`cars` `c` left join `maintenance` `m` on(`c`.`car_id` = `m`.`car_id`)) GROUP BY `c`.`car_id`, `c`.`brand`, `c`.`model`, `c`.`year`, `c`.`license_plate`, `c`.`status`, `c`.`mileage` ORDER BY `c`.`brand` ASC, `c`.`model` ASC ;

-- --------------------------------------------------------

--
-- 뷰 구조 `CurrentRentalsSummary`
--
DROP TABLE IF EXISTS `CurrentRentalsSummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u152541596_notme`@`127.0.0.1` SQL SECURITY DEFINER VIEW `CurrentRentalsSummary`  AS SELECT `ra`.`rental_id` AS `rental_id`, `ra`.`rental_start` AS `rental_start`, `ra`.`rental_end` AS `rental_end`, concat(`cust`.`first_name`,' ',`cust`.`last_name`) AS `customer_name`, `cust`.`phone` AS `customer_phone`, concat(`car`.`brand`,' ',`car`.`model`,' (',`car`.`license_plate`,')') AS `car_details`, concat(`emp`.`first_name`,' ',`emp`.`last_name`) AS `employee_name`, `pickup_loc`.`location_name` AS `pickup_location`, `dropoff_loc`.`location_name` AS `dropoff_location`, `ra`.`total_cost` AS `total_cost` FROM (((((`rental_agreements` `ra` join `customers` `cust` on(`ra`.`customer_id` = `cust`.`customer_id`)) join `cars` `car` on(`ra`.`car_id` = `car`.`car_id`)) join `employees` `emp` on(`ra`.`employee_id` = `emp`.`employee_id`)) join `locations` `pickup_loc` on(`ra`.`pickup_location_id` = `pickup_loc`.`location_id`)) join `locations` `dropoff_loc` on(`ra`.`dropoff_location_id` = `dropoff_loc`.`location_id`)) WHERE `ra`.`status` = 'active' ;

-- --------------------------------------------------------

--
-- 뷰 구조 `PastRentalsSummary`
--
DROP TABLE IF EXISTS `PastRentalsSummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u152541596_notme`@`127.0.0.1` SQL SECURITY DEFINER VIEW `PastRentalsSummary`  AS SELECT `ra`.`rental_id` AS `rental_id`, `ra`.`rental_start` AS `rental_start`, `ra`.`rental_end` AS `rental_end`, `ra`.`total_cost` AS `total_cost`, `ra`.`status` AS `status`, concat(`c`.`first_name`,' ',`c`.`last_name`) AS `customer_name`, `c`.`phone` AS `customer_phone`, concat(`car`.`brand`,' ',`car`.`model`,' (',`car`.`license_plate`,')') AS `car_details`, concat(`e`.`first_name`,' ',`e`.`last_name`) AS `employee_name`, `pl`.`location_name` AS `pickup_location`, `dl`.`location_name` AS `dropoff_location` FROM (((((`rental_agreements` `ra` join `customers` `c` on(`ra`.`customer_id` = `c`.`customer_id`)) join `cars` `car` on(`ra`.`car_id` = `car`.`car_id`)) join `employees` `e` on(`ra`.`employee_id` = `e`.`employee_id`)) join `locations` `pl` on(`ra`.`pickup_location_id` = `pl`.`location_id`)) join `locations` `dl` on(`ra`.`dropoff_location_id` = `dl`.`location_id`)) WHERE `ra`.`status` <> 'active' ;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `unique_license_plate` (`license_plate`),
  ADD KEY `cars_fk_category` (`category_id`);

--
-- 테이블의 인덱스 `car_categories`
--
ALTER TABLE `car_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- 테이블의 인덱스 `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_license_number` (`license_number`);

--
-- 테이블의 인덱스 `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `unique_employee_email` (`email`);

--
-- 테이블의 인덱스 `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `unique_location_name` (`location_name`);

--
-- 테이블의 인덱스 `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `maintenance_fk_cars` (`car_id`);

--
-- 테이블의 인덱스 `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payments_fk_rental` (`rental_id`);

--
-- 테이블의 인덱스 `rental_agreements`
--
ALTER TABLE `rental_agreements`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `rental_fk_customers` (`customer_id`),
  ADD KEY `rental_fk_cars` (`car_id`),
  ADD KEY `rental_fk_employees` (`employee_id`),
  ADD KEY `rental_fk_pickup_location` (`pickup_location_id`),
  ADD KEY `rental_fk_dropoff_location` (`dropoff_location_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- 테이블의 AUTO_INCREMENT `car_categories`
--
ALTER TABLE `car_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 테이블의 AUTO_INCREMENT `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- 테이블의 AUTO_INCREMENT `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 테이블의 AUTO_INCREMENT `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- 테이블의 AUTO_INCREMENT `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 테이블의 AUTO_INCREMENT `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 테이블의 AUTO_INCREMENT `rental_agreements`
--
ALTER TABLE `rental_agreements`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- 덤프된 테이블의 제약사항
--

--
-- 테이블의 제약사항 `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_fk_category` FOREIGN KEY (`category_id`) REFERENCES `car_categories` (`category_id`);

--
-- 테이블의 제약사항 `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_fk_cars` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`);

--
-- 테이블의 제약사항 `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_fk_rental` FOREIGN KEY (`rental_id`) REFERENCES `rental_agreements` (`rental_id`);

--
-- 테이블의 제약사항 `rental_agreements`
--
ALTER TABLE `rental_agreements`
  ADD CONSTRAINT `rental_fk_cars` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`),
  ADD CONSTRAINT `rental_fk_customers` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `rental_fk_dropoff_location` FOREIGN KEY (`dropoff_location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `rental_fk_employees` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `rental_fk_pickup_location` FOREIGN KEY (`pickup_location_id`) REFERENCES `locations` (`location_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
