-- Create Users table.
CREATE TABLE IF NOT EXISTS Users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- bcrypt output via PHP's password_hash(); embeds its own per-user salt
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id) -- Primary Key for Users table
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Tanks table.
CREATE TABLE IF NOT EXISTS Tanks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    custom_name VARCHAR(100) NOT NULL,
    tank_type VARCHAR(50), -- e.g. freshwater, saltwater, planted, shrimp
    salinity_type VARCHAR(50), -- e.g. freshwater, saltwater, brackish
    tank_shape VARCHAR(50),
    volume_gallons DECIMAL(8,2),
    has_filter BOOLEAN NOT NULL DEFAULT FALSE,
    filter_type VARCHAR(100),
    has_co2 BOOLEAN NOT NULL DEFAULT FALSE,
    co2_type VARCHAR(100),
    has_fertilizer BOOLEAN NOT NULL DEFAULT FALSE,
    fertilizer_routine VARCHAR(255),
    has_lighting BOOLEAN NOT NULL DEFAULT FALSE,
    lighting_type VARCHAR(100),
    lighting_schedule VARCHAR(100),
    lighting_intensity VARCHAR(50),
    has_substrate BOOLEAN NOT NULL DEFAULT FALSE,
    substrate_type VARCHAR(100),
    has_heater BOOLEAN NOT NULL DEFAULT FALSE,
    heater_type VARCHAR(100),
    cycle_start_date DATE,
    is_cycled BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), -- Primary Key for Tanks table
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE -- Foreign Key to reference Users table for id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
