-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Jul 2018 um 16:53
-- Server-Version: 10.1.31-MariaDB
-- PHP-Version: 7.2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `moco_to_gitlabdb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbl_gitlab_token`
--

CREATE TABLE `tbl_gitlab_token` (
  `id` int(11) NOT NULL,
  `gitlab_token` varchar(100) CHARACTER SET utf8 NOT NULL,
  `gitlab_email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `gitlab_user_name` varchar(100) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tbl_gitlab_token`
--

INSERT INTO `tbl_gitlab_token` (`id`, `gitlab_token`, `gitlab_email`, `gitlab_user_name`) VALUES
(1, 'Vb23WYp2KmxvPG4xVRhB', 'chrispitzner@hotmail.com', 'ChrisP2147');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tickets_check`
--

CREATE TABLE `tickets_check` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `title` varchar(250) CHARACTER SET utf8 NOT NULL,
  `transferred` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tickets_check`
--

INSERT INTO `tickets_check` (`id`, `ticket_id`, `title`, `transferred`) VALUES
(206, 967166, 'Artikel in Web-Shop einpflegen -', 0),
(207, 977870, 'Beratung', 0),
(208, 1011559, 'Bildbearbeitung', 0),
(209, 967164, 'Design CSS erstellen -', 0),
(210, 967990, 'Dokumentation erstellen -', 0),
(211, 977872, 'Flasch Animationen', 0),
(212, 977871, 'Logo Design', 0),
(213, 967194, 'Optional Bildbearbeitung der einzelnen Artikel', 0),
(214, 1011562, 'Software-Einrichtung vor Ort inclusive Schulung fÃ¼rs Personal', 0),
(215, 977873, 'URL-Design', 0),
(216, 977874, 'URL-Redirects', 0),
(217, 1011560, 'Videoclip fÃ¼r Webshop', 0),
(218, 967391, 'CSS erstellen', 0),
(219, 967390, 'Design', 0),
(220, 967393, 'Dokumentation -', 0),
(221, 977978, 'Logo Design', 0),
(222, 967392, 'Programm entwickeln', 0),
(223, 967391, 'CSS erstellen', 0),
(224, 967390, 'Design', 0),
(225, 977872, 'Flasch Animationen', 0),
(226, 967391, 'CSS erstellen', 0),
(227, 967391, 'CSS erstellen', 0),
(228, 967391, 'CSS erstellen', 0),
(229, 967391, 'CSS erstellen', 0),
(230, 967391, 'CSS erstellen', 0),
(231, 967391, 'CSS erstellen', 0),
(232, 967391, 'CSS erstellen', 0),
(233, 967391, 'CSS erstellen', 0),
(234, 967391, 'CSS erstellen', 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `tbl_gitlab_token`
--
ALTER TABLE `tbl_gitlab_token`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `tickets_check`
--
ALTER TABLE `tickets_check`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `tbl_gitlab_token`
--
ALTER TABLE `tbl_gitlab_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `tickets_check`
--
ALTER TABLE `tickets_check`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
