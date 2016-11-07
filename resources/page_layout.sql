-- MySQL dump 10.14  Distrib 5.5.50-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: eccube3_database
-- ------------------------------------------------------
-- Server version	5.5.50-MariaDB

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
-- Table structure for table `dtb_page_layout`
--

DROP TABLE IF EXISTS `dtb_page_layout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dtb_page_layout` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type_id` smallint(6) DEFAULT NULL,
  `page_name` longtext,
  `url` longtext NOT NULL,
  `file_name` longtext,
  `edit_flg` smallint(6) DEFAULT '1',
  `author` longtext,
  `description` longtext,
  `keyword` longtext,
  `update_url` longtext,
  `create_date` datetime NOT NULL,
  `update_date` datetime NOT NULL,
  `meta_robots` longtext,
  PRIMARY KEY (`page_id`),
  KEY `IDX_F27999414FFA550E` (`device_type_id`),
  CONSTRAINT `FK_F27999414FFA550E` FOREIGN KEY (`device_type_id`) REFERENCES `mtb_device_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dtb_page_layout`
--

LOCK TABLES `dtb_page_layout` WRITE;
/*!40000 ALTER TABLE `dtb_page_layout` DISABLE KEYS */;
INSERT INTO `dtb_page_layout` VALUES (0,10,'プレビューデータ','preview',NULL,1,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(1,10,'TOPページ','homepage','index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(2,10,'商品一覧ページ','product_list','Product/list',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(3,10,'商品詳細ページ','product_detail','Product/detail',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(4,10,'MYページ','mypage','Mypage/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(5,10,'MYページ/会員登録内容変更(入力ページ)','mypage_change','Mypage/change',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(6,10,'MYページ/会員登録内容変更(完了ページ)','mypage_change_complete','Mypage/change_complete',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(7,10,'MYページ/お届け先一覧','mypage_delivery','Mypage/delivery',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(8,10,'MYページ/お届け先追加','mypage_delivery_new','Mypage/delivery_edit',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(9,10,'MYページ/お気に入り一覧','mypage_favorite','Mypage/favorite',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(10,10,'MYページ/購入履歴詳細','mypage_history','Mypage/history',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(11,10,'MYページ/ログイン','mypage_login','Mypage/login',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(12,10,'MYページ/退会手続き(入力ページ)','mypage_withdraw','Mypage/withdraw',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(13,10,'MYページ/退会手続き(完了ページ)','mypage_withdraw_complete','Mypage/withdraw_complete',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(14,10,'当サイトについて','help_about','Help/about',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(15,10,'現在のカゴの中','cart','Cart/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(16,10,'お問い合わせ(入力ページ)','contact','Contact/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(17,10,'お問い合わせ(完了ページ)','contact_complete','Contact/complete',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(18,10,'会員登録(入力ページ)','entry','Entry/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(19,10,'ご利用規約','help_agreement','Help/agreement',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(20,10,'会員登録(完了ページ)','entry_complete','Entry/complete',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(21,10,'特定商取引に関する法律に基づく表記','help_tradelaw','Help/tradelaw',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(22,10,'本会員登録(完了ページ)','entry_activate','Entry/activate',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(23,10,'商品購入','shopping','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(24,10,'商品購入/お届け先の指定','shopping_shipping','Shopping/shipping',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(25,10,'商品購入/お届け先の複数指定','shopping_shipping_multiple','Shopping/shipping_multiple',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(28,10,'商品購入/ご注文完了','shopping_complete','Shopping/complete',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49','noindex'),(29,10,'プライバシーポリシー','help_privacy','Help/privacy',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(30,10,'商品購入ログイン','shopping_login','Shopping/login',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(31,10,'非会員購入情報入力','shopping_nonmember','Shopping/nonmember',2,NULL,NULL,NULL,NULL,'2016-08-01 17:25:49','2016-08-01 17:25:49',NULL),(32,10,'商品購入/お届け先の追加','shopping_shipping_edit','Shopping/shipping_edit',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:10','noindex'),(33,10,'商品購入/お届け先の複数指定(お届け先の追加)','shopping_shipping_multiple_edit','Shopping/shipping_multiple_edit',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:10','noindex'),(34,10,'商品購入/購入エラー','shopping_error','Shopping/shopping_error',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:10','noindex'),(35,10,'ご利用ガイド','help_guide','Help/guide',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:10',NULL),(36,10,'パスワード再発行(入力ページ)','forgot','Forgot/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:10',NULL),(37,10,'パスワード再発行(完了ページ)','forgot_complete','Forgot/complete',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:10','noindex'),(38,10,'パスワード変更(完了ページ)','forgot_reset','Forgot/reset',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:10','2016-08-01 17:26:14','noindex'),(39,10,'商品購入/配送方法選択','shopping_delivery','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:11','2016-08-01 17:26:11','noindex'),(40,10,'商品購入/支払方法選択','shopping_payment','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:11','2016-08-01 17:26:11','noindex'),(41,10,'商品購入/お届け先変更','shopping_shipping_change','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:11','2016-08-01 17:26:11','noindex'),(42,10,'商品購入/お届け先変更','shopping_shipping_edit_change','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:11','2016-08-01 17:26:11','noindex'),(43,10,'商品購入/お届け先の複数指定','shopping_shipping_multiple_change','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-08-01 17:26:11','2016-08-01 17:26:11','noindex'),(45,10,'MYページ/カード情報編集','gmo_mypage_change_card',NULL,2,NULL,NULL,NULL,NULL,'2016-10-17 15:56:03','2016-10-17 15:56:03','noindex'),(48,10,'商品購入/GMOペイメント決済画面','gmo_shopping_payment',NULL,2,NULL,NULL,NULL,NULL,'2016-10-18 11:40:30','2016-10-18 11:40:30','noindex'),(49,10,'楽天決済エラー/GMOペイメント決済画面','gmo_shopping_rakuten_result','/shopping/rakutenResult/0',2,NULL,NULL,NULL,NULL,'2016-10-18 11:40:30','2016-10-18 11:40:30','noindex'),(50,10,'データ作成ガイド','creation','creation',0,NULL,NULL,NULL,NULL,'2016-10-19 09:29:17','2016-10-19 09:29:17',NULL),(51,10,'製品別テンプレート','template','template',0,NULL,NULL,NULL,NULL,'2016-10-19 09:29:52','2016-10-19 09:29:52',NULL),(52,10,'MYページ/お届け先編集','mypage_delivery_edit','Mypage/delivery_edit',2,NULL,NULL,NULL,NULL,'2016-10-20 13:11:53','2016-10-20 13:11:53','noindex'),(53,10,'商品購入/確認','shopping_confirm','Shopping/index',2,NULL,NULL,NULL,NULL,'2016-11-01 14:00:11','2016-11-01 14:00:11','noindex');
/*!40000 ALTER TABLE `dtb_page_layout` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-07 16:06:40
