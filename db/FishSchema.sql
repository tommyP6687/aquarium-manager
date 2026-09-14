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

-- Create Species table. Shared reference data, found-or-created when an organism is added.
CREATE TABLE IF NOT EXISTS Species (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    scientific_name VARCHAR(150) NOT NULL UNIQUE,
    common_name VARCHAR(150),
    organism_type VARCHAR(50), -- e.g. Fish, Invertebrate, Plant, Coral
    salinity_type VARCHAR(50), -- e.g. freshwater, saltwater, brackish
    external_taxon_id INT UNSIGNED, -- iNaturalist taxon id, if found via species search
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id) -- Primary Key for Species table
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create PixelArt table. A user's personal sprite bank; not tied to one organism or species.
CREATE TABLE IF NOT EXISTS PixelArt (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    sprite_name VARCHAR(100) NOT NULL,
    grid_size TINYINT UNSIGNED NOT NULL,
    pixel_data JSON NOT NULL, -- grid of hex color strings
    source_type VARCHAR(20) NOT NULL, -- 'photo_generated' for now; 'hand_drawn' reserved for a future editor
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), -- Primary Key for PixelArt table
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE -- Foreign Key to reference Users table for id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Organism table.
CREATE TABLE IF NOT EXISTS Organism (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    tank_id INT UNSIGNED NOT NULL,
    species_id INT UNSIGNED NOT NULL,
    pixel_art_id INT UNSIGNED NOT NULL,
    custom_name VARCHAR(100) NOT NULL,
    date_added DATE NOT NULL,
    health_status VARCHAR(50) NOT NULL DEFAULT 'Healthy',
    growth_stage VARCHAR(50),
    current_size_inches DECIMAL(6,2),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), -- Primary Key for Organism table
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE, -- Foreign Key to reference Users table for id
    FOREIGN KEY (tank_id) REFERENCES Tanks(id) ON DELETE CASCADE, -- Foreign Key to reference Tanks table for id
    FOREIGN KEY (species_id) REFERENCES Species(id), -- Foreign Key to reference Species table for id
    FOREIGN KEY (pixel_art_id) REFERENCES PixelArt(id) -- Foreign Key to reference PixelArt table for id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create WaterTest table. One row per individual parameter reading.
CREATE TABLE IF NOT EXISTS WaterTest (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    tank_id INT UNSIGNED NOT NULL,
    tested_at DATETIME NOT NULL,
    parameter_name VARCHAR(50) NOT NULL, -- e.g. pH, Ammonia, Temperature, Salinity
    value DECIMAL(10,4) NOT NULL,
    unit VARCHAR(20), -- e.g. ppm, dKH, ppt
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), -- Primary Key for WaterTest table
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE, -- Foreign Key to reference Users table for id
    FOREIGN KEY (tank_id) REFERENCES Tanks(id) ON DELETE CASCADE -- Foreign Key to reference Tanks table for id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create MaintenanceLog table. One row per completed care task.
CREATE TABLE IF NOT EXISTS MaintenanceLog (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    tank_id INT UNSIGNED NOT NULL,
    performed_at DATETIME NOT NULL,
    task_type VARCHAR(50) NOT NULL, -- e.g. Feeding, Water Change, Filter Cleaning
    details TEXT,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), -- Primary Key for MaintenanceLog table
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE, -- Foreign Key to reference Users table for id
    FOREIGN KEY (tank_id) REFERENCES Tanks(id) ON DELETE CASCADE -- Foreign Key to reference Tanks table for id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Reminder table.
CREATE TABLE IF NOT EXISTS Reminder (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    tank_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    task_type VARCHAR(50) NOT NULL, -- e.g. Feed fish, Test water, Clean filter
    due_at DATETIME NOT NULL,
    repeat_interval_days INT UNSIGNED, -- null = one-time reminder
    is_completed BOOLEAN NOT NULL DEFAULT FALSE,
    completed_at DATETIME,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id), -- Primary Key for Reminder table
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE, -- Foreign Key to reference Users table for id
    FOREIGN KEY (tank_id) REFERENCES Tanks(id) ON DELETE CASCADE -- Foreign Key to reference Tanks table for id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
