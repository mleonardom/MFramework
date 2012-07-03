--
-- `mf_db` database
--

-- --------------------------------------------------------

--
-- Structure for the `users` table
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `level` enum('admin','user') NOT NULL DEFAULT 'user',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- `users` inserts
--

INSERT INTO `users` (`id`, `active`, `level`, `username`, `password`, `last_login`) VALUES
(1, 1, 'admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', NULL);