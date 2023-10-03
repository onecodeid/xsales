DROP PROCEDURE IF EXISTS `sp_dashboard_warehouse`;
DELIMITER ;;
CREATE PROCEDURE `sp_dashboard_warehouse` (IN `section` varchar(50), IN `sdate` date, IN `edate` date, IN `staffid` INTEGER)
BEGIN

DECLARE deliveries TEXT;
DECLARE deliveries2 TEXT;
DECLARE expedition_cnt INTEGER;
DECLARE driver_cnt INTEGER;

IF section = 'WAREHOUSE.001' THEN
    SELECT M_WarehouseName warehouse_name, M_WarehouseShortName warehouse_short_name, COUNT(DISTINCT L_DeliveryID) delivery_cnt,
        COUNT(DISTINCT(L_DeliveryDetailA_ItemID)) item_cnt,
        SUM(L_DeliveryDetailQty) item_qty
    FROM l_delivery
    JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID AND L_DeliveryDetailISActive = "Y"
    JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
    WHERE L_DeliveryIsActive = "Y" AND L_DeliveryDate BETWEEN sdate AND edate
    GROUP BY L_DeliveryM_WarehouseID;

ELSEIF section = 'WAREHOUSE.002' THEN
-- PROSENTASE PREGERAKAN BARANG
    SELECT warehouse_id, warehouse_name, warehouse_short_name,
        SUM(IF(omzet_freq > 2, item_stock, 0)) as fm,
        SUM(IF(omzet_freq = 2, item_stock, 0)) as mm,
        SUM(IF(omzet_freq = 1, item_stock, 0)) as sm,
        SUM(IF(omzet_freq < 1, item_stock, 0)) as hsm,
        SUM(IF(omzet_freq > 2, 1, 0)) as nfm,
        SUM(IF(omzet_freq = 2, 1, 0)) as nmm,
        SUM(IF(omzet_freq = 1, 1, 0)) as nsm,
        SUM(IF(omzet_freq < 1, 1, 0)) as nhsm
    FROM (
    SELECT *
    FROM ( SELECT m_itemid item_id, m_itemcode item_code, m_itemname item_name, m_unitname unit_name,
    (ifnull(omzet_qty, 0) + ifnull(assembly_qty, 0)) omzet_qty, 
    (ifnull(omzet_freq, 0) + ifnull(assembly_cnt, 0)) omzet_freq, 
    ifnull(log_after_qty, 0) item_stock, 
    M_WarehouseName warehouse_name, M_WarehouseShortName warehouse_short_name, M_WarehouseID warehouse_id

    FROM m_item
    join m_unit on m_itemm_unitid = m_unitid
    join m_category ON M_ItemM_CategoryID = M_CategoryID
    join m_warehouse ON M_WarehouseIsActive = "Y"
    LEFT JOIN (
        SELECT Log_StockM_ItemID log_item_id, Log_StockM_WarehouseID log_warehouse_id, SUM(Log_StockAfterQty) log_after_qty
        FROM one_account_aw_log.log_stock
        WHERE Log_StockIndex IN (
            SELECT MAX(Log_StockIndex)
            FROM one_account_aw_log.log_stock
            WHERE Log_StockIsActive = "Y"
            AND Log_StockDate <= edate AND ((Log_StockM_WarehouseID = 0 AND 0 <> 0) OR 0 = 0)
            GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID
        ) 
        AND Log_StockAfterQty > 0
        GROUP BY Log_StockM_ItemID, Log_StockM_WarehouseID) lg 
    ON log_item_id = M_ItemID AND log_warehouse_id = M_WarehouseID

    LEFT JOIN (
        SELECT M_ItemID p_item_id, M_ItemName item_name, stock_min as p_item_min_stock,
            COUNT(DISTINCT L_DeliveryID) omzet_freq, SUM(L_DeliveryDetailQty) omzet_qty,

            IFNULL(stock_qty, 0) stock_qty, L_DeliveryM_WarehouseID p_warehouse_id
            
            FROM l_deliverydetail
            JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
                AND L_DeliveryDate BETWEEN sdate AND edate
                AND L_DeliveryConfirm = 'Y'
                AND L_DeliveryIsActive = 'Y'
            JOIN m_warehouse ON L_DeliveryM_WarehouseID = M_WarehouseID
            JOIN m_item ON L_DeliveryDetailA_ItemID = M_ItemID

            LEFT JOIN (
                SELECT SUM(I_StockQty) stock_qty, I_StockM_ItemID stock_item_id,
                    SUM(I_StockMinQty) stock_min
                FROM i_stock
                WHERE ((I_StockM_WarehouseID = 0 AND 0 <> 0) OR 0 = 0)
                    AND I_StockIsActive = 'Y'
                GROUP BY I_StockM_ItemID
            ) stock ON stock.stock_item_id = M_ItemID

            WHERE L_DeliveryDetailIsActive = 'Y'
                AND ((L_DeliveryM_WarehouseID = 0 AND 0 <> 0) OR 0 = 0)
            GROUP BY L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID
            ORDER BY M_ItemName
    ) p ON p_item_id = M_ItemID AND M_WarehouseID = p_warehouse_id

    LEFT JOIN (
        SELECT COUNT(DISTINCT I_AssemblyID) assembly_cnt, SUM(I_AssemblyDetailQty) assembly_qty, 
            I_AssemblyDetailM_ItemID assembly_item, I_AssemblyM_WarehouseID assembly_warehouse
        FROM i_assemblydetail
        JOIN i_assembly ON I_AssemblyDate BETWEEN sdate AND edate AND I_AssemblyIsActive = "Y"
        WHERE I_AssemblyDetailIsActive = "Y"
        GROUP BY I_AssemblyDetailM_ItemID, I_AssemblyM_WarehouseID
    ) assmb ON assembly_item = M_ItemID AND assembly_warehouse = M_WarehouseID

    where m_itemisactive = "Y" and (M_ITemName LIKE '%' OR M_ITemCode LIKE '%')
    and (log_after_qty is not null or omzet_freq is not null)
    order by m_warehousename, m_itemname) xyz order by warehouse_name asc, omzet_freq desc, item_name asc
    ) abc
    GROUP BY warehouse_id;

ELSEIF section = "WAREHOUSE.003" THEN
    SET deliveries = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("dtype_id", dtype_id, "name", dtype_name, "amount", delivery_cnt)), "]")
                        FROM (
                            SELECT M_DeliveryTypeID dtype_id, M_DeliveryTypeName dtype_name,
                                IFNULL(COUNT(L_DeliveryID), 0) delivery_cnt
                            FROM m_deliverytype
                            LEFT JOIN l_delivery ON L_DeliveryM_DeliveryTypeID = M_DeliveryTypeID
                            AND L_DeliveryIsACtive = "Y" AND L_DeliveryDate BETWEEN sdate AND edate
                            GROUP BY M_DeliveryTypeID) x);
    SET deliveries2 = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("name", dlv_name, "amount", IFNULL(dlv_cnt, 0))), "]")
                        FROM (
                            SELECT n, dlv_name, dlv_cnt FROM (
                                SELECT "N" AS n, 'Tanpa Ekspedisi' as dlv_name
                                UNION ALL
                                SELECT "Y" as n, 'Dengan Ekspedisi' as dlv_name
                            ) cte
                            LEFT JOIN (
                                SELECT COUNT(dlv) as dlv_cnt, dlv
                                FROM (
                                    SELECT IF(L_DeliveryM_ExpeditionID=0,'N','Y') as dlv
                                    FROM l_delivery
                                    WHERE L_DeliveryIsACtive = "Y" AND L_DeliveryDate BETWEEN sdate AND edate) x
                            GROUP BY dlv) y ON dlv = n
                            ) z);

    SET expedition_cnt = (SELECT COUNT(M_ExpeditionID) FROM m_expedition WHERE M_ExpeditionIsActive = "Y");
    SET driver_cnt = (SELECT COUNT(M_DriverID) FROM m_driver WHERE M_DriverIsActive = "Y");
    SELECT deliveries, deliveries2, expedition_cnt, driver_cnt;
END IF;

END;;
DELIMITER ;

CALL sp_dashboard_warehouse("WAREHOUSE.003", "2022-07-01", "2022-07-31", 0);