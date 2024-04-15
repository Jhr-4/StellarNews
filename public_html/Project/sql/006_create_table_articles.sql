CREATE TABLE IF NOT EXISTS  `ArticlesTable`
(
    `id`         INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    `api_id` INT NOT NULL UNIQUE,
    `api_timestamp` TIMESTAMP NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `site_url` VARCHAR(2048) NOT NULL,
    `image_url` VARCHAR(2048) NOT NULL,
    `news_text` TEXT NOT NULL,
    `news_summary_long` TEXT NOT NULL,
    `created`    TIMESTAMP DEFAULT current_timestamp,
    `modified`   TIMESTAMP DEFAULT current_timestamp ON UPDATE current_timestamp
)