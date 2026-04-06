-- ================================================================
--  GameDB — MySQL Schema for Laragon / InfinityFree
--
--  HOW TO RUN IN LARAGON:
--  1. Open HeidiSQL → connect to Laragon.MySQL
--  2. Right-click → Create New → Database → name it "gamedb"
--  3. Click on "gamedb" in the left panel
--  4. Query menu → New Query → paste this → press F9
-- ================================================================

    CREATE TABLE IF NOT EXISTS `games` (
        `id`            INT(11)         NOT NULL AUTO_INCREMENT,
        `title`         VARCHAR(200)    NOT NULL,               -- Field 1: Game title
        `genre`         VARCHAR(80)     NOT NULL,               -- Field 2: Genre
        `developer`     VARCHAR(150)    NOT NULL,               -- Field 3: Developer studio
        `price`         DECIMAL(8,2)    NOT NULL DEFAULT 0.00,  -- Field 4: Price in USD
        `release_date`  DATE            NOT NULL,               -- Field 5: Release date
        `platform`      VARCHAR(100)    NOT NULL,               -- Field 6: Platform(s)
        `rating`        TINYINT         NOT NULL DEFAULT 0      -- Field 7: Rating out of 10
                            CHECK (`rating` >= 0 AND `rating` <= 10),
        `description`   TEXT            DEFAULT NULL,           -- Field 8: Short description
        `cover_path`    VARCHAR(255)    DEFAULT NULL,           -- Cover image path
        `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- Sample data
-- ----------------------------------------------------------------
INSERT INTO `games` (`title`, `genre`, `developer`, `price`, `release_date`, `platform`, `rating`, `description`) VALUES
('Cyberpunk 2077',      'RPG',              'CD Projekt Red',       29.99,  '2020-12-10', 'PC, PS5, Xbox',    9,  'An open-world RPG set in the dystopian Night City.'),
('Elden Ring',          'Action RPG',       'FromSoftware',         59.99,  '2022-02-25', 'PC, PS5, Xbox',    10, 'A vast open-world action RPG with punishing combat.'),
('Hollow Knight',       'Metroidvania',     'Team Cherry',          14.99,  '2017-02-24', 'PC, Switch, PS4',  10, 'A challenging hand-drawn adventure in a vast underground kingdom.'),
('Hades',               'Roguelite',        'Supergiant Games',     24.99,  '2020-09-17', 'PC, Switch',       10, 'Battle out of the Underworld in this rogue-like dungeon crawler.'),
('Stardew Valley',      'Simulation',       'ConcernedApe',          14.99,  '2016-02-26', 'PC, Switch, PS4',  9,  'Build the farm of your dreams in this relaxing RPG.');
