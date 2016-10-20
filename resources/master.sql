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
INSERT INTO `mtb_order_status` VALUES (0,'入力中',0),(1,'新規受付',91),(2,'入金待ち',1),(3,'キャンセル',2),(4,'取り寄せ中',4),(5,'発送済み',9),(6,'入金済み',3),(7,'決済処理中',92),(8,'購入処理中',93),(11,'入稿データ確認中',5),(12,'入稿データ確認済',6),(20,'印刷中',7),(21,'発送準備中',8);
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
INSERT INTO `mtb_customer_order_status` VALUES (0,'入力中',0),(1,'注文受付',91),(2,'入金待ち',1),(3,'キャンセル',2),(4,'注文受付',4),(5,'発送済み',9),(6,'注文受付',3),(7,'注文未完了',92),(8,'注文未完了',93),(11,'入稿データ確認中',5),(12,'印刷中',6),(20,'印刷中',7),(21,'発送準備中',8);
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


--
-- 暫定処置
--
INSERT INTO `mtb_nyukin_status` (`id`, `name`, `rank`) VALUES (0, '入金有', 0);
INSERT INTO `mtb_nyukin_status` (`id`, `name`, `rank`) VALUES (1, '入金無', 1);


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-10-17 15:31:02
