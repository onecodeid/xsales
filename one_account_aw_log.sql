-- MariaDB dump 10.18  Distrib 10.4.17-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: one_account_aw_log
-- ------------------------------------------------------
-- Server version	10.4.17-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `log_hpp`
--

DROP TABLE IF EXISTS `log_hpp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_hpp` (
  `Log_HppID` int(11) NOT NULL AUTO_INCREMENT,
  `Log_HppDate` date DEFAULT NULL,
  `Log_HppM_ItemID` int(11) NOT NULL DEFAULT 0,
  `Log_HppCode` varchar(25) DEFAULT NULL,
  `Log_HppRefID` int(11) NOT NULL DEFAULT 0,
  `Log_HppRefNumber` varchar(25) DEFAULT NULL,
  `Log_HppBeforeQty` double NOT NULL DEFAULT 0,
  `Log_HppQty` double NOT NULL DEFAULT 0,
  `Log_HppAfterQty` double NOT NULL DEFAULT 0,
  `Log_HppBeforeAmount` double NOT NULL DEFAULT 0,
  `Log_HppAfterAmount` double NOT NULL DEFAULT 0,
  `Log_HppIsActive` char(1) NOT NULL DEFAULT 'Y',
  `Log_HppCreated` datetime NOT NULL DEFAULT current_timestamp(),
  `Log_HppLastUpdated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`Log_HppID`),
  KEY `Log_HppDate` (`Log_HppDate`),
  KEY `Log_HppM_ItemID` (`Log_HppM_ItemID`),
  KEY `Log_HppCode` (`Log_HppCode`),
  KEY `Log_HppRefID` (`Log_HppRefID`),
  KEY `Log_HppRefNumber` (`Log_HppRefNumber`),
  KEY `Log_HppBeforeQty` (`Log_HppBeforeQty`),
  KEY `Log_HppQty` (`Log_HppQty`),
  KEY `Log_HppAfterQty` (`Log_HppAfterQty`),
  KEY `Log_HppBeforeAmount` (`Log_HppBeforeAmount`),
  KEY `Log_HppAfterAmount` (`Log_HppAfterAmount`),
  KEY `Log_HppIsActive` (`Log_HppIsActive`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_hpp`
--

LOCK TABLES `log_hpp` WRITE;
/*!40000 ALTER TABLE `log_hpp` DISABLE KEYS */;
INSERT INTO `log_hpp` VALUES (3,'2021-07-15',1,'PURCHASE.RECEIVE',42,'',90,20,110,20000,9000,'Y','2021-07-15 16:51:52','2021-07-15 16:51:52'),(4,'2021-07-15',1,'PURCHASE.RECEIVE',44,'',0,50,50,18000,24750.000000000004,'Y','2021-07-15 16:55:33','2021-07-15 16:55:33'),(5,'2021-07-15',2,'PURCHASE.RECEIVE',45,'',0,20,20,0,27000,'Y','2021-07-15 17:42:30','2021-07-15 17:42:30'),(6,'2021-07-18',1,'PURCHASE.RECEIVE',46,'',0,100,100,20109.375,34650,'Y','2021-07-18 16:37:30','2021-07-18 16:37:30');
/*!40000 ALTER TABLE `log_hpp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_stock`
--

DROP TABLE IF EXISTS `log_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_stock` (
  `Log_StockID` int(11) NOT NULL AUTO_INCREMENT,
  `Log_StockCode` varchar(25) DEFAULT NULL,
  `Log_StockRefID` int(11) NOT NULL DEFAULT 0,
  `Log_StockRefNumber` varchar(25) DEFAULT NULL,
  `Log_StockM_CustomerID` int(11) NOT NULL DEFAULT 0,
  `Log_StockM_SupplierID` int(11) NOT NULL DEFAULT 0,
  `Log_StockM_ItemID` int(11) NOT NULL DEFAULT 0,
  `Log_StockM_WarehouseID` int(11) NOT NULL DEFAULT 0,
  `Log_StockBeforeQty` double NOT NULL DEFAULT 0,
  `Log_StockQty` double NOT NULL DEFAULT 0,
  `Log_StockAfterQty` double NOT NULL DEFAULT 0,
  `Log_StockIsActive` char(1) NOT NULL DEFAULT 'Y',
  `Log_StockLastUpdated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Log_StockCreated` datetime NOT NULL DEFAULT current_timestamp(),
  `Log_StockUserID` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`Log_StockID`),
  KEY `Log_StockRefID` (`Log_StockRefID`),
  KEY `Log_StockRefNumber` (`Log_StockRefNumber`),
  KEY `Log_StockM_ItemID` (`Log_StockM_ItemID`),
  KEY `Log_StockBeforeQty` (`Log_StockBeforeQty`),
  KEY `Log_StockQty` (`Log_StockQty`),
  KEY `Log_StockAfterQty` (`Log_StockAfterQty`),
  KEY `Log_StockIsActive` (`Log_StockIsActive`),
  KEY `Log_StockUID` (`Log_StockUserID`),
  KEY `Log_StockM_CustomerID` (`Log_StockM_CustomerID`),
  KEY `Log_StockM_SupplierID` (`Log_StockM_SupplierID`),
  KEY `Log_StockM_WarehouseID` (`Log_StockM_WarehouseID`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_stock`
--

LOCK TABLES `log_stock` WRITE;
/*!40000 ALTER TABLE `log_stock` DISABLE KEYS */;
INSERT INTO `log_stock` VALUES (1,'INV.ADJUSTMENT',1,'',0,0,3,3,0,1000,1000,'Y','2021-07-07 08:48:42','2021-07-07 08:48:42',1),(2,'INV.ADJUSTMENT',2,'',0,0,4,3,0,1000,1000,'Y','2021-07-07 08:48:42','2021-07-07 08:48:42',1),(3,'INV.ADJUSTMENT',3,'',0,0,6,3,0,1000,1000,'Y','2021-07-07 08:48:42','2021-07-07 08:48:42',1),(4,'INV.TRANSFER.OUT',1,'',0,0,3,3,1000,100,900,'Y','2021-07-07 13:32:35','2021-07-07 13:32:35',1),(5,'INV.TRANSFER.IN',1,'',0,0,3,2,0,100,100,'Y','2021-07-07 13:32:35','2021-07-07 13:32:35',1),(6,'INV.TRANSFER.OUT',2,'',0,0,4,3,1000,100,900,'Y','2021-07-07 13:32:35','2021-07-07 13:32:35',1),(7,'INV.TRANSFER.IN',2,'',0,0,4,2,0,100,100,'Y','2021-07-07 13:32:35','2021-07-07 13:32:35',1),(8,'SALES.DELIVERY',48,'',0,0,11,2,0,-100,-100,'Y','2021-07-11 16:23:32','2021-07-11 16:23:32',1),(9,'PURCHASE.RECEIVE',41,'',0,0,8,2,0,5,5,'Y','2021-07-12 09:33:29','2021-07-12 09:33:29',1),(10,'INV.ADJUSTMENT',4,'',0,0,1,1,0,100,100,'Y','2021-07-13 13:22:01','2021-07-13 13:22:01',1),(11,'SALES.DELIVERY',51,'',0,0,1,1,100,-10,90,'Y','2021-07-13 13:22:13','2021-07-13 13:22:13',1),(12,'PURCHASE.RECEIVE',42,'',0,0,1,1,90,20,110,'Y','2021-07-15 10:14:18','2021-07-15 10:14:18',1),(13,'PURCHASE.RECEIVE',43,'',0,0,2,1,0,20,20,'Y','2021-07-15 10:14:18','2021-07-15 10:14:18',1),(22,'PURCHASE.RECEIVE',44,'',0,0,1,2,0,50,50,'Y','2021-07-15 16:55:10','2021-07-15 16:55:10',1),(23,'PURCHASE.RECEIVE',45,'',0,0,2,3,0,20,20,'Y','2021-07-15 17:42:30','2021-07-15 17:42:30',1),(24,'PURCHASE.RECEIVE',46,'',0,0,1,3,0,100,100,'Y','2021-07-18 16:37:30','2021-07-18 16:37:30',1),(25,'SALES.DELIVERY',52,'RO21070012',3,0,1,3,100,-50,50,'Y','2021-07-18 16:46:43','2021-07-18 16:46:43',1),(26,'INV.ADJUSTMENT',5,'',0,0,1,3,50,10,60,'Y','2021-07-19 05:34:46','2021-07-19 05:34:46',1),(27,'INV.ADJUSTMENT',6,'',0,0,3,3,900,-100,800,'Y','2021-07-19 05:34:46','2021-07-19 05:34:46',1),(28,'INV.ADJUSTMENT',7,'',0,0,3,4,0,10,10,'Y','2021-07-19 06:00:55','2021-07-19 06:00:55',1),(29,'INV.ADJUSTMENT',8,'',0,0,1,4,0,10,10,'Y','2021-07-19 06:09:39','2021-07-19 06:09:39',1),(30,'INV.ADJUSTMENT',9,'',0,0,1,3,60,-10,50,'Y','2021-07-19 06:41:06','2021-07-19 06:41:06',1),(31,'INV.ADJUSTMENT',10,'',0,0,3,3,800,50,850,'Y','2021-07-19 06:41:06','2021-07-19 06:41:06',1),(32,'INV.TRANSFER.OUT',3,'',0,0,3,3,850,150,700,'Y','2021-07-19 06:50:13','2021-07-19 06:50:13',1),(33,'INV.TRANSFER.IN',3,'',0,0,3,1,0,150,150,'Y','2021-07-19 06:50:13','2021-07-19 06:50:13',1),(34,'INV.TRANSFER.OUT',4,'',0,0,6,3,1000,200,800,'Y','2021-07-19 06:50:13','2021-07-19 06:50:13',1),(35,'INV.TRANSFER.IN',4,'',0,0,6,1,0,200,200,'Y','2021-07-19 06:50:13','2021-07-19 06:50:13',1),(36,'INV.TRANSFER.OUT',5,'',0,0,3,3,700,100,600,'Y','2021-07-19 06:56:03','2021-07-19 06:56:03',1),(37,'INV.TRANSFER.IN',5,'',0,0,3,1,150,100,250,'Y','2021-07-19 06:56:03','2021-07-19 06:56:03',1),(38,'INV.TRANSFER.OUT',6,'',0,0,6,3,800,150,650,'Y','2021-07-19 06:56:03','2021-07-19 06:56:03',1),(39,'INV.TRANSFER.IN',6,'',0,0,6,1,200,150,350,'Y','2021-07-19 06:56:03','2021-07-19 06:56:03',1),(40,'INV.ADJUSTMENT',11,'',0,0,1,2,50,100,150,'Y','2021-07-19 14:07:57','2021-07-19 14:07:57',1),(41,'INV.ADJUSTMENT',12,'',0,0,2,2,0,200,200,'Y','2021-07-19 14:07:57','2021-07-19 14:07:57',1),(42,'INV.TRANSFER.OUT',7,'',0,0,3,3,600,100,500,'Y','2021-07-19 14:26:48','2021-07-19 14:26:48',1),(43,'INV.TRANSFER.IN',7,'',0,0,3,1,250,100,350,'Y','2021-07-19 14:26:48','2021-07-19 14:26:48',1),(44,'INV.TRANSFER.OUT',8,'',0,0,4,3,900,400,500,'Y','2021-07-19 14:26:48','2021-07-19 14:26:48',1),(45,'INV.TRANSFER.IN',8,'',0,0,4,1,0,400,400,'Y','2021-07-19 14:26:48','2021-07-19 14:26:48',1);
/*!40000 ALTER TABLE `log_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_type`
--

DROP TABLE IF EXISTS `log_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_type` (
  `Log_TypeID` int(11) NOT NULL AUTO_INCREMENT,
  `Log_TypeCode` varchar(25) DEFAULT NULL,
  `Log_TypeText` varchar(255) DEFAULT NULL,
  `Log_TypeIsActive` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`Log_TypeID`),
  KEY `Log_TypeCode` (`Log_TypeCode`),
  KEY `Log_TypeIsActive` (`Log_TypeIsActive`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_type`
--

LOCK TABLES `log_type` WRITE;
/*!40000 ALTER TABLE `log_type` DISABLE KEYS */;
INSERT INTO `log_type` VALUES (1,'SALES.DELIVERY','Pengiriman ke [customer]','Y'),(2,'PURCHASE.RECEIVE','Penerimaan dari [supplier]','Y'),(3,'INV.ADJUSTMENT','Adjustment','Y'),(4,'INV.TRANSFER.IN','Transfer barang dari [warehouse]','Y'),(5,'INV.TRANSFER.OUT','Transfer barang ke [warehouse]','Y');
/*!40000 ALTER TABLE `log_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'one_account_aw_log'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-07-20  5:26:37
