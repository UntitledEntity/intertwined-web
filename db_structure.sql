--
-- Table structure for table `application_users`
--

DROP TABLE IF EXISTS `application_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_users` (
  `username` varchar(70) DEFAULT NULL,
  `password` varchar(70) DEFAULT NULL,
  `expires` varchar(70) DEFAULT NULL,
  `hwid` varchar(70) DEFAULT '0',
  `banned` tinyint(1) DEFAULT NULL,
  `ip` varchar(70) DEFAULT NULL,
  `lastlogin` int(11) DEFAULT NULL,
  `level` int(1) DEFAULT NULL,
  `application` varchar(70) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blacklists`
--

DROP TABLE IF EXISTS `blacklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklists` (
  `ip` varchar(70) DEFAULT NULL,
  `hwid` varchar(70) DEFAULT NULL,
  `user` varchar(70) DEFAULT NULL,
  `application` varchar(70) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licenses` (
  `expires` varchar(70) DEFAULT NULL,
  `level` int(1) DEFAULT NULL,
  `applied` tinyint(1) DEFAULT NULL,
  `banned` int(1) DEFAULT 0,
  `ip` varchar(70) DEFAULT NULL,
  `hwid` varchar(70) DEFAULT NULL,
  `usedate` int(11) DEFAULT NULL,
  `lastlogin` int(12) DEFAULT NULL,
  `applieduser` varchar(70) DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `createdby` varchar(70) DEFAULT NULL,
  `license` varchar(70) DEFAULT NULL,
  `application` varchar(70) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sessionid` varchar(70) DEFAULT NULL,
  `ip` varchar(49) NOT NULL,
  `validated` int(1) DEFAULT 0,
  `opentime` int(11) DEFAULT NULL,
  `application` varchar(70) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_applications`
--

DROP TABLE IF EXISTS `user_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_applications` (
  `appid` varchar(70) DEFAULT NULL,
  `enckey` varchar(70) DEFAULT NULL,
  `owner` varchar(70) DEFAULT NULL,
  `enabled` int(1) DEFAULT 1,
  `iplock` int(1) DEFAULT 0,
  `hwidlock` int(1) DEFAULT 0,
  `authlock` int(1) DEFAULT 0,
  `hashcheck` int(1) DEFAULT 0,
  `hash` varchar(255) DEFAULT NULL,
  `version` varchar(5) DEFAULT NULL,
  `enabled_functions` varchar(6) DEFAULT '111111'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `username` varchar(70) NOT NULL,
  `password` varchar(70) NOT NULL,
  `email` varchar(70) NOT NULL, 
  `expires` varchar(70) NOT NULL DEFAULT '',
  `hwid` varchar(70) NOT NULL DEFAULT '0',
  `banned` tinyint(1) DEFAULT 0,
  `ip` varchar(49) DEFAULT NULL,
  `lastlogin` int(11) DEFAULT NULL,
  `level` int(1) DEFAULT NULL,
  `resetcode` varchar(32) DEFAULT NULL,
  `lastreset` TIME int(11) NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhooks` (
  `id` varchar(70) DEFAULT NULL,
  `link` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `variables`
--

DROP TABLE IF EXISTS `variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variables` (
  `id` varchar(70) DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `appid` varchar(70) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_limits` (
  `ip` varchar(70) NOT NULL,
  `request_count` varchar(100) DEFAULT NULL,
  `last_request` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;