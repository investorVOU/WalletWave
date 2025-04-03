-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `cryptofund` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cryptofund`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User wallet addresses for registered users
CREATE TABLE IF NOT EXISTS `user_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address` varchar(42) NOT NULL, -- Ethereum address (0x + 40 hex chars)
  `is_connected` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `address` (`address`),
  CONSTRAINT `user_wallets_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guest wallet addresses for non-registered users (session-based)
CREATE TABLE IF NOT EXISTS `guest_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(42) NOT NULL, -- Ethereum address (0x + 40 hex chars)
  `session_id` varchar(64) NOT NULL,
  `is_connected` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  UNIQUE KEY `address` (`address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaigns table
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL, -- Can be NULL for guest users
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `funding_goal` decimal(18,8) NOT NULL, -- Support for large amounts with 8 decimal places
  `token_symbol` varchar(10) NOT NULL DEFAULT 'ETH', -- ETH, USDT, etc.
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `contract_address` varchar(42) DEFAULT NULL, -- Ethereum contract address if deployed
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('draft','active','completed','expired','cancelled') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `campaigns_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign contributions table
CREATE TABLE IF NOT EXISTS `contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL, -- Can be NULL for guest users
  `wallet_address` varchar(42) NOT NULL,
  `amount` decimal(18,8) NOT NULL,
  `transaction_hash` varchar(66) NOT NULL, -- Ethereum transaction hash
  `status` enum('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `contributions_campaign_id_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contributions_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallet connection logs
CREATE TABLE IF NOT EXISTS `wallet_connection_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL, -- Can be NULL for guest users
  `wallet_address` varchar(42) NOT NULL,
  `session_id` varchar(64) NOT NULL,
  `ip_address` varchar(45) NOT NULL, -- IPv6 compatible
  `user_agent` text,
  `action` enum('connect','disconnect') NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wallet_logs_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for faster searching
CREATE INDEX idx_guest_wallets_address ON guest_wallets(address);
CREATE INDEX idx_user_wallets_address ON user_wallets(address);
CREATE INDEX idx_campaigns_status ON campaigns(status);
CREATE INDEX idx_campaigns_category ON campaigns(category);
CREATE INDEX idx_contributions_wallet ON contributions(wallet_address);
