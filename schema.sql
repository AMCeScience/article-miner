CREATE SCHEMA `miner`;
use miner;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `Pubmed_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pubmed_id` int(11) NOT NULL,
  `journal_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_idx` (`journal_id`),
  CONSTRAINT `journal_id` FOREIGN KEY (`journal_id`) REFERENCES `Journals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `title_stripped` varchar(300) NOT NULL,
  `abstract` varchar(5000) NOT NULL,
  `journal` int(11) NOT NULL,
  `day` varchar(2) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `doi` varchar(45) DEFAULT NULL,
  `search_db` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_idx` (`journal`),
  KEY `title_idx` (`title_stripped`(255)),
  KEY `doi_idx` (`doi`),
  KEY `db_idx` (`search_db`),
  CONSTRAINT `journal` FOREIGN KEY (`journal`) REFERENCES `Journals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Journal_definitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `iso` varchar(45) NOT NULL,
  `issn` varchar(45) NOT NULL,
  `category` varchar(150) NOT NULL,
  `category_type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Journals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `iso` varchar(100) DEFAULT NULL,
  `issn` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Journals_to_definitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal` int(11) NOT NULL,
  `definition` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `definition_idx` (`definition`),
  KEY `journal_idx` (`journal`),
  CONSTRAINT `definition` FOREIGN KEY (`definition`) REFERENCES `Journal_definitions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `journal_link` FOREIGN KEY (`journal`) REFERENCES `Journals` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Scripts_ran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `ran` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=4659 DEFAULT CHARSET=utf8;

CREATE TABLE `Keywords_to_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article` int(11) NOT NULL,
  `keyword` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyword_idx` (`keyword`),
  KEY `article_link` (`article`),
  CONSTRAINT `article_link` FOREIGN KEY (`article`) REFERENCES `Articles` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `keyword` FOREIGN KEY (`keyword`) REFERENCES `Keywords` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4659 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;