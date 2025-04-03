-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password, email) 
VALUES ('admin', '$2y$10$IW8BXt56Owz5Yh5OVAtlE.Y2.v/FjOoRWvfxl4YDmJI9cpDVQbNyW', 'admin@cryptofund.example.com')
ON CONFLICT (username) DO NOTHING;

-- We don't need to recreate the campaigns table as it already exists in the database
-- The existing campaigns table has: id, user_id, funding_goal, start_date, end_date, 
-- created_at, updated_at, thumbnail_url, contract_address, title, description, category, status, token_symbol

-- Just for reference, here's how the campaigns table is structured:
-- CREATE TABLE IF NOT EXISTS campaigns (
--     id SERIAL PRIMARY KEY,
--     user_id INTEGER DEFAULT NULL,
--     title VARCHAR(255) NOT NULL,
--     description TEXT NOT NULL,
--     category VARCHAR(50) NOT NULL,
--     funding_goal DECIMAL(18,8) NOT NULL,
--     token_symbol VARCHAR(10) DEFAULT 'ETH',
--     thumbnail_url VARCHAR(255) DEFAULT NULL,
--     contract_address VARCHAR(42) DEFAULT NULL,
--     start_date TIMESTAMP NOT NULL,
--     end_date TIMESTAMP NOT NULL,
--     status VARCHAR(20) NOT NULL DEFAULT 'pending',
--     created_at TIMESTAMP NOT NULL,
--     updated_at TIMESTAMP NOT NULL
-- );

-- Create index on status for faster filtering
CREATE INDEX IF NOT EXISTS campaigns_status_idx ON campaigns(status);