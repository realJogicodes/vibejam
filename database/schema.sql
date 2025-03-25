-- Drop existing tables if they exist
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS jury_members;
DROP TABLE IF EXISTS sponsors;

-- Create jury_members table
CREATE TABLE jury_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create sponsors table
CREATE TABLE sponsors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create submissions table with enhanced fields
CREATE TABLE submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    creator TEXT NOT NULL,
    description TEXT,
    category TEXT CHECK (category IN ('Death match', 'FPS', 'Platformer', 'Real Time Strategy', 'Simulator', 'Other')),
    screenshot_url TEXT,
    game_url TEXT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ai_code_percentage INTEGER CHECK (ai_code_percentage >= 0 AND ai_code_percentage <= 100),
    engine_used TEXT,
    is_multiplayer BOOLEAN DEFAULT 0,
    domain_url TEXT,
    loading_time_ms INTEGER,
    username_required BOOLEAN DEFAULT 0,
    has_jam_badge BOOLEAN DEFAULT 0
);

-- Create indexes for better query performance
CREATE INDEX idx_submissions_title ON submissions(title);
CREATE INDEX idx_submissions_creator ON submissions(creator);
CREATE INDEX idx_submissions_category ON submissions(category);
CREATE INDEX idx_submissions_submission_date ON submissions(submission_date);

-- Insert jury members
INSERT INTO jury_members (username) VALUES
    ('@karpathy'),
    ('@timsoret'),
    ('@mrdoob'),
    ('@s13k_'),
    ('@levelsio');

-- Insert sponsors
INSERT INTO sponsors (username) VALUES
    ('@boltdotnew'),
    ('@coderabbitai');

-- Create submission validation trigger
CREATE TRIGGER validate_submission BEFORE INSERT ON submissions
BEGIN
    SELECT
        CASE
            WHEN NEW.ai_code_percentage < 80 THEN
                RAISE (ABORT, 'AI code percentage must be at least 80%')
            WHEN NEW.loading_time_ms IS NOT NULL AND NEW.loading_time_ms > 5000 THEN
                RAISE (ABORT, 'Loading time must be under 5 seconds')
        END;
END;