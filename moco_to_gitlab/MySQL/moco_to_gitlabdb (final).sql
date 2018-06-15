-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 15. Jun 2018 um 13:10
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
  `gitlab_email` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `tbl_gitlab_token`
--

INSERT INTO `tbl_gitlab_token` (`id`, `gitlab_token`, `gitlab_email`) VALUES
(1, 'Vb23WYp2KmxvPG4xVRhB', 'chrispitzner@hotmail.com'),
(2, '12345', 'peter.hansen@gal-digital.de'),
(3, '6789', 'michelle.mueller@gal-digital.de');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `tickets_check`
--
ALTER TABLE `tickets_check`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
