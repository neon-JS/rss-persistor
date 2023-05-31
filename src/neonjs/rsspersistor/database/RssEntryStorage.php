<?php

declare(strict_types=1);

namespace neonjs\rsspersistor\database;

use \Exception;
use \PDO;

readonly class RssEntryStorage
{
    private const ENV_KEY_DSN = 'dsn';
    private const ENV_KEY_USER = 'user';
    private const ENV_KEY_PASSWORD = 'password';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private PDO $pdo;

    public function __construct()
    {
        $dsn = $_ENV[self::ENV_KEY_DSN] ?? null ?: throw new Exception('No DSN configured');
        $user = $_ENV[self::ENV_KEY_USER] ?? null ?: throw new Exception('No user configured');
        $password = $_ENV[self::ENV_KEY_PASSWORD] ?? null ?: throw new Exception('No password configured');

        try {
            $this->pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (Exception) {
            /* Hide secrets! */
            throw new Exception('Could not connect to database');
        }
    }

    public function initializeTables(): void
    {
        $this->pdo->query("
            CREATE TABLE IF NOT EXISTS `entry` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `guid` VARCHAR(255) NOT NULL UNIQUE,
                `link` VARCHAR(255) NULL,
                `publicationDate` DATETIME NULL,
                `category` VARCHAR(255) NULL,
                `relevant` TINYINT(1) DEFAULT 0 NOT NULL
            )
            DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`
        ");

        $this->pdo->query("
            CREATE TABLE IF NOT EXISTS `word` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `word` VARCHAR(255) NOT NULL UNIQUE,
                `relevant` TINYINT(1) DEFAULT 0 NOT NULL
            )
            DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`
        ");

        $this->pdo->query("
            CREATE TABLE IF NOT EXISTS `titleWord` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `entryId` INT UNSIGNED NOT NULL,
                `wordId` INT UNSIGNED NOT NULL,
                CONSTRAINT FOREIGN KEY (`entryId`)
                    REFERENCES `entry` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT FOREIGN KEY (`wordId`)
                    REFERENCES `word` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                UNIQUE KEY (`entryId`, `wordId`)
            )
            DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`
        ");
    }

    public function store(RssEntry $rssEntry, array $titleWords): void
    {
        $insertEntry = $this->pdo->prepare("
            INSERT INTO `entry`
                (`title`, `guid`, `link`, `publicationDate`, `category`)
            SELECT
                :title, :guid, :link, :publicationDate, :category
            WHERE NOT EXISTS (
                SELECT *
                FROM `entry`
                WHERE `guid` = :guid
            )   
        ");

        $insertWord = $this->pdo->prepare("
            INSERT INTO `word` (`word`)
            SELECT :word
            WHERE NOT EXISTS (
                SELECT *
                FROM `word`
                WHERE `word` = :word
            )
        ");

        $referenceWord = $this->pdo->prepare("
            INSERT INTO `titleWord`
                (`entryId`, `wordId`)
            SELECT
                `entry`.`id`, `word`.`id`
            FROM `word`
            
            INNER JOIN `entry`
                ON `entry`.`guid` = :guid

            LEFT JOIN (SELECT * FROM `titleWord`) AS `existing`
                ON `existing`.`entryId` = `entry`.`id`
                AND `existing`.`wordId` = `word`.`id`

            WHERE
                `word`.`word` = :word
                AND `existing`.`id` IS NULL
        ");

        $insertEntry->execute([
            ':title' => $rssEntry->getTitle(),
            ':guid' => $rssEntry->getGuid(),
            ':link' => $rssEntry->getLink(),
            ':publicationDate' => $rssEntry->getPublicationDate()?->format(self::DATETIME_FORMAT),
            ':category' => $rssEntry->getCategory(),
        ]);

        foreach ($titleWords as $word) {
            $insertWord->execute([
                ':word' => $word,
            ]);

            $referenceWord->execute([
                ':word' => $word,
                ':guid' => $rssEntry->getGuid(),
            ]);
        }
    }
}
