CREATE DATABASE IF NOT EXISTS sinderella_db;
USE sinderella_db;

-- -- CUSTOMER TABLE
-- DROP TABLE IF EXISTS customers;
-- CREATE TABLE IF NOT EXISTS customers (
-- 	cust_id INT(11) PRIMARY KEY AUTO_INCREMENT,
-- 	cust_name VARCHAR(255) NOT NULL,
-- 	cust_phno VARCHAR(11) NOT NULL,
-- 	cust_pwd VARCHAR(255) NOT NULL,
-- 	cust_address TEXT NOT NULL,
-- 	cust_postcode VARCHAR(5) NOT NULL,
-- 	cust_area VARCHAR(100) NOT NULL,
-- 	cust_state VARCHAR(100) NOT NULL,
-- 	cust_status VARCHAR(20) NOT NULL,  -- Stores 'active', 'inactive'
-- 	last_login_date DATETIME
-- );

-- INSERT INTO customers 
-- (cust_name, cust_phno, cust_pwd, cust_address, cust_postcode, cust_area, cust_state, cust_status) VALUES
-- ('Customer One', '0123456789', 'pwd123', '12, Jalan ABC, Taman XYZ', '43000', 'Kajang', 'Selangor', 'active'),
-- ('Customer Two', '0198765432', 'pwd123', '12, Jalan ABC, Taman XYZ', '43000', 'Kajang', 'Selangor', 'active');

-- SINDERELLA TABLE
DROP TABLE IF EXISTS sinderellas;
CREATE TABLE IF NOT EXISTS sinderellas (
	sind_id INT(11) PRIMARY KEY AUTO_INCREMENT,
	sind_name VARCHAR(255) NOT NULL,
	sind_phno VARCHAR(11) NOT NULL,
	sind_pwd VARCHAR(255) NOT NULL,
	sind_address TEXT NOT NULL,
	sind_postcode VARCHAR(5) NOT NULL,
	sind_area VARCHAR(100) NOT NULL,
	sind_state VARCHAR(100) NOT NULL,
	sind_icno VARCHAR(20) NOT NULL,
	sind_icphoto_path VARCHAR(255),
	sind_profile_path VARCHAR(255),
	sind_upline_id VARCHAR(11),
	sind_status VARCHAR(20),  -- Stores 'pending', 'active', 'inactive'
	last_login_date DATETIME
); 

INSERT INTO sinderellas
(sind_name, sind_phno, sind_pwd, sind_address, sind_postcode, sind_area, sind_state, sind_icno, 
sind_icphoto_path, sind_profile_path, sind_upline_id, sind_status) VALUES
('Sinderella One', '0123456789', 'pwd123', '12, Jalan ABC, Taman XYZ', '43000', 'Kajang', 'Selangor', '123456121234', 
'../img/ic_photo/0001.jpeg','../img/profile_photo/0001.jpg', '', 'pending');

-- ADMIN TABLE
DROP TABLE IF EXISTS admins;
CREATE TABLE IF NOT EXISTS admins (
	adm_id INT(11) PRIMARY KEY AUTO_INCREMENT,
	adm_name VARCHAR(255) NOT NULL,
	adm_role VARCHAR(100) NOT NULL,  -- Stores 'Junior Admin', 'Senior Admin'
	adm_phno VARCHAR(11) NOT NULL,
	adm_pwd VARCHAR(255) NOT NULL,
    adm_status VARCHAR(20) NOT NULL DEFAULT 'active',  -- Stores 'active', 'inactive'
	last_login_date DATETIME
);

INSERT INTO admins
(adm_name, adm_role, adm_phno, adm_pwd) VALUES
('Admin One', 'Senior Admin', '0123456789', 'pwd123'),
('Admin Two', 'Junior Admin', '0198765432', 'pwd123');

-- VERIFICATION CODE TABLE
DROP TABLE IF EXISTS verification_codes;
CREATE TABLE IF NOT EXISTS verification_codes (
    code_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_phno VARCHAR(11) NOT NULL,
    ver_code VARCHAR(6) NOT NULL,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN NOT NULL DEFAULT 0
);

-- SINDERELLA'S SERVICE AREA TABLE
DROP TABLE IF EXISTS sind_service_area;
CREATE TABLE IF NOT EXISTS sind_service_area (
    service_area_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    sind_id INT(11) NOT NULL,
    area VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
);

-- QUALIFIER TEST
DROP TABLE IF EXISTS qualifier_test;
CREATE TABLE IF NOT EXISTS qualifier_test (
    question_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    question_text TEXT NOT NULL,
    f_option0 TEXT NOT NULL, -- stores the correct option
    f_option1 TEXT NOT NULL, -- stores the false option
    f_option2 TEXT NOT NULL, -- stores the false option
    f_option3 TEXT NOT NULL  -- stores the false option
);

INSERT INTO qualifier_test (question_text, f_option0, f_option1, f_option2, f_option3) VALUES
('question 1', 'true 1', 'false 1.1', 'false 1.2', 'false 1.3'),
('question 2', 'true 2', 'false 2.1', 'false 2.2', 'false 2.3'),
('question 3', 'true 3', 'false 3.1', 'false 3.2', 'false 3.3'),
('question 4', 'true 4', 'false 4.1', 'false 4.2', 'false 4.3'),
('question 5', 'true 5', 'false 5.1', 'false 5.2', 'false 5.3'),
('question 6', 'true 6', 'false 6.1', 'false 6.2', 'false 6.3'),
('question 7', 'true 7', 'false 7.1', 'false 7.2', 'false 7.3'),
('question 8', 'true 8', 'false 8.1', 'false 8.2', 'false 8.3'),
('question 9', 'true 9', 'false 9.1', 'false 9.2', 'false 9.3'),
('question 10', 'true 10', 'false 10.1', 'false 10.2', 'false 10.3'),
('question 11', 'true 11', 'false 11.1', 'false 11.2', 'false 11.3'),
('question 12', 'true 12', 'false 12.1', 'false 12.2', 'false 12.3'),
('question 13', 'true 13', 'false 13.1', 'false 13.2', 'false 13.3'),
('question 14', 'true 14', 'false 14.1', 'false 14.2', 'false 14.3'),
('question 15', 'true 15', 'false 15.1', 'false 15.2', 'false 15.3'),
('question 16', 'true 16', 'false 16.1', 'false 16.2', 'false 16.3'),
('question 17', 'true 17', 'false 17.1', 'false 17.2', 'false 17.3'),
('question 18', 'true 18', 'false 18.1', 'false 18.2', 'false 18.3'),
('question 19', 'true 19', 'false 19.1', 'false 19.2', 'false 19.3'),
('question 20', 'true 20', 'false 20.1', 'false 20.2', 'false 20.3'),
('question 21', 'true 21', 'false 21.1', 'false 21.2', 'false 21.3'),
('question 22', 'true 22', 'false 22.1', 'false 22.2', 'false 22.3'),
('question 23', 'true 23', 'false 23.1', 'false 23.2', 'false 23.3'),
('question 24', 'true 24', 'false 24.1', 'false 24.2', 'false 24.3'),
('question 25', 'true 25', 'false 25.1', 'false 25.2', 'false 25.3');

-- QUALIFIER TEST ATTEMPT HISTORY
DROP TABLE IF EXISTS qt_attempt_hist;
CREATE TABLE IF NOT EXISTS qt_attempt_hist (
    attempt_id INT(10) PRIMARY KEY AUTO_INCREMENT,
    sind_id INT(11) NOT NULL,
    attempt_date DATETIME NOT NULL,
    attempt_score INT(10) NOT NULL,
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
);

-- SINDERELLA SERVICE AREA
DROP TABLE IF EXISTS sind_service_area;
CREATE TABLE IF NOT EXISTS sind_service_area (
    service_area_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    sind_id INT(11) NOT NULL,
    area VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
);

INSERT INTO sind_service_area (sind_id, area, state) VALUES
(1, 'Kajang', 'Selangor'),
(2, 'Shah Alam', 'Selangor');

-- -- SINDERELLA AVAILABLE TIME - DATE
-- DROP TABLE IF EXISTS sind_available_time;
-- CREATE TABLE IF NOT EXISTS sind_available_time (
--     schedule_id INT(11) PRIMARY KEY AUTO_INCREMENT,
--     sind_id INT(11) NOT NULL,
--     available_date DATE NOT NULL,
--     available_from TIME,
--     available_to TIME,
--     FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
-- );

-- -- SINDERELLA AVAILABLE TIME - DAY
-- DROP TABLE IF EXISTS sind_available_day;
-- CREATE TABLE IF NOT EXISTS sind_available_day (
--     day_id INT(11) PRIMARY KEY AUTO_INCREMENT,
--     sind_id INT(11) NOT NULL,
--     day_of_week VARCHAR(10) NOT NULL,
--     available_from TIME NOT NULL,
--     available_to TIME NOT NULL,
--     FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
-- );

-- SERVICE PRICING TABLE
DROP TABLE IF EXISTS service_pricing;
CREATE TABLE IF NOT EXISTS service_pricing (
    service_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(255) NOT NULL,
    service_price DECIMAL(10, 2) NOT NULL,
    service_duration DECIMAL(10, 2) NOT NULL, -- in hours
    service_status VARCHAR(20) NOT NULL DEFAULT 'active' -- Stores 'active', 'inactive'
    -- FOREIGN KEY (service_id) REFERENCES service_pricing(service_id)
);

INSERT INTO service_pricing (service_name, service_price, service_duration) VALUES
('4 Hours Cleaning', 130.00, 4),
('2 Hours Cleaning', 86.00, 2);

-- MASTER TABLE (NUMBER)
DROP TABLE IF EXISTS master_number;
CREATE TABLE IF NOT EXISTS master_number (
    master_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    master_desc VARCHAR(255) NOT NULL,
    master_amount DECIMAL(10, 2) NOT NULL
);

INSERT INTO master_number (master_desc, master_amount) VALUES
('Sind Registration Fee', 80.00);

-- -- ADDON TABLE
-- DROP TABLE IF EXISTS addon;
-- CREATE TABLE IF NOT EXISTS addon (
--     ao_id INT(11) PRIMARY KEY AUTO_INCREMENT,
--     service_id INT(11) NOT NULL,
--     ao_desc VARCHAR(255) NOT NULL,
--     ao_price DECIMAL(10, 2) NOT NULL,
--     ao_duration DECIMAL(10, 2) NOT NULL, -- in hours
--     FOREIGN KEY (service_id) REFERENCES service_pricing(service_id)
-- );

-- INSERT INTO addon (service_id, ao_desc, ao_price, ao_duration) VALUES
-- (1, 'Extra 1 Hour', 21.25, 1), 
-- (1, 'Cleaning Tools', 40.00, 0), 
-- (2, 'Extra 1 Hour', 34.00, 1), 
-- (2, 'Cleaning Tools', 40.00, 0);

-- PRICING TABLE
DROP TABLE IF EXISTS pricing;
CREATE TABLE IF NOT EXISTS pricing (
    pricing_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    service_id INT(11) NOT NULL,
    pr_platform DECIMAL(10, 2) NOT NULL,
    pr_sind DECIMAL(10, 2) NOT NULL,
    pr_lvl1 DECIMAL(10, 2) NOT NULL,
    pr_lvl2 DECIMAL(10, 2) NOT NULL,
    pr_lvl3 DECIMAL(10, 2) NOT NULL,
    pr_lvl4 DECIMAL(10, 2) NOT NULL,
    pr_br_basic DECIMAL(10, 2) NOT NULL,
    pr_br_rate DECIMAL(10, 2) NOT NULL,
    pr_br_perf DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (service_id) REFERENCES service_pricing(service_id)
);

INSERT INTO pricing (service_id, pr_platform, pr_sind, pr_lvl1, pr_lvl2, pr_lvl3, pr_lvl4, pr_br_basic, pr_br_rate, pr_br_perf) VALUES
(1, 33.00, 85.00, 7.00, 5.00, 0.00, 0.00, 42.50, 25.50, 17.00),
(2, 18.00, 68.00, 0.00, 0.00, 0.00, 0.00, 34.00, 20.40, 13.60);

-- CUSTOMER LABEL TABLE
DROP TABLE IF EXISTS cust_label;
CREATE TABLE IF NOT EXISTS cust_label (
    clbl_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    clbl_name VARCHAR(255) NOT NULL,
    clbl_color_code VARCHAR(100) NOT NULL,
    clbl_status VARCHAR(100) NOT NULL DEFAULT 'active' -- Stores 'active', 'inactive'
);

-- CUSTOMER ID+LABEL TABLE
DROP TABLE IF EXISTS cust_id_label;
CREATE TABLE IF NOT EXISTS cust_id_label (
    cust_id_label_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    cust_id INT(11) NOT NULL,
    clbl_id INT(11) NOT NULL,
    FOREIGN KEY (cust_id) REFERENCES customers(cust_id),
    FOREIGN KEY (clbl_id) REFERENCES cust_label(clbl_id)
);

-- SINDERELLA LABEL TABLE
DROP TABLE IF EXISTS sind_label;
CREATE TABLE IF NOT EXISTS sind_label (
    slbl_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    slbl_name VARCHAR(255) NOT NULL,
    slbl_color_code VARCHAR(100) NOT NULL,
    slbl_status VARCHAR(100) NOT NULL DEFAULT 'active' -- Stores 'active', 'inactive'
);

-- SINDERELLA ID+LABEL TABLE
DROP TABLE IF EXISTS sind_id_label;
CREATE TABLE IF NOT EXISTS sind_id_label (
    sind_id_label_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    sind_id INT(11) NOT NULL,
    slbl_id INT(11) NOT NULL,
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id),
    FOREIGN KEY (slbl_id) REFERENCES sind_label(slbl_id)
);

-- -- BOOKING TABLE
-- DROP TABLE IF EXISTS bookings;
-- CREATE TABLE IF NOT EXISTS bookings (
--     booking_id INT(11) PRIMARY KEY AUTO_INCREMENT,
--     cust_id INT(11) NOT NULL,
--     sind_id INT(11) NOT NULL,
--     booking_date DATETIME NOT NULL,
--     booking_from_time TIME NOT NULL,
--     booking_to_time TIME NOT NULL,
--     service_id INT(11) NOT NULL,
--     booked_at DATETIME NOT NULL,
--     booking_status VARCHAR(20) NOT NULL,  -- Stores 'pending', 'confirm', 'done', 'rated', 'cancelled by admin', 'cancelled by customer'
--     FOREIGN KEY (cust_id) REFERENCES customers(cust_id),
--     FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id),
--     FOREIGN KEY (service_id) REFERENCES service_pricing(service_id)
-- );

-- -- BOOKING ADDON TABLE
-- DROP TABLE IF EXISTS booking_addons;
-- CREATE TABLE IF NOT EXISTS booking_addons (
--     booking_addon_id INT(11) PRIMARY KEY AUTO_INCREMENT,
--     booking_id INT(11) NOT NULL,
--     ao_id INT(11) NOT NULL,
--     FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
--     FOREIGN KEY (ao_id) REFERENCES addon(ao_id)
-- );


-----------------------------------------------------------------------
-----------------------------------------------------------------------
-----------------------------------------------------------------------
-----------------------------------------------------------------------
-----------------------------------------------------------------------
-- UPDATED APRIL 2025 BASED ON CLIENT'S REQUIREMENT + NEW FUNCTIONS

-- ADDON TABLE [[[[ENHANCEMENT]]]]
DROP TABLE IF EXISTS addon;
CREATE TABLE IF NOT EXISTS addon (
    ao_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    service_id INT(11) NOT NULL,
    ao_desc VARCHAR(255) NOT NULL,
    ao_price DECIMAL(10, 2) NOT NULL,
    ao_platform DECIMAL(10, 2) NOT NULL,
    ao_sind DECIMAL(10, 2) NOT NULL,
    ao_duration DECIMAL(10, 2) NOT NULL, -- in hours
    ao_status VARCHAR(20) NOT NULL DEFAULT 'active', -- Stores 'active', 'inactive'
    FOREIGN KEY (service_id) REFERENCES service_pricing(service_id)
);

INSERT INTO addon (service_id, ao_desc, ao_price, ao_platform, ao_sind, ao_duration) VALUES
(1, 'Extra 1 Hour', 32.50, 11.25, 21.25, 1), 
(1, 'Cleaning Tools', 40.00, 0.00, 40.00, 0), 
(2, 'Extra 1 Hour', 43.00, 9.00, 34.00, 1), 
(2, 'Cleaning Tools', 40.00, 0.00, 40.00, 0);

-- UPDATED APRIL 2025 BASED ON CLIENT'S REQUIREMENT + NEW FUNCTIONS

-- SINDERELLA'S DOWNLINE TABLE [[[[NEW]]]]
DROP TABLE IF EXISTS sind_downline;
CREATE TABLE sind_downline (
    sind_id INT(11) NOT NULL, 
    dwln_phno VARCHAR(11) NOT NULL, 
    dwln_id INT(11) DEFAULT NULL, 
    created_at DATETIME NULL,
    PRIMARY KEY (sind_id, dwln_phno), 
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id), 
    FOREIGN KEY (dwln_id) REFERENCES sinderellas(sind_id) 
);

-- UPDATED APRIL 2025 BASED ON CLIENT'S REQUIREMENT + NEW FUNCTIONS

-- SINDERELLA AVAILABLE TIME - DATE [[[[ENHANCEMENT]]]]
DROP TABLE IF EXISTS sind_available_time;
CREATE TABLE IF NOT EXISTS sind_available_time (
    schedule_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    sind_id INT(11) NOT NULL,
    available_date DATE NOT NULL,
    available_from1 TIME NULL,
    available_from2 TIME NULL,
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
);

-- SINDERELLA AVAILABLE TIME - DAY [[[[ENHANCEMENT]]]]
DROP TABLE IF EXISTS sind_available_day;
CREATE TABLE IF NOT EXISTS sind_available_day (
    day_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    sind_id INT(11) NOT NULL,
    day_of_week VARCHAR(10) NOT NULL,
    available_from1 TIME NULL,
    available_from2 TIME NULL,
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id)
);

-- UPDATED APRIL 2025 BASED ON CLIENT'S REQUIREMENT + NEW FUNCTIONS

-- CUSTOMER TABLE [[[[ENHANCEMENT]]]]
DROP TABLE IF EXISTS customers;
CREATE TABLE IF NOT EXISTS customers (
	cust_id INT(11) PRIMARY KEY AUTO_INCREMENT,
	cust_name VARCHAR(255) NOT NULL,
	cust_phno VARCHAR(11) NOT NULL,
	cust_pwd VARCHAR(255) NOT NULL,
	cust_status VARCHAR(20) NOT NULL,  -- Stores 'active', 'inactive'
	last_login_date DATETIME
);

INSERT INTO customers 
(cust_name, cust_phno, cust_pwd, cust_status) VALUES
('Customer One', '0123456789', 'password', 'active');

-- CUSTOMER ADDRESS TABLE [[[[NEW FOR ENHANCEMENT]]]]
DROP TABLE IF EXISTS cust_addresses;
CREATE TABLE IF NOT EXISTS cust_addresses (
    cust_address_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    cust_id INT(11) NOT NULL,
    cust_address TEXT NOT NULL,
    cust_postcode VARCHAR(5) NOT NULL,
    cust_area VARCHAR(100) NOT NULL,
    cust_state VARCHAR(100) NOT NULL,
    FOREIGN KEY (cust_id) REFERENCES customers(cust_id)
);

INSERT INTO cust_addresses
(cust_id, cust_address, cust_postcode, cust_area, cust_state) VALUES
(1, '12, Jalan ABC, Taman XYZ', '43000', 'Kajang', 'Selangor'),
(1, '13, Jalan DEF, Taman UVW', '43000', 'Kajang', 'Selangor');

-- UPDATED APRIL 2025 BASED ON CLIENT'S REQUIREMENT + NEW FUNCTIONS

-- BOOKING TABLE
DROP TABLE IF EXISTS bookings;
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    cust_id INT(11) NOT NULL,
    sind_id INT(11) NOT NULL,
    booking_date DATE NOT NULL,
    booking_from_time TIME NOT NULL,
    booking_to_time TIME NOT NULL,
    service_id INT(11) NOT NULL,
    full_address VARCHAR(255),
    booked_at DATETIME NOT NULL,
    booking_status VARCHAR(20) NOT NULL,  -- Stores 'pending', 'confirm', 'done', 'rated', 'cancelled by admin', 'cancelled by customer'
    FOREIGN KEY (cust_id) REFERENCES customers(cust_id),
    FOREIGN KEY (sind_id) REFERENCES sinderellas(sind_id),
    FOREIGN KEY (service_id) REFERENCES service_pricing(service_id)
);

-- BOOKING ADDON TABLE
DROP TABLE IF EXISTS booking_addons;
CREATE TABLE IF NOT EXISTS booking_addons (
    booking_addon_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    ao_id INT(11) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (ao_id) REFERENCES addon(ao_id)
);

  -- BOOKING PAYMENTS TABLE
DROP TABLE IF EXISTS booking_payments;
CREATE TABLE IF NOT EXISTS booking_payments (
    payment_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    bill_code VARCHAR(20) NOT NULL,
    payment_amount DECIMAL(10, 2) NOT NULL,
    payment_status VARCHAR(20) NOT NULL,  -- Stores 'paid', 'unpaid', 'refunded'
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);






-- EVENT TO CLEAN UP SINDERELLA OLD SCEDULE ***TBC
CREATE EVENT cleanup_old_schedule
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM sind_available_time
  WHERE available_date < CURDATE() - INTERVAL 2 MONTH;