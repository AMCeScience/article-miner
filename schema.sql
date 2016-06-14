CREATE SCHEMA `miner_alchemy`;
use `miner_alchemy`;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `Alchemy_concepts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(150) NOT NULL,
  `relevance` decimal(7,6) NOT NULL,
  `outcome` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `concepts_to_outcome_idx` (`outcome`),
  CONSTRAINT `concepts_to_outcome` FOREIGN KEY (`outcome`) REFERENCES `Outcomes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Alchemy_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(150) NOT NULL,
  `relevance` decimal(7,6) NOT NULL,
  `count` int(4) NOT NULL,
  `text` varchar(150) NOT NULL,
  `outcome` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entities_to_outcome_idx` (`outcome`),
  CONSTRAINT `entities_to_outcome` FOREIGN KEY (`outcome`) REFERENCES `Outcomes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Alchemy_keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(150) NOT NULL,
  `relevance` decimal(7,6) NOT NULL,
  `outcome` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyword_to_outcome_idx` (`outcome`),
  CONSTRAINT `keyword_to_outcome` FOREIGN KEY (`outcome`) REFERENCES `Outcomes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Alchemy_taxonomy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(250) NOT NULL,
  `score` decimal(7,6) NOT NULL,
  `confident` varchar(3) DEFAULT NULL,
  `outcome` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `taxonomy_to_outcome_idx` (`outcome`),
  CONSTRAINT `taxonomy_to_outcome` FOREIGN KEY (`outcome`) REFERENCES `Outcomes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Alchemy_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transactions_used` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Outcomes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article` int(11) NOT NULL,
  `keywords_done` tinyint(1) NOT NULL DEFAULT '0',
  `concepts_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '\n',
  `taxonomy_done` tinyint(1) NOT NULL DEFAULT '0',
  `entities_done` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `article_idx` (`article`),
  CONSTRAINT `article` FOREIGN KEY (`article`) REFERENCES `Articles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
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

SET FOREIGN_KEY_CHECKS = 1;