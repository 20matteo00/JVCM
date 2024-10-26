-- Dump della struttura del database vcm_db
CREATE DATABASE IF NOT EXISTS `vcm_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `vcm_db`;

-- Dump della struttura di tabella vcm_db.vcmdb_competizioni
CREATE TABLE IF NOT EXISTS `vcmdb_competizioni` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `nome_competizione` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalita` int unsigned NOT NULL,
  `gironi` int unsigned NOT NULL,
  `andata_ritorno` tinyint unsigned NOT NULL,
  `partecipanti` int unsigned NOT NULL,
  `fase_finale` int unsigned NOT NULL,
  `finita` tinyint unsigned NOT NULL DEFAULT '0',
  `squadre` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
