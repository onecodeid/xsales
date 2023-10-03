BEGIN

DECLARE confirm CHAR(1);
DECLARE bill INTEGER;
DECLARE fail INTEGER DEFAULT 0;
DECLARE warehouse_id INTEGER;

DECLARE finished INTEGER DEFAULT 0;
DECLARE log_id INTEGER;
DECLARE curr_item_id INTEGER DEFAULT 0;
DECLARE item_id INTEGER;
DECLARE before_qty DOUBLE DEFAULT 0;
DECLARE qty DOUBLE;
DECLARE after_qty DOUBLE;

DECLARE log_cursor
    CURSOR FOR
        SELECT Log_StockID, Log_StockM_ItemID, Log_StockQty
        FROM  one_account_aw_log.log_stock
        WHERE Log_StockM_WarehouseID = warehouse_id
        ORDER BY `Log_StockM_ItemID`, `Log_StockCreated`, `Log_StockID`;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

START TRANSACTION;

-- CHECK WHETER BILLED OR CONFIRMED
SET confirm = (SELECT L_DeliveryConfirm FROM l_delivery WHERE L_DeliveryID = deliveryid);
SET bill = (SELECT L_DeliveryL_InvoiceID FROM l_delivery WHERE L_DeliveryID = deliveryid);
-- END CHECK

IF bill < -1 THEN
    SELECT "ERR" status, "Penerimaan tersebut telah ditagihkan, tidak bisa dikonfirmasi :(" message;
    ROLLBACK;
ELSEIF confirm = "Y" THEN
    SELECT "ERR" status, "Penerimaan tersebut telah dikonfirmasi, tidak perlu diulangi :)" message;
    ROLLBACK;
ELSE
    SET warehouse_id = (SELECT L_DeliveryM_WarehouseID FROM l_delivery WHERE L_DeliveryID = deliveryid);

    -- SET fail = (SELECT SUM(IF(IFNULL(I_StockQty, 0) < L_DeliveryDetailQty, 1, 0))
    -- FROM l_deliverydetail
    -- LEFT JOIN i_stock ON I_StockM_ItemID = L_DeliveryDetailA_ItemID
    --     AND I_StockM_WarehouseID = warehouse_id
    --     AND I_StockIsActive = "Y"
    -- WHERE L_DeliveryDetailL_DeliveryID = deliveryid
    --     AND L_DeliveryDetailIsActive = "Y");
    -- IF fail IS NULL THEN SET fail = 0; END IF;

    -- IF fail > 0 THEN
    --     SELECT "ERR" status, "Ada item / produk yang memiliki stok di bawah permintaan, mohon cek kembali :(" message;

-- SELECT I_StockQty, L_DeliveryDetailQty, m_itemid, m_itemname, m_warehouseid, m_warehousename
--     FROM l_deliverydetail
--     LEFT JOIN i_stock ON I_StockM_ItemID = L_DeliveryDetailA_ItemID
--         AND I_StockM_WarehouseID = warehouse_id
--         AND I_StockIsActive = "Y"
-- left join m_item on l_deliverydetaila_itemid = m_itemid
-- left join m_warehouse on m_warehouseid = warehouse_id
--     WHERE L_DeliveryDetailL_DeliveryID = deliveryid
--         AND L_DeliveryDetailIsActive = "Y";

--         ROLLBACK;
--     ELSE

        UPDATE i_stock
        JOIN l_deliverydetail ON L_DeliveryDetailA_ItemID = I_StockM_ItemID
            AND L_DeliveryDetailL_DeliveryID = deliveryid
            AND L_DeliveryDetailIsActive = "Y"
        JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
        SET I_StockQty = I_StockQty - L_DeliveryDetailQty,
            I_StockLastTransCode = "SALES.DELIVERY",
            I_StockLastTransRefID = L_DeliveryDetailID,
            I_StockLastTransQty = (0 - L_DeliveryDetailQty),
            I_StockLastTransCreated = CONCAT(L_DeliveryDate, " 23:59:59")
        WHERE I_StockM_WarehouseID = warehouse_id
            AND I_StockIsActive = "Y";

        UPDATE l_delivery SET L_DeliveryConfirm = "Y" WHERE L_DeliveryID = deliveryid;

        -- UPDATE LOG
        OPEN log_cursor;
        getLog: LOOP
            FETCH log_cursor INTO log_id, item_id, qty;
            IF finished = 1 THEN
                LEAVE getLog;
            END IF;
        
            IF curr_item_id <> item_id THEN
                SET curr_item_id = item_id;
                SET before_qty = 0;
            END IF;

            SET after_qty = before_qty + qty;

            IF after_qty < 0 THEN
                SELECT item_id, log_id, before_qty, qty, after_qty;
            END IF;

            UPDATE one_account_aw_log.log_stock
            SET Log_StockBeforeQty = before_qty, Log_StockQty = qty, Log_StockAfterQty = after_qty
            WHERE Log_StockID = log_id;

            SET before_qty = after_qty;

        END LOOP getLog;
        CLOSE log_cursor;

        SET fail = (SELECT count(Log_StockID)
        FROM  one_account_aw_log.log_stock
        WHERE Log_StockM_WarehouseID = warehouse_id AND Log_StockAfterQty < 0);

        IF fail = 0 OR fail is null THEN
            SELECT "OK" status, JSON_OBJECT("delivery_id", deliveryid) data;
            COMMIT;
        ELSE
            SELECT "ERR" status, "Stock tidak mencukupi" message;

            SELECT fail, item_id, log_id, qty;

            SELECT Log_StockID, Log_StockM_ItemID, Log_StockQty
            FROM  one_account_aw_log.log_stock
            WHERE Log_StockM_WarehouseID = warehouse_id ANd Log_StockM_ItemID = item_id
            ORDER BY `Log_StockM_ItemID`, `Log_StockCreated`, `Log_StockID`;

            SELECT I_StockQty, L_DeliveryDetailQty, m_itemid, m_itemname, m_warehouseid, m_warehousename, l_deliverydate
            FROM l_deliverydetail
            JOIN l_delivery ON l_deliveryDetaill_deliveryid = l_deliveryid
            LEFT JOIN i_stock ON I_StockM_ItemID = L_DeliveryDetailA_ItemID
                AND I_StockM_WarehouseID = warehouse_id
                AND I_StockIsActive = "Y"
            left join m_item on l_deliverydetaila_itemid = m_itemid
            left join m_warehouse on m_warehouseid = warehouse_id
            WHERE L_DeliveryDetailL_DeliveryID = deliveryid
                AND L_DeliveryDetailIsActive = "Y";

            ROLLBACK;
        END IF;

    -- END IF;

    
END IF;

END