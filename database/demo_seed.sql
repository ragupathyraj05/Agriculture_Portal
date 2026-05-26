-- ============================================
-- Agriculture Portal — Demo Seed Data
-- ============================================
-- Run this AFTER schema.sql
-- Password for ALL demo users: Demo@123
-- Hashed with PHP password_hash('Demo@123', PASSWORD_DEFAULT)
-- ============================================

USE agriculture_portal;

-- Clean existing demo data (in correct FK order)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM orders;
DELETE FROM crops;
DELETE FROM customers;
DELETE FROM farmers;
ALTER TABLE orders AUTO_INCREMENT = 1;
ALTER TABLE crops AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 1;
ALTER TABLE farmers AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. FARMERS (18 farmers across Tamil Nadu)
-- ============================================
INSERT INTO farmers (name, mobile, email, address, district, state, farm_size, main_crops, password) VALUES
('Rajesh Kumar',     '9876543201', 'rajesh.kumar@farm.in',     'Kallanai Road, Thanjavur',          'Thanjavur',    'Tamil Nadu', 8.50,  'Rice, Sugarcane',        '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Murugan S',        '9876543202', 'murugan.s@farm.in',        'Srirangam, Trichy',                 'Trichy',       'Tamil Nadu', 6.00,  'Paddy, Groundnut',       '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Lakshmi Devi',     '9876543203', 'lakshmi.d@farm.in',        'Pollachi Road, Coimbatore',         'Coimbatore',   'Tamil Nadu', 12.00, 'Coconut, Banana',        '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Senthil Vel',      '9876543204', 'senthil.v@farm.in',        'Attur Road, Salem',                 'Salem',        'Tamil Nadu', 5.00,  'Tomato, Onion',          '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Kamatchi P',       '9876543205', 'kamatchi.p@farm.in',       'Bhavani Road, Erode',               'Erode',        'Tamil Nadu', 10.00, 'Turmeric, Cotton',       '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Vel Murugan',      '9876543206', 'vel.murugan@farm.in',      'Usilampatti, Madurai',              'Madurai',      'Tamil Nadu', 7.50,  'Chilli, Brinjal',        '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Anbu Selvi',       '9876543207', 'anbu.selvi@farm.in',       'Kumbakonam, Thanjavur',             'Thanjavur',    'Tamil Nadu', 15.00, 'Rice, Sugarcane',        '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Karthik R',        '9876543208', 'karthik.r@farm.in',        'Mettur Dam Road, Salem',            'Salem',        'Tamil Nadu', 4.00,  'Maize, Ragi',            '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Ponni M',          '9876543209', 'ponni.m@farm.in',          'Gobichettipalayam, Erode',          'Erode',        'Tamil Nadu', 9.00,  'Ginger, Turmeric',       '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Durai Raj',        '9876543210', 'durai.raj@farm.in',        'Sivagangai Road, Sivagangai',       'Sivagangai',   'Tamil Nadu', 6.50,  'Millets, Wheat',         '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Meena K',          '9876543211', 'meena.k@farm.in',          'Palani Road, Dindigul',             'Dindigul',     'Tamil Nadu', 3.50,  'Drumstick, Carrot',      '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Ravi Shankar',     '9876543212', 'ravi.shankar@farm.in',     'Tirunelveli Town',                  'Tirunelveli',  'Tamil Nadu', 11.00, 'Banana, Rice',           '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Selvi T',          '9876543213', 'selvi.t@farm.in',          'Nagapattinam Road',                 'Nagapattinam', 'Tamil Nadu', 7.00,  'Paddy, Sugarcane',       '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Gokul N',          '9876543214', 'gokul.n@farm.in',          'Karur Town',                        'Karur',        'Tamil Nadu', 5.50,  'Cotton, Maize',          '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Bhuvana S',        '9876543215', 'bhuvana.s@farm.in',        'Namakkal Town',                     'Namakkal',     'Tamil Nadu', 8.00,  'Potato, Cabbage',        '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Arjun V',          '9876543216', 'arjun.v@farm.in',          'Theni Town',                        'Theni',        'Tamil Nadu', 13.00, 'Cardamom, Coriander',    '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Sumathi R',        '9876543217', 'sumathi.r@farm.in',        'Virudhunagar Town',                 'Virudhunagar', 'Tamil Nadu', 4.50,  'Groundnut, Chilli',      '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Manikandan P',     '9876543218', 'manikandan.p@farm.in',     'Ramanathapuram Town',               'Ramanathapuram','Tamil Nadu', 6.00, 'Rice, Millets',          '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC');

-- ============================================
-- 2. CUSTOMERS (25 customers)
-- ============================================
INSERT INTO customers (name, mobile, email, address, city, state, pincode, password) VALUES
('Priya Sharma',      '9123456701', 'priya.sharma@mail.in',     '12 Anna Nagar, Chennai',            'Chennai',      'Tamil Nadu', '600040', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Deepak M',          '9123456702', 'deepak.m@mail.in',         '45 T Nagar, Chennai',               'Chennai',      'Tamil Nadu', '600017', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Sathya N',          '9123456703', 'sathya.n@mail.in',         '78 RS Puram, Coimbatore',           'Coimbatore',   'Tamil Nadu', '641002', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Arun Kumar',        '9123456704', 'arun.kumar@mail.in',       '23 KK Nagar, Madurai',              'Madurai',      'Tamil Nadu', '625020', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Divya R',           '9123456705', 'divya.r@mail.in',          '56 Gandhi Road, Trichy',            'Trichy',       'Tamil Nadu', '620001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Naveen S',          '9123456706', 'naveen.s@mail.in',         '89 Sathy Road, Erode',              'Erode',        'Tamil Nadu', '638003', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Kavitha L',         '9123456707', 'kavitha.l@mail.in',        '34 New Street, Salem',              'Salem',        'Tamil Nadu', '636007', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Surya P',           '9123456708', 'surya.p@mail.in',          '67 Beach Road, Pondicherry',        'Pondicherry',  'Tamil Nadu', '605001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Revathi K',         '9123456709', 'revathi.k@mail.in',        '12 Main Road, Thanjavur',           'Thanjavur',    'Tamil Nadu', '613001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Vignesh D',         '9123456710', 'vignesh.d@mail.in',        '45 South Street, Tirunelveli',      'Tirunelveli',  'Tamil Nadu', '627001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Janani M',          '9123456711', 'janani.m@mail.in',         '78 West Car Street, Madurai',       'Madurai',      'Tamil Nadu', '625001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Prasad V',          '9123456712', 'prasad.v@mail.in',         '23 Bazaar Street, Dindigul',        'Dindigul',     'Tamil Nadu', '624001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Sangeetha R',       '9123456713', 'sangeetha.r@mail.in',      '56 Lake Area, Chennai',             'Chennai',      'Tamil Nadu', '600034', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Karthikeyan B',     '9123456714', 'karthikeyan.b@mail.in',    '89 Peelamedu, Coimbatore',          'Coimbatore',   'Tamil Nadu', '641004', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Nithya S',          '9123456715', 'nithya.s@mail.in',         '34 East Street, Salem',             'Salem',        'Tamil Nadu', '636004', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Balaji T',          '9123456716', 'balaji.t@mail.in',         '67 North Street, Trichy',           'Trichy',       'Tamil Nadu', '620002', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Anitha P',          '9123456717', 'anitha.p@mail.in',         '12 New Colony, Karur',              'Karur',        'Tamil Nadu', '639001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Sivakumar G',       '9123456718', 'sivakumar.g@mail.in',      '45 Station Road, Nagapattinam',     'Nagapattinam', 'Tamil Nadu', '611001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Latha V',           '9123456719', 'latha.v@mail.in',          '78 Temple Street, Kumbakonam',      'Thanjavur',    'Tamil Nadu', '612001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Mohan R',           '9123456720', 'mohan.r@mail.in',          '23 Big Bazaar Street, Theni',       'Theni',        'Tamil Nadu', '625531', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Vidhya K',          '9123456721', 'vidhya.k@mail.in',         '56 Palayam, Coimbatore',            'Coimbatore',   'Tamil Nadu', '641014', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Ganesh M',          '9123456722', 'ganesh.m@mail.in',         '89 VOC Street, Madurai',            'Madurai',      'Tamil Nadu', '625002', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Padma S',           '9123456723', 'padma.s@mail.in',          '12 Colony Road, Virudhunagar',      'Virudhunagar', 'Tamil Nadu', '626001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Ashok D',           '9123456724', 'ashok.d@mail.in',          '34 South Car Street, Chennai',      'Chennai',      'Tamil Nadu', '600079', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC'),
('Renuka T',          '9123456725', 'renuka.t@mail.in',         '67 Nehru Street, Erode',            'Erode',        'Tamil Nadu', '638001', '$2y$10$ik7huC/TqC8RyCTiVzOmSu9oeoLQR9NcRpwp9AcLXD5hM/PPKlALC');

-- ============================================
-- 3. CROPS (30 crops across farmers)
-- ============================================
INSERT INTO crops (farmer_id, crop_name, quantity, price_per_kg, harvest_date, description, image, status) VALUES
(1,  'Rice',         800.00,  42.00, '2026-01-15', 'Premium Sona Masuri rice from Thanjavur delta',          'rice.svg', 'available'),
(1,  'Sugarcane',    2000.00, 8.00,  '2026-01-20', 'Fresh sugarcane from irrigated fields',                  'sugarcane.svg',    'available'),
(2,  'Rice',         600.00,  40.00, '2026-02-01', 'Ponni rice variety grown using traditional methods',     'rice.svg', 'available'),
(2,  'Groundnut',    300.00,  85.00, '2026-01-25', 'Organic groundnut, sun-dried and sorted',                'groundnut.svg',    'available'),
(3,  'Banana',       500.00,  30.00, '2026-02-05', 'Nendran banana, premium export quality',                 'banana.svg',       'available'),
(4,  'Tomato',       400.00,  25.00, '2026-02-10', 'Hybrid tomato, firm and fresh from Salem farms',         'tomato.svg',       'available'),
(4,  'Onion',        350.00,  35.00, '2026-02-08', 'Red onion, perfect for cooking',                         'onion.svg',        'available'),
(5,  'Turmeric',     200.00, 120.00, '2026-01-18', 'Erode turmeric, high curcumin content',                  'turmeric.svg',     'available'),
(5,  'Cotton',       1000.00, 55.00, '2026-01-10', 'Long staple cotton, premium grade',                      'cotton.svg',       'available'),
(6,  'Chilli',       250.00,  90.00, '2026-02-12', 'Guntur variety red chilli from Madurai region',          'chilli.svg',       'available'),
(6,  'Brinjal',      180.00,  28.00, '2026-02-14', 'Purple brinjal, pesticide-free',                         'brinjal.svg',      'available'),
(7,  'Rice',         1200.00, 44.00, '2026-01-28', 'Kuruva rice from fertile Kumbakonam fields',             'rice.svg', 'available'),
(8,  'Maize',        500.00,  22.00, '2026-02-03', 'Yellow maize, ideal for poultry feed and flour',         'maize.svg',        'available'),
(8,  'Ragi',         300.00,  48.00, '2026-01-30', 'Finger millet (Ragi), high in calcium',                  'ragi.svg', 'available'),
(9,  'Ginger',       150.00, 140.00, '2026-02-06', 'Fresh ginger root from Erode district',                  'ginger.svg',       'available'),
(10, 'Millets',      400.00,  55.00, '2026-01-22', 'Mixed millets — barnyard, foxtail and little millet',    'millets.svg',      'available'),
(10, 'Wheat',        350.00,  32.00, '2026-02-09', 'Whole wheat grain, freshly harvested',                   'wheat.svg',        'available'),
(11, 'Drumstick',    200.00,  45.00, '2026-02-15', 'Fresh moringa drumstick, organically grown',             'drumstick.svg',    'available'),
(11, 'Carrot',       250.00,  38.00, '2026-02-11', 'Orange carrots from cool Palani hills',                  'carrot.svg',       'available'),
(12, 'Banana',       700.00,  32.00, '2026-01-27', 'Robusta banana, table-ready ripened',                    'banana.svg',       'available'),
(13, 'Rice',         900.00,  41.00, '2026-02-02', 'Nagapattinam delta paddy rice',                          'rice.svg', 'available'),
(14, 'Cotton',       800.00,  52.00, '2026-01-12', 'Medium staple cotton from Karur',                        'cotton.svg',       'available'),
(14, 'Maize',        450.00,  20.00, '2026-02-04', 'Sweet corn variety maize',                               'maize.svg',        'available'),
(15, 'Potato',       500.00,  30.00, '2026-02-07', 'Table potato, sorted and graded',                        'potato.svg',       'available'),
(15, 'Cabbage',      300.00,  18.00, '2026-02-13', 'Green cabbage, fresh from Namakkal farms',               'cabbage.svg',      'available'),
(16, 'Coriander',    100.00,  95.00, '2026-02-16', 'Fresh coriander leaves bundle',                          'coriander.svg',    'available'),
(17, 'Groundnut',    250.00,  82.00, '2026-01-24', 'Bold kernel groundnut from Virudhunagar',                'groundnut.svg',    'available'),
(17, 'Chilli',       200.00,  88.00, '2026-02-17', 'Dried red chilli, perfect spice grade',                   'chilli.svg',       'available'),
(18, 'Rice',         500.00,  43.00, '2026-02-18', 'Traditional Mappillai Samba rice',                        'rice.svg', 'available'),
(16, 'Sugarcane',    1500.00, 9.00,  '2026-01-14', 'Sweet sugarcane for juice and jaggery',                   'sugarcane.svg',    'available');

-- ============================================
-- 4. ORDERS (75 delivered COD orders)
-- ============================================
INSERT INTO orders (customer_id, farmer_id, crop_id, quantity, total_amount, order_date, payment_status, order_status, delivery_address, payment_method, delivered_at) VALUES
-- Batch 1: Jan orders
(1,  1,  1,  25.00,  1050.00, '2026-01-16 09:30:00', 'Confirmed (COD Collected)', 'Delivered', '12 Anna Nagar, Chennai',          'COD', '2026-01-19 14:00:00'),
(2,  1,  2,  50.00,   400.00, '2026-01-21 10:15:00', 'Confirmed (COD Collected)', 'Delivered', '45 T Nagar, Chennai',             'COD', '2026-01-24 11:30:00'),
(3,  2,  3,  30.00,  1200.00, '2026-02-02 08:45:00', 'Confirmed (COD Collected)', 'Delivered', '78 RS Puram, Coimbatore',         'COD', '2026-02-05 16:00:00'),
(4,  2,  4,  10.00,   850.00, '2026-01-26 14:20:00', 'Confirmed (COD Collected)', 'Delivered', '23 KK Nagar, Madurai',            'COD', '2026-01-29 10:45:00'),
(5,  3,  5,  20.00,   600.00, '2026-02-06 11:00:00', 'Confirmed (COD Collected)', 'Delivered', '56 Gandhi Road, Trichy',          'COD', '2026-02-09 15:30:00'),
(6,  4,  6,  15.00,   375.00, '2026-02-11 09:00:00', 'Confirmed (COD Collected)', 'Delivered', '89 Sathy Road, Erode',            'COD', '2026-02-14 12:00:00'),
(7,  4,  7,  20.00,   700.00, '2026-02-09 13:30:00', 'Confirmed (COD Collected)', 'Delivered', '34 New Street, Salem',            'COD', '2026-02-12 09:15:00'),
(8,  5,  8,   5.00,   600.00, '2026-01-19 10:45:00', 'Confirmed (COD Collected)', 'Delivered', '67 Beach Road, Pondicherry',      'COD', '2026-01-22 14:30:00'),
(9,  5,  9,  40.00,  2200.00, '2026-01-11 08:30:00', 'Confirmed (COD Collected)', 'Delivered', '12 Main Road, Thanjavur',         'COD', '2026-01-14 11:00:00'),
(10, 6,  10,  8.00,   720.00, '2026-02-13 15:00:00', 'Confirmed (COD Collected)', 'Delivered', '45 South Street, Tirunelveli',    'COD', '2026-02-16 10:30:00'),

-- Batch 2
(11, 6,  11, 12.00,   336.00, '2026-02-15 09:20:00', 'Confirmed (COD Collected)', 'Delivered', '78 West Car Street, Madurai',     'COD', '2026-02-18 13:00:00'),
(12, 7,  12, 50.00,  2200.00, '2026-01-29 10:00:00', 'Confirmed (COD Collected)', 'Delivered', '23 Bazaar Street, Dindigul',      'COD', '2026-02-01 15:45:00'),
(13, 8,  13, 30.00,   660.00, '2026-02-04 14:30:00', 'Confirmed (COD Collected)', 'Delivered', '56 Lake Area, Chennai',           'COD', '2026-02-07 09:30:00'),
(14, 8,  14, 15.00,   720.00, '2026-01-31 11:15:00', 'Confirmed (COD Collected)', 'Delivered', '89 Peelamedu, Coimbatore',        'COD', '2026-02-03 16:00:00'),
(15, 9,  15,  3.00,   420.00, '2026-02-07 08:00:00', 'Confirmed (COD Collected)', 'Delivered', '34 East Street, Salem',           'COD', '2026-02-10 12:15:00'),
(16, 10, 16, 20.00,  1100.00, '2026-01-23 13:45:00', 'Confirmed (COD Collected)', 'Delivered', '67 North Street, Trichy',         'COD', '2026-01-26 10:00:00'),
(17, 10, 17, 25.00,   800.00, '2026-02-10 09:30:00', 'Confirmed (COD Collected)', 'Delivered', '12 New Colony, Karur',            'COD', '2026-02-13 14:45:00'),
(18, 11, 18, 10.00,   450.00, '2026-02-16 10:00:00', 'Confirmed (COD Collected)', 'Delivered', '45 Station Road, Nagapattinam',   'COD', '2026-02-19 11:30:00'),
(19, 11, 19, 15.00,   570.00, '2026-02-12 14:15:00', 'Confirmed (COD Collected)', 'Delivered', '78 Temple Street, Kumbakonam',    'COD', '2026-02-15 09:00:00'),
(20, 12, 20, 25.00,   800.00, '2026-01-28 11:30:00', 'Confirmed (COD Collected)', 'Delivered', '23 Big Bazaar Street, Theni',     'COD', '2026-01-31 16:15:00'),

-- Batch 3
(21, 13, 21, 40.00,  1640.00, '2026-02-03 09:00:00', 'Confirmed (COD Collected)', 'Delivered', '56 Palayam, Coimbatore',          'COD', '2026-02-06 13:30:00'),
(22, 14, 22, 35.00,  1820.00, '2026-01-13 10:30:00', 'Confirmed (COD Collected)', 'Delivered', '89 VOC Street, Madurai',          'COD', '2026-01-16 15:00:00'),
(23, 14, 23, 20.00,   400.00, '2026-02-05 14:00:00', 'Confirmed (COD Collected)', 'Delivered', '12 Colony Road, Virudhunagar',    'COD', '2026-02-08 10:30:00'),
(24, 15, 24, 30.00,   900.00, '2026-02-08 08:45:00', 'Confirmed (COD Collected)', 'Delivered', '34 South Car Street, Chennai',    'COD', '2026-02-11 14:00:00'),
(25, 15, 25, 20.00,   360.00, '2026-02-14 13:00:00', 'Confirmed (COD Collected)', 'Delivered', '67 Nehru Street, Erode',          'COD', '2026-02-17 09:45:00'),

-- Batch 4: More diverse orders
(1,  16, 26,  5.00,   475.00, '2026-02-17 10:30:00', 'Confirmed (COD Collected)', 'Delivered', '12 Anna Nagar, Chennai',          'COD', '2026-02-20 14:00:00'),
(2,  17, 27, 15.00,  1230.00, '2026-01-25 09:15:00', 'Confirmed (COD Collected)', 'Delivered', '45 T Nagar, Chennai',             'COD', '2026-01-28 11:30:00'),
(3,  17, 28, 10.00,   880.00, '2026-02-18 14:45:00', 'Confirmed (COD Collected)', 'Delivered', '78 RS Puram, Coimbatore',         'COD', '2026-02-21 10:00:00'),
(4,  18, 29, 20.00,   860.00, '2026-02-19 11:00:00', 'Confirmed (COD Collected)', 'Delivered', '23 KK Nagar, Madurai',            'COD', '2026-02-22 15:30:00'),
(5,  16, 30, 60.00,   540.00, '2026-01-15 08:30:00', 'Confirmed (COD Collected)', 'Delivered', '56 Gandhi Road, Trichy',          'COD', '2026-01-18 12:00:00'),

-- Batch 5: Repeat customers
(1,  3,  5,  15.00,   450.00, '2026-02-08 09:00:00', 'Confirmed (COD Collected)', 'Delivered', '12 Anna Nagar, Chennai',          'COD', '2026-02-11 13:30:00'),
(2,  4,  6,  10.00,   250.00, '2026-02-13 10:30:00', 'Confirmed (COD Collected)', 'Delivered', '45 T Nagar, Chennai',             'COD', '2026-02-16 14:00:00'),
(3,  5,  8,   3.00,   360.00, '2026-02-02 14:00:00', 'Confirmed (COD Collected)', 'Delivered', '78 RS Puram, Coimbatore',         'COD', '2026-02-05 10:15:00'),
(6,  7,  12, 20.00,   880.00, '2026-02-01 11:30:00', 'Confirmed (COD Collected)', 'Delivered', '89 Sathy Road, Erode',            'COD', '2026-02-04 15:45:00'),
(7,  9,  15,  2.00,   280.00, '2026-02-09 08:15:00', 'Confirmed (COD Collected)', 'Delivered', '34 New Street, Salem',            'COD', '2026-02-12 12:30:00'),

-- Batch 6
(8,  10, 16, 10.00,   550.00, '2026-01-24 13:00:00', 'Confirmed (COD Collected)', 'Delivered', '67 Beach Road, Pondicherry',      'COD', '2026-01-27 09:30:00'),
(9,  11, 18, 12.00,   540.00, '2026-02-17 10:45:00', 'Confirmed (COD Collected)', 'Delivered', '12 Main Road, Thanjavur',         'COD', '2026-02-20 14:15:00'),
(10, 12, 20, 15.00,   480.00, '2026-01-29 09:30:00', 'Confirmed (COD Collected)', 'Delivered', '45 South Street, Tirunelveli',    'COD', '2026-01-31 11:00:00'),
(11, 1,  1,  20.00,   840.00, '2026-01-17 14:00:00', 'Confirmed (COD Collected)', 'Delivered', '78 West Car Street, Madurai',     'COD', '2026-01-20 10:30:00'),
(12, 2,  4,  8.00,    680.00, '2026-01-27 11:15:00', 'Confirmed (COD Collected)', 'Delivered', '23 Bazaar Street, Dindigul',      'COD', '2026-01-30 15:00:00'),

-- Batch 7
(13, 3,  5,  30.00,   900.00, '2026-02-07 08:30:00', 'Confirmed (COD Collected)', 'Delivered', '56 Lake Area, Chennai',           'COD', '2026-02-10 12:45:00'),
(14, 4,  7,  10.00,   350.00, '2026-02-10 13:45:00', 'Confirmed (COD Collected)', 'Delivered', '89 Peelamedu, Coimbatore',        'COD', '2026-02-13 09:30:00'),
(15, 6,  10,  6.00,   540.00, '2026-02-14 09:00:00', 'Confirmed (COD Collected)', 'Delivered', '34 East Street, Salem',           'COD', '2026-02-17 13:15:00'),
(16, 8,  13, 15.00,   330.00, '2026-02-05 10:30:00', 'Confirmed (COD Collected)', 'Delivered', '67 North Street, Trichy',         'COD', '2026-02-08 14:45:00'),
(17, 9,  15,  4.00,   560.00, '2026-02-08 14:15:00', 'Confirmed (COD Collected)', 'Delivered', '12 New Colony, Karur',            'COD', '2026-02-11 10:00:00'),

-- Batch 8
(18, 11, 19,  8.00,   304.00, '2026-02-13 11:00:00', 'Confirmed (COD Collected)', 'Delivered', '45 Station Road, Nagapattinam',   'COD', '2026-02-16 15:30:00'),
(19, 13, 21, 30.00,  1230.00, '2026-02-04 09:45:00', 'Confirmed (COD Collected)', 'Delivered', '78 Temple Street, Kumbakonam',    'COD', '2026-02-07 13:00:00'),
(20, 14, 22, 20.00,  1040.00, '2026-01-14 14:30:00', 'Confirmed (COD Collected)', 'Delivered', '23 Big Bazaar Street, Theni',     'COD', '2026-01-17 10:15:00'),
(21, 15, 24, 25.00,   750.00, '2026-02-09 08:00:00', 'Confirmed (COD Collected)', 'Delivered', '56 Palayam, Coimbatore',          'COD', '2026-02-12 12:30:00'),
(22, 16, 26,  3.00,   285.00, '2026-02-18 13:30:00', 'Confirmed (COD Collected)', 'Delivered', '89 VOC Street, Madurai',          'COD', '2026-02-21 09:45:00'),

-- Batch 9
(23, 17, 27, 10.00,   820.00, '2026-01-26 10:00:00', 'Confirmed (COD Collected)', 'Delivered', '12 Colony Road, Virudhunagar',    'COD', '2026-01-29 14:30:00'),
(24, 18, 29, 15.00,   645.00, '2026-02-20 11:30:00', 'Confirmed (COD Collected)', 'Delivered', '34 South Car Street, Chennai',    'COD', '2026-02-23 09:00:00'),
(25, 1,  2,  30.00,   240.00, '2026-01-22 09:15:00', 'Confirmed (COD Collected)', 'Delivered', '67 Nehru Street, Erode',          'COD', '2026-01-25 13:45:00'),
(1,  2,  3,  40.00,  1600.00, '2026-02-03 14:00:00', 'Confirmed (COD Collected)', 'Delivered', '12 Anna Nagar, Chennai',          'COD', '2026-02-06 10:30:00'),
(2,  6,  10,  5.00,   450.00, '2026-02-14 08:30:00', 'Confirmed (COD Collected)', 'Delivered', '45 T Nagar, Chennai',             'COD', '2026-02-17 12:00:00'),

-- Batch 10
(3,  7,  12, 35.00,  1540.00, '2026-01-30 10:45:00', 'Confirmed (COD Collected)', 'Delivered', '78 RS Puram, Coimbatore',         'COD', '2026-02-02 14:30:00'),
(4,  8,  14, 20.00,   960.00, '2026-02-01 13:00:00', 'Confirmed (COD Collected)', 'Delivered', '23 KK Nagar, Madurai',            'COD', '2026-02-04 09:15:00'),
(5,  10, 17, 15.00,   480.00, '2026-02-11 09:30:00', 'Confirmed (COD Collected)', 'Delivered', '56 Gandhi Road, Trichy',          'COD', '2026-02-14 13:45:00'),
(6,  11, 18, 10.00,   450.00, '2026-02-16 14:15:00', 'Confirmed (COD Collected)', 'Delivered', '89 Sathy Road, Erode',            'COD', '2026-02-19 10:30:00'),
(7,  12, 20, 20.00,   640.00, '2026-01-28 11:00:00', 'Confirmed (COD Collected)', 'Delivered', '34 New Street, Salem',            'COD', '2026-01-31 15:15:00'),

-- Batch 11
(8,  13, 21, 25.00,  1025.00, '2026-02-04 08:45:00', 'Confirmed (COD Collected)', 'Delivered', '67 Beach Road, Pondicherry',      'COD', '2026-02-07 12:00:00'),
(9,  14, 23, 15.00,   300.00, '2026-02-06 13:30:00', 'Confirmed (COD Collected)', 'Delivered', '12 Main Road, Thanjavur',         'COD', '2026-02-09 09:45:00'),
(10, 15, 25, 25.00,   450.00, '2026-02-15 10:00:00', 'Confirmed (COD Collected)', 'Delivered', '45 South Street, Tirunelveli',    'COD', '2026-02-18 14:30:00'),
(11, 16, 30, 40.00,   360.00, '2026-01-16 14:30:00', 'Confirmed (COD Collected)', 'Delivered', '78 West Car Street, Madurai',     'COD', '2026-01-19 10:00:00'),
(12, 17, 28, 12.00,  1056.00, '2026-02-19 09:15:00', 'Confirmed (COD Collected)', 'Delivered', '23 Bazaar Street, Dindigul',      'COD', '2026-02-22 13:30:00'),

-- Batch 12
(13, 18, 29, 10.00,   430.00, '2026-02-20 11:45:00', 'Confirmed (COD Collected)', 'Delivered', '56 Lake Area, Chennai',           'COD', '2026-02-23 08:00:00'),
(14, 1,  1,  15.00,   630.00, '2026-01-18 10:00:00', 'Confirmed (COD Collected)', 'Delivered', '89 Peelamedu, Coimbatore',        'COD', '2026-01-21 14:15:00'),
(15, 3,  5,  18.00,   540.00, '2026-02-10 08:30:00', 'Confirmed (COD Collected)', 'Delivered', '34 East Street, Salem',           'COD', '2026-02-13 12:45:00'),
(16, 5,  9,  25.00,  1375.00, '2026-01-12 13:00:00', 'Confirmed (COD Collected)', 'Delivered', '67 North Street, Trichy',         'COD', '2026-01-15 09:30:00'),
(17, 7,  12, 30.00,  1320.00, '2026-02-01 14:45:00', 'Confirmed (COD Collected)', 'Delivered', '12 New Colony, Karur',            'COD', '2026-02-04 10:00:00');

-- ============================================
-- End of Demo Seed Data
-- ============================================
