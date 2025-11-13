-- SQL schema for AuthBoard
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example seed
INSERT INTO users (name, email, password) VALUES
('Demo User', 'demo@example.com', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8r2bK5QkqZ1s6lG6a7Y5Qx1p1Kf1y'); -- password hash placeholder
