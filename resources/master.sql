-- MySQL dump 10.13  Distrib 5.6.22, for osx10.10 (x86_64)
--
-- Host: localhost    Database: eccube3_database
-- ------------------------------------------------------
-- Server version	5.6.22

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mtb_order_status`
--

DROP TABLE IF EXISTS `mtb_order_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtb_order_status` (
  `id` smallint(6) NOT NULL,
  `name` longtext,
  `rank` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mtb_order_status`
--

LOCK TABLES `mtb_order_status` WRITE;
/*!40000 ALTER TABLE `mtb_order_status` DISABLE KEYS */;
INSERT INTO `mtb_order_status` VALUES (0,'入力中',0),(1,'注文受付',1),(3,'キャンセル',2),(4,'取り寄せ中',3),(5,'発送済み',8),(11,'入稿データ確認中',4),(12,'入稿データ確認済',5),(20,'印刷中',6),(21,'発送準備中',7);
/*!40000 ALTER TABLE `mtb_order_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mtb_customer_order_status`
--

DROP TABLE IF EXISTS `mtb_customer_order_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtb_customer_order_status` (
  `id` smallint(6) NOT NULL,
  `name` longtext,
  `rank` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mtb_customer_order_status`
--

LOCK TABLES `mtb_customer_order_status` WRITE;
/*!40000 ALTER TABLE `mtb_customer_order_status` DISABLE KEYS */;
INSERT INTO `mtb_customer_order_status` VALUES (0,'入力中',0),(1,'注文受付',1),(2,'入金待ち',99),(3,'キャンセル',3),(4,'取り寄せ中',3),(5,'発送済み',8),(6,'注文受付',2),(7,'注文未完了',99),(8,'注文未完了',99),(11,'入稿データ確認中',4),(12,'印刷中',5),(20,'印刷中',6),(21,'発送準備中',7);
/*!40000 ALTER TABLE `mtb_customer_order_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mtb_product_type`
--

DROP TABLE IF EXISTS `mtb_product_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtb_product_type` (
  `id` smallint(6) NOT NULL,
  `name` longtext,
  `rank` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mtb_product_type`
--

LOCK TABLES `mtb_product_type` WRITE;
/*!40000 ALTER TABLE `mtb_product_type` DISABLE KEYS */;
INSERT INTO `mtb_product_type` VALUES (1,'既製品',0),(2,'印刷商品',1);
/*!40000 ALTER TABLE `mtb_product_type` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-10-14 12:08:18
