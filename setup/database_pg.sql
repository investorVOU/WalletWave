-- Create tables for CryptoFund platform
-- PostgreSQL version

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  is_verified BOOLEAN DEFAULT FALSE,
  verification_token VARCHAR(64) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  CONSTRAINT users_email_unique UNIQUE (email),
  CONSTRAINT users_username_unique UNIQUE (username)
);

-- User wallet addresses for registered users
CREATE TABLE IF NOT EXISTS user_wallets (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  address VARCHAR(42) NOT NULL, -- Ethereum address (0x + 40 hex chars)
  is_connected BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  CONSTRAINT user_wallets_user_id_unique UNIQUE (user_id),
  CONSTRAINT user_wallets_address_unique UNIQUE (address),
  CONSTRAINT user_wallets_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Guest wallet addresses for non-registered users (session-based)
CREATE TABLE IF NOT EXISTS guest_wallets (
  id SERIAL PRIMARY KEY,
  address VARCHAR(42) NOT NULL, -- Ethereum address (0x + 40 hex chars)
  session_id VARCHAR(64) NOT NULL,
  is_connected BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  CONSTRAINT guest_wallets_session_id_unique UNIQUE (session_id),
  CONSTRAINT guest_wallets_address_unique UNIQUE (address)
);

-- Campaigns table
CREATE TABLE IF NOT EXISTS campaigns (
  id SERIAL PRIMARY KEY,
  user_id INTEGER DEFAULT NULL, -- Can be NULL for guest users
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  funding_goal DECIMAL(18,8) NOT NULL, -- Support for large amounts with 8 decimal places
  token_symbol VARCHAR(10) NOT NULL DEFAULT 'ETH', -- ETH, USDT, etc.
  thumbnail_url VARCHAR(255) DEFAULT NULL,
  contract_address VARCHAR(42) DEFAULT NULL, -- Ethereum contract address if deployed
  start_date TIMESTAMP NOT NULL,
  end_date TIMESTAMP NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft', -- draft, active, completed, expired, cancelled
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  CONSTRAINT campaigns_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- Campaign contributions table
CREATE TABLE IF NOT EXISTS contributions (
  id SERIAL PRIMARY KEY,
  campaign_id INTEGER NOT NULL,
  user_id INTEGER DEFAULT NULL, -- Can be NULL for guest users
  wallet_address VARCHAR(42) NOT NULL,
  amount DECIMAL(18,8) NOT NULL,
  transaction_hash VARCHAR(66) NOT NULL, -- Ethereum transaction hash
  status VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending, confirmed, failed
  created_at TIMESTAMP NOT NULL,
  CONSTRAINT contributions_campaign_id_fk FOREIGN KEY (campaign_id) REFERENCES campaigns (id) ON DELETE CASCADE,
  CONSTRAINT contributions_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- Wallet connection logs
CREATE TABLE IF NOT EXISTS wallet_connection_logs (
  id SERIAL PRIMARY KEY,
  user_id INTEGER DEFAULT NULL, -- Can be NULL for guest users
  wallet_address VARCHAR(42) NOT NULL,
  session_id VARCHAR(64) NOT NULL,
  ip_address VARCHAR(45) NOT NULL, -- IPv6 compatible
  user_agent TEXT,
  action VARCHAR(20) NOT NULL, -- connect, disconnect
  created_at TIMESTAMP NOT NULL,
  CONSTRAINT wallet_logs_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- Create indexes for faster searching
CREATE INDEX idx_guest_wallets_address ON guest_wallets(address);
CREATE INDEX idx_user_wallets_address ON user_wallets(address);
CREATE INDEX idx_campaigns_status ON campaigns(status);
CREATE INDEX idx_campaigns_category ON campaigns(category);
CREATE INDEX idx_contributions_wallet ON contributions(wallet_address);