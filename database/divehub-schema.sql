
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `boats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `boats` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `tec_capacity` int DEFAULT NULL,
  `operatorId` int DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `pic` varchar(45) DEFAULT NULL,
  `manufacturer` varchar(45) DEFAULT NULL,
  `beam` float DEFAULT NULL,
  `length` float DEFAULT NULL,
  `speed` float DEFAULT NULL,
  `power` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `time` varchar(10) DEFAULT NULL,
  `operatorId` int DEFAULT NULL,
  `tripName` varchar(150) DEFAULT NULL,
  `booked` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `mailto` varchar(150) DEFAULT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `body` longtext,
  `mail_sent_on` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `read` tinyint DEFAULT '0',
  `send_on_email` varchar(45) DEFAULT NULL,
  `send_on_sms` varchar(45) DEFAULT NULL,
  `deleted` tinyint DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9841 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operators` (
  `id` int NOT NULL,
  `operatorName` varchar(100) DEFAULT NULL,
  `queryMaxDaySpan` int DEFAULT NULL,
  `queryCurrentIndex` int DEFAULT NULL,
  `locationArea` varchar(45) DEFAULT NULL,
  `location` varchar(45) DEFAULT NULL,
  `streetAddress` varchar(100) DEFAULT NULL,
  `cityAddress` varchar(60) DEFAULT NULL,
  `stateAddress` varchar(45) DEFAULT NULL,
  `zipAddress` varchar(45) DEFAULT NULL,
  `coutryAddress` varchar(45) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `webSite` varchar(100) DEFAULT NULL,
  `waiverLink` varchar(200) DEFAULT NULL,
  `onSiteFillAir` tinyint DEFAULT NULL,
  `onSiteFillNitrox` tinyint DEFAULT NULL,
  `onSiteFillTrimix` tinyint DEFAULT NULL,
  `onSiteFillO2` tinyint DEFAULT NULL,
  `marinaAddress` varchar(100) DEFAULT NULL,
  `marinaAddressAlt` varchar(100) DEFAULT NULL,
  `logoUrl` varchar(200) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `_lastUpdate` datetime DEFAULT NULL,
  `_status` varchar(45) DEFAULT NULL,
  `_cron` varchar(45) DEFAULT NULL,
  `_updatedCount` varchar(45) DEFAULT NULL,
  `mapUrl` varchar(400) DEFAULT NULL,
  `hourOfOperation` varchar(400) DEFAULT NULL,
  `tripPrice` varchar(400) DEFAULT NULL,
  `desc` varchar(4000) DEFAULT NULL,
  `_ver` varchar(45) DEFAULT NULL,
  `tec` tinyint DEFAULT '0',
  `private` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operatorId_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file` varchar(400) DEFAULT NULL,
  `desc` varchar(500) DEFAULT NULL,
  `credit` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `siteId` int DEFAULT NULL,
  `_token` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=894 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ProductName` varchar(50) NOT NULL,
  `Price` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sitecomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sitecomments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userid` int DEFAULT NULL,
  `siteid` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `_token` varchar(100) DEFAULT NULL,
  `comment` longtext,
  `likes` int DEFAULT '0',
  `childof` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `siteratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siteratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `siteId` int DEFAULT NULL,
  `starRating` int DEFAULT NULL,
  `comment` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `aka` varchar(200) DEFAULT NULL,
  `avgDepth` int DEFAULT NULL,
  `maxDepth` int DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `tag` varchar(45) DEFAULT NULL,
  `desc` text,
  `route` text,
  `pics` varchar(1000) DEFAULT NULL,
  `videos` varchar(1000) DEFAULT NULL,
  `externalLink` varchar(200) DEFAULT NULL,
  `level` int DEFAULT NULL,
  `visitingOperators` varchar(100) DEFAULT NULL,
  `typicalConditions` text,
  `access` varchar(45) DEFAULT NULL,
  `history` text,
  `rate` float DEFAULT NULL,
  `votes` int DEFAULT NULL,
  `relief` int DEFAULT NULL,
  `wreckData` varchar(400) DEFAULT NULL,
  `location` varchar(45) DEFAULT NULL,
  `gpsLat` varchar(45) DEFAULT NULL,
  `gpsLon` varchar(45) DEFAULT NULL,
  `_token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `historicImg` varchar(200) DEFAULT NULL,
  `dModel` varchar(300) DEFAULT NULL,
  `dModelCredit` varchar(45) DEFAULT NULL,
  `distance_from_shore` int DEFAULT NULL,
  `_hidden` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=416 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trips` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `tags` varchar(100) DEFAULT NULL,
  `operatorId` int DEFAULT NULL,
  `operatorName` varchar(100) DEFAULT NULL,
  `tripName` varchar(100) DEFAULT NULL,
  `boatId` varchar(45) DEFAULT NULL,
  `boatName` varchar(45) DEFAULT NULL,
  `linkToBook` varchar(400) DEFAULT NULL,
  `tripFreeSpots` varchar(45) DEFAULT NULL,
  `departureTime` varchar(10) DEFAULT NULL,
  `checkInTime` varchar(10) DEFAULT NULL,
  `tripPrice` int DEFAULT NULL,
  `tripType` varchar(100) DEFAULT NULL,
  `_dateAdded` datetime DEFAULT NULL,
  `siteId` varchar(45) DEFAULT NULL,
  `siteIdStatus` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13119121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `visitedsites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitedsites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `siteId` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1836 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `weatherday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `weatherday` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` varchar(45) DEFAULT NULL,
  `location` varchar(45) DEFAULT NULL,
  `maxtemp_f` float DEFAULT NULL,
  `mintemp_f` float DEFAULT NULL,
  `tides` varchar(1000) DEFAULT NULL,
  `conditions_text` varchar(45) DEFAULT NULL,
  `conditions_icon` varchar(150) DEFAULT NULL,
  `sunrise` varchar(45) DEFAULT NULL,
  `sunset` varchar(45) DEFAULT NULL,
  `maxwind_mph` float DEFAULT NULL,
  `avghumidity` float DEFAULT NULL,
  `avgvis_miles` float DEFAULT NULL,
  `totalprecip_in` float DEFAULT NULL,
  `_dateAdded` datetime DEFAULT NULL,
  `conditionsAM_text` varchar(45) DEFAULT NULL,
  `conditionsPM_text` varchar(45) DEFAULT NULL,
  `conditionsAM_score` float DEFAULT NULL,
  `conditionsPM_score` float DEFAULT NULL,
  `wind_speed_AM` float DEFAULT NULL,
  `wind_speed_PM` float DEFAULT NULL,
  `swell_period_AM` float DEFAULT NULL,
  `swell_period_PM` float DEFAULT NULL,
  `wind_dir_AM` varchar(5) DEFAULT NULL,
  `wind_dir_PM` varchar(5) DEFAULT NULL,
  `swell_height_AM` float DEFAULT NULL,
  `swell_height_PM` varchar(45) DEFAULT NULL,
  `water_temp_AM` float DEFAULT NULL,
  `water_temp_PM` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3735300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `weatherhour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `weatherhour` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` varchar(45) DEFAULT NULL,
  `time` varchar(45) DEFAULT NULL,
  `location` varchar(45) DEFAULT NULL,
  `temp_f` float DEFAULT NULL,
  `condition_text` varchar(45) DEFAULT NULL,
  `condition_icon` varchar(150) DEFAULT NULL,
  `wind_mph` float DEFAULT NULL,
  `wind_degree` int DEFAULT NULL,
  `wind_dir` varchar(10) DEFAULT NULL,
  `pressure_mb` float DEFAULT NULL,
  `precip_in` float DEFAULT NULL,
  `humidity` float DEFAULT NULL,
  `feelslike_f` float DEFAULT NULL,
  `windchill_f` float DEFAULT NULL,
  `heatindex_f` float DEFAULT NULL,
  `dewpoint_f` float DEFAULT NULL,
  `vis_miles` float DEFAULT NULL,
  `gust_mph` float DEFAULT NULL,
  `uv` float DEFAULT NULL,
  `swell_ht_ft` float DEFAULT NULL,
  `swell_dir` float DEFAULT NULL,
  `swell_dir_16_point` varchar(10) DEFAULT NULL,
  `swell_period_secs` float DEFAULT NULL,
  `water_temp_f` float DEFAULT NULL,
  `_dateAdded` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89681026 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `weatherlocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `weatherlocations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` varchar(45) DEFAULT NULL,
  `short` varchar(45) DEFAULT NULL,
  `country` varchar(3) DEFAULT NULL,
  `centerLat` double DEFAULT NULL,
  `centerLon` double DEFAULT NULL,
  `_status` int DEFAULT NULL,
  `_lastUpdated` datetime DEFAULT NULL,
  `buoy` varchar(10) DEFAULT NULL,
  `dir` int DEFAULT NULL,
  `speed` float DEFAULT NULL,
  `dir4p` varchar(4) DEFAULT NULL,
  `updatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wishedsites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishedsites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `siteId` int DEFAULT NULL,
  `notified_email` tinyint DEFAULT NULL,
  `notified_sms` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `next_trip` datetime DEFAULT NULL,
  `next_trip_operatorId` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

