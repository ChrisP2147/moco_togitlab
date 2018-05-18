-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 18. Mai 2018 um 08:34
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
-- Tabellenstruktur für Tabelle `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `moco_token` varchar(45) NOT NULL,
  `gitlab_token` varchar(45) NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(80) NOT NULL,
  `firstname` varchar(45) NOT NULL,
  `lastname` varchar(45) NOT NULL,
  `admin` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `staff`
--

INSERT INTO `staff` (`id`, `active`, `moco_token`, `gitlab_token`, `username`, `password`, `firstname`, `lastname`, `admin`) VALUES
(1, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'admin', '$2y$10$h6ePqy//nLIoXbWPLsslHuUbmVyHj8Xbb9CDIrjgMNyGesOszEn9m', 'Admin', 'Admin', 1),
(2, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'chrisP', '$2y$10$TmvdmLjBCy1auyUrPR0oHOJ1F6LLGzHCuzmtA3fqwYByyD5tV.T22', 'Christian', 'Pitzner', 0),
(3, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'justiB', '$2y$10$rVb6iIMjL4Q8IvUb4jRbOeBEHFAYrGyx0oguMkMHkBZDNPBJphxBy', 'Bieber', 'Justin', 0),
(4, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'ladyG', '$2y$10$1/nn45J9atoo0DLnwx6L0uxEQAIzWDH322495sod2z/65T2RS0Gcq', 'Gaggason', 'Lady', 0),
(5, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'charlyM', '$2y$10$buLLZTbkCD0Wra1Avsu0Huh.ttcD70wbwpSt5hfgNuYFPTwn/KKti', 'Manson', 'Charles', 0),
(6, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'karleB', '$2y$10$TLyRVKRX0UpjyUX7CpGKS.BNblNmAkrk2m07Me9Wms0Xt8OapbNFO', 'Bosse', 'Karle', 1),
(8, 1, '53a856de73a8b8b0a82aa7a604026747', 'Vb23WYp2KmxvPG4xVRhB', 'hagenB', '$2y$10$8OSR/0OHGVjfNlV4fkVyUO65VnxsTEZyzq51euZMvtGyDMisYz.sm', 'Hagen', 'Berger', 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
