-- ================================================
-- SQL Query untuk Vercel Postgres
-- Membuat table settings dan insert default values
-- ================================================

-- 1. CREATE TABLE settings
CREATE TABLE IF NOT EXISTS settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 2. INSERT default target values
INSERT INTO settings (key, value, description, created_at, updated_at)
VALUES 
    ('target_nasi', '120', 'Target jumlah nasi per hari', NOW(), NOW()),
    ('target_snack', '200', 'Target jumlah snack per hari', NOW(), NOW())
ON CONFLICT (key) DO NOTHING;

-- 3. Verify data (optional - untuk checking)
-- SELECT * FROM settings;
