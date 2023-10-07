-- MariaDB dump 10.19  Distrib 10.4.21-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: xsales_log
-- ------------------------------------------------------
-- Server version	10.4.21-MariaDB

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
-- Table structure for table `log_activity`
--

DROP TABLE IF EXISTS `log_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_activity` (
  `Log_ActivityID` int(11) NOT NULL AUTO_INCREMENT,
  `Log_ActivityWhat` varchar(25) DEFAULT NULL,
  `Log_ActivityModule` varchar(25) DEFAULT NULL,
  `Log_ActivityRefID` int(11) NOT NULL DEFAULT 0,
  `Log_ActivityRefNumber` varchar(25) DEFAULT NULL,
  `Log_ActivityRefDate` date DEFAULT NULL,
  `Log_ActivityUID` int(11) NOT NULL DEFAULT 0,
  `Log_ActivityStaffID` int(11) NOT NULL DEFAULT 0,
  `Log_ActivityStaffName` varchar(50) DEFAULT NULL,
  `Log_ActivityCreated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Log_ActivityID`),
  KEY `Log_ActivityWhat` (`Log_ActivityWhat`),
  KEY `Log_ActivityModule` (`Log_ActivityModule`),
  KEY `Log_ActivityUID` (`Log_ActivityUID`),
  KEY `Log_ActivityStaffID` (`Log_ActivityStaffID`),
  KEY `Log_ActivityStaffName` (`Log_ActivityStaffName`),
  KEY `Log_ActivityRefID` (`Log_ActivityRefID`),
  KEY `Log_ActivityRefNumber` (`Log_ActivityRefNumber`),
  KEY `Log_ActivityRefDate` (`Log_ActivityRefDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_activity`
--

LOCK TABLES `log_activity` WRITE;
/*!40000 ALTER TABLE `log_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_activity` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_hpp`
--

LOCK TABLES `log_hpp` WRITE;
/*!40000 ALTER TABLE `log_hpp` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_hpp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_itempurchase`
--

DROP TABLE IF EXISTS `log_itempurchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_itempurchase` (
  `Log_ItemPurchaseID` int(11) NOT NULL AUTO_INCREMENT,
  `Log_ItemPurchaseM_ItemID` int(11) NOT NULL DEFAULT 0,
  `Log_ItemPurchaseDetails` text DEFAULT NULL,
  `Log_ItemPurchaseIsActive` char(1) NOT NULL DEFAULT 'Y',
  `Log_ItemPurchaseCreated` datetime NOT NULL DEFAULT current_timestamp(),
  `Log_ItemPurchaseLastUpdated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`Log_ItemPurchaseID`),
  KEY `Log_ItemPurchaseM_ItemID` (`Log_ItemPurchaseM_ItemID`),
  KEY `Log_ItemPurchaseIsActive` (`Log_ItemPurchaseIsActive`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_itempurchase`
--

LOCK TABLES `log_itempurchase` WRITE;
/*!40000 ALTER TABLE `log_itempurchase` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_itempurchase` ENABLE KEYS */;
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
  `Log_StockFromToM_WarehouseID` int(11) NOT NULL DEFAULT 0,
  `Log_StockBeforeQty` double NOT NULL DEFAULT 0,
  `Log_StockQty` double NOT NULL DEFAULT 0,
  `Log_StockAfterQty` double NOT NULL DEFAULT 0,
  `Log_StockIsActive` char(1) NOT NULL DEFAULT 'Y',
  `Log_StockLastUpdated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Log_StockCreated` datetime NOT NULL DEFAULT current_timestamp(),
  `Log_StockUserID` int(11) NOT NULL DEFAULT 0,
  `Log_StockDate` datetime NOT NULL DEFAULT current_timestamp(),
  `log_stockindex` char(30) DEFAULT NULL,
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
  KEY `Log_StockM_WarehouseID` (`Log_StockM_WarehouseID`),
  KEY `Log_StockDate` (`Log_StockDate`),
  KEY `Log_StockFromToM_WarehouseID` (`Log_StockFromToM_WarehouseID`),
  KEY `Log_StockIndex` (`log_stockindex`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_stock`
--

LOCK TABLES `log_stock` WRITE;
/*!40000 ALTER TABLE `log_stock` DISABLE KEYS */;
INSERT INTO `log_stock` VALUES (1,'PURCHASE.RECEIVE',1,'',0,0,5,1,0,0,10,10,'Y','2023-10-04 22:08:20','2023-10-04 22:08:20',1,'2023-10-03 23:38:26','2023-10-03 23:38:26.0000000001'),(2,'PURCHASE.RECEIVE',2,'',0,0,6,1,0,0,10,10,'Y','2023-10-04 22:08:20','2023-10-04 22:08:20',1,'2023-10-03 23:38:54','2023-10-03 23:38:54.0000000002'),(3,'PURCHASE.MODIFY',1,'',0,0,5,1,0,10,-10,0,'Y','2023-10-04 22:26:12','2023-10-04 22:26:12',1,'2023-10-03 23:38:26','2023-10-03 23:38:26.0000000003'),(4,'PURCHASE.DELETE',2,'',0,0,6,1,0,10,-10,0,'Y','2023-10-04 22:26:12','2023-10-04 22:26:12',1,'2023-10-03 23:38:54','2023-10-03 23:38:54.0000000004'),(5,'PURCHASE.RECEIVE',1,'',0,0,5,1,0,0,20,20,'Y','2023-10-04 22:26:12','2023-10-04 22:26:12',1,'2023-10-03 23:38:26','2023-10-03 23:38:26.0000000005'),(6,'PURCHASE.DELETE',1,'',0,0,5,1,0,20,-20,0,'Y','2023-10-04 22:27:04','2023-10-04 22:27:04',1,'2023-10-03 23:38:26','2023-10-03 23:38:26.0000000006'),(7,'PURCHASE.RECEIVE',3,'',0,0,6,1,0,0,10,10,'Y','2023-10-04 22:27:04','2023-10-04 22:27:04',1,'2023-10-03 23:38:54','2023-10-03 23:38:54.0000000007'),(8,'PURCHASE.DELETE',3,'',0,0,6,1,0,10,-10,0,'Y','2023-10-04 22:30:05','2023-10-04 22:30:05',1,'2023-10-03 23:38:54','2023-10-03 23:38:54.0000000008'),(9,'PURCHASE.RECEIVE',4,'',0,0,5,1,0,0,20,20,'Y','2023-10-04 22:30:05','2023-10-04 22:30:05',1,'2023-10-03 23:38:26','2023-10-03 23:38:26.0000000009'),(10,'PURCHASE.RECEIVE',4,'',0,0,5,1,0,20,20,40,'Y','2023-10-04 22:32:02','2023-10-04 22:32:02',1,'2023-10-03 23:38:26','2023-10-03 23:38:26.0000000010'),(11,'PURCHASE.RECEIVE',5,'',0,0,6,1,0,0,10,10,'Y','2023-10-04 22:32:02','2023-10-04 22:32:02',1,'2023-10-03 23:38:54','2023-10-03 23:38:54.0000000011');
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
  `Log_TypeName` varchar(50) DEFAULT NULL,
  `Log_TypeText` varchar(255) DEFAULT NULL,
  `Log_TypeIsActive` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`Log_TypeID`),
  KEY `Log_TypeCode` (`Log_TypeCode`),
  KEY `Log_TypeIsActive` (`Log_TypeIsActive`),
  KEY `Log_TypeName` (`Log_TypeName`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_type`
--

LOCK TABLES `log_type` WRITE;
/*!40000 ALTER TABLE `log_type` DISABLE KEYS */;
INSERT INTO `log_type` VALUES (1,'SALES.DELIVERY','Pengiriman','Pengiriman ke [customer]','Y'),(2,'PURCHASE.RECEIVE','Penerimaan','Penerimaan dari [supplier]','Y'),(3,'INV.ADJUSTMENT','Penyesuaian','Adjustment','Y'),(4,'INV.TRANSFER.IN','Transfer Gudang','Transfer barang dari [towarehouse]','Y'),(5,'INV.TRANSFER.OUT','Transfer Gudang','Transfer barang ke [towarehouse]','Y'),(6,'INV.ASSEMBLY.IN','Perakitan','Perakitan Produk','Y'),(7,'INV.ASSEMBLY.OUT','Perakitan','Perakitan Produk','Y'),(8,'SALES.DELIVERY.DELETE','[hapus] Pengiriman','(hapus) Pengiriman ke [customer]','Y'),(9,'SALES.RETUR','Retur Penjualan','Retur Penjualan dari [customer]','Y'),(10,'SALES.RETUR.DELETE','[hapus] Retur Penjualan','(hapus) Retur Penjualan dari [customer]','Y'),(11,'PURCHASE.MODIFY','Revisi Penerimaan','Revisi Penerimaan dari [supplier]','Y'),(12,'PURCHASE.DELETE','Hapus Penerimaan','Hapus Penerimaan dari [supplier]','Y');
/*!40000 ALTER TABLE `log_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'xsales_log'
--
/*!50003 DROP PROCEDURE IF EXISTS `sp_log_stock_adjust` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_log_stock_adjust`(IN `itemid` int, IN `warehouseid` int, IN `logdate` date)
BEGIN

DECLARE finished INTEGER DEFAULT 0;
DECLARE logig varchar(100) DEFAULT "";

DECLARE laststock DOUBLE DEFAULT 0;
DECLARE logid INTEGER;
DECLARE beforeqty DOUBLE;
DECLARE qty DOUBLE;
DECLARE afterqty DOUBLE;













	
DEClARE curLog
    CURSOR FOR 
        SELECT Log_StockID, Log_StockBeforeQty, Log_StockQty, Log_StockAfterQty
        FROM log_stock WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid AND Log_StockIsActive = "Y"
        AND Log_StockDate >= CONCAT(logdate, " 00:00:00")
        ORDER BY Log_StockDate ASC, Log_StockID ASC;


DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

SET laststock = (SELECT Log_StockAfterQty
    FROM log_stock WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid AND Log_StockIsActive = "Y"
    AND Log_StockDate < CONCAT(logdate, " 00:00:00")
    ORDER BY Log_StockDate DESC, Log_StockID DESC
    LIMIT 1);

IF laststock IS NULL THEN SET laststock = 0; END IF;

OPEN curLog;

getLog: LOOP
    FETCH curLog INTO logid, beforeqty, qty, afterqty;
    IF finished = 1 THEN 
        LEAVE getLog;
    END IF;
    
    
    UPDATE log_stock SET Log_StockBeforeQty = laststock, Log_StockAfterQty = laststock + Log_StockQty WHERE Log_StockID = logid;
    SET laststock = laststock + qty;

END LOOP getLog;
CLOSE curLog;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-10-06 13:40:39
