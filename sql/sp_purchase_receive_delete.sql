DROP PROCEDURE `sp_purchase_receive_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_purchase_receive_delete` (IN `receiveid` int, IN `uid` int)
BEGIN

DECLARE confirm CHAR(1);
DECLARE bill INTEGER;
DECLARE warehouse_id INTEGER;

DECLARE log_date DATE;
DECLARE rcv_id INTEGER;
DECLARE rcv_item INTEGER;
DECLARE rcv_qty DOUBLE;

DECLARE finished INTEGER DEFAULT 0;

DEClARE curRcv
    CURSOR FOR 
        SELECT P_ReceiveDetailID, P_ReceiveDetailA_ItemID, P_ReceiveDetailQty
        FROM p_receivedetail WHERE P_ReceiveDetailP_ReceiveID = receiveid AND P_ReceiveDetailIsActive = "Y";

DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

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

SET confirm = (SELECT P_ReceiveConfirm FROM p_receive WHERE P_ReceiveID = receiveid);
SET bill = (SELECT P_ReceiveF_BillID FROM p_receive WHERE P_ReceiveID = receiveid);

IF bill <> 0 THEN
    SELECT "ERR" status, "Penerimaan tersebut telah ditagihkan, tidak bisa dihapus :(" message;
ELSEIF confirm = "Y" THEN
    -- UNCONFIRM
    SET warehouse_id = (SELECT P_ReceiveM_WarehouseID FROM p_receive WHERE P_ReceiveID = receiveid);

    -- UPDATE STOCK
    UPDATE i_stock
    JOIN p_receivedetail ON P_ReceiveDetailA_ItemID = I_StockM_ItemID
        AND P_ReceiveDetailP_ReceiveID = receiveid
        AND P_ReceiveDetailIsActive = "Y"
    SET I_StockQty = I_StockQty - P_ReceiveDetailQty,
        I_StockLastTransCode = "LOG.ADJUST",
        I_StockLastTransRefID = P_ReceiveDetailID,
        I_StockLastTransQty = P_ReceiveDetailQty
    WHERE I_StockM_WarehouseID = warehouse_id
        AND I_StockIsActive = "Y";

    -- UPDATE PURCHASE & PURCHASE DETAIL
    UPDATE p_purchase
    JOIN p_purchasedetail ON P_PurchaseDetailP_PurchaseID = P_PurchaseID AND P_PurchaseDetailIsActive = "Y"
    JOIN p_receivedetail ON P_ReceiveDetailP_ReceiveID = receiveid
        AND P_ReceiveDetailIsActive = "Y"
        AND P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
    SET P_PurchaseDone = "N";

    UPDATE p_purchasedetail
    JOIN p_receivedetail ON P_ReceiveDetailP_ReceiveID = receiveid
        AND P_ReceiveDetailIsActive = "Y"
        AND P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
    SET P_PurchaseDetailReceived = P_PurchaseDetailReceived - P_ReceiveDetailQty;

    

    OPEN curRcv;

    getRcv: LOOP
        FETCH curRcv INTO rcv_id, rcv_item, rcv_qty;
        IF finished = 1 THEN 
            LEAVE getRcv;
        END IF;
        
        SET log_date = (SELECT date(Log_StockDate) FROM one_account_aw_log.log_stock WHERE Log_StockRefID = rcv_id AND Log_StockCode = "PURCHASE.RECEIVE" AND Log_StockIsActive = "Y" LIMIT 1);
        UPDATE one_account_aw_log.log_stock SET Log_StockIsActive = "N" WHERE Log_StockRefID = rcv_id AND Log_StockCode = "PURCHASE.RECEIVE" AND Log_StockIsActive = "Y";
        
        CALL one_account_aw_log.sp_log_stock_adjust(rcv_item, warehouse_id, log_date);
        UPDATE p_receivedetail SET P_ReceiveDetailIsActive = "N" WHERE P_ReceiveDetailID = rcv_id;
    END LOOP getRcv;
    CLOSE curRcv;
        
    UPDATE p_receive SET P_ReceiveConfirm = "N", P_ReceiveIsActive = "N" WHERE P_ReceiveID = receiveid;

    SELECT "OK" status, JSON_OBJECT("receive_id", receiveid) data;
--    SELECT "ERR" status, "Penerimaan tersebut telah dikonfirmasi, tidak bisa dihapus :(" message;
ELSE

    UPDATE p_purchasedetail
    JOIN p_receivedetail ON P_ReceiveDetailP_ReceiveID = receiveid
        AND P_ReceiveDetailIsActive = "Y"
        AND P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
    SET P_PurchaseDetailReceived = P_PurchaseDetailReceived - P_ReceiveDetailQty;

    UPDATE p_receivedetail SET P_ReceiveDetailIsActive = "N" WHERE P_ReceiveDetailP_ReceiveID = receiveid;
    UPDATE p_receive SET P_ReceiveIsActive = "N" WHERE P_ReceiveID = receiveid;

    SELECT "OK" status, JSON_OBJECT("receive_id", receiveid) data;
END IF;

COMMIT;

END;;
DELIMITER ;