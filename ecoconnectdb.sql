-- Admin Insert Password Hash Manually Query (Generated using adminpass.php)
-- http://localhost/eco_connect/adminpass.php
INSERT INTO users (username, password, location, role) 
VALUES ('Admin', 'PASTE_THE_GENERATED_HASH_HERE', 'Puchong', 'admin');


-- Note: User passwords all end with 123. Example, the username is "Ammarul", the password is "Ammarul123".



CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) DEFAULT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    full_name VARCHAR(100),
    phone_number VARCHAR(20),

    location VARCHAR(100),

    profile_image VARCHAR(255) DEFAULT 'default.png',

    role ENUM('user', 'admin') DEFAULT 'user',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE items (

    item_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    item_name VARCHAR(100) NOT NULL,

    item_description TEXT,

    item_image VARCHAR(255) DEFAULT 'default.png',

    location VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
);

ALTER TABLE items 
ADD COLUMN category ENUM('Electronics', 'Furniture', 'Clothing', 'Sports', 'Books', 'Others') 
DEFAULT 'Others' 
AFTER item_description;

CREATE TABLE IF NOT EXISTS items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,

  user_id INT NOT NULL,

  item_name VARCHAR(255) NOT NULL,
  item_description TEXT NOT NULL,
  item_price DECIMAL(10,2) DEFAULT 0.00,
  is_free TINYINT(1) DEFAULT 1,

  item_image VARCHAR(255) NOT NULL,

  status ENUM('available', 'sold') DEFAULT 'available',
  location VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) 
  REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    context_item VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    related_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_wishlist(user_id, item_id),

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    deal_type ENUM('purchase', 'donation') NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    meeting_location VARCHAR(255) NOT NULL,
    payment_method VARCHAR(100) NOT NULL,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE messages ADD COLUMN order_id INT DEFAULT NULL AFTER context_item;

ALTER TABLE orders ADD COLUMN bank_name VARCHAR(100) DEFAULT NULL AFTER payment_method;

CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE
);

--Old Report Database
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` INT AUTO_INCREMENT PRIMARY KEY,
  `reporter_id` INT NOT NULL,
  `reported_user_id` INT DEFAULT NULL,
  `reported_item_id` INT DEFAULT NULL,
  `report_type` ENUM('user', 'item') NOT NULL,
  `reason` text NOT NULL,
  `status` ENUM('pending', 'resolved') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `users` 
ADD COLUMN `status` ENUM('Active', 'Warned', 'Banned') DEFAULT 'Active',
ADD COLUMN `warning_message` VARCHAR(255) DEFAULT NULL;