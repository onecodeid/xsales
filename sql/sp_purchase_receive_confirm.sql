BEGIN

DECLARE confirm CHAR(1);
DECLARE bill INTEGER;
DECLARE warehouse_id INTEGER;

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
    SELECT "ERR" status, "Penerimaan tersebut telah ditagihkan, tidak bisa dikonfirmasi :(" message;
    ROLLBACK;
ELSEIF confirm = "Y" THEN
    SELECT "ERR" status, "Penerimaan tersebut telah dikonfirmasi, tidak perlu diulangi :)" message;
    ROLLBACK;
ELSE
    SET warehouse_id = (SELECT P_ReceiveM_WarehouseID FROM p_receive WHERE P_ReceiveID = receiveid);
    UPDATE i_stock
    JOIN p_receivedetail ON P_ReceiveDetailA_ItemID = I_StockM_ItemID
        AND P_ReceiveDetailP_ReceiveID = receiveid
        AND P_ReceiveDetailIsActive = "Y"
    SET I_StockQty = I_StockQty + P_ReceiveDetailQty,
        I_StockLastTransCode = "PURCHASE.RECEIVE",
        I_StockLastTransRefID = P_ReceiveDetailID,
        I_StockLastTransQty = P_ReceiveDetailQty
    WHERE I_StockM_WarehouseID = warehouse_id
        AND I_StockIsActive = "Y";
        
    UPDATE p_receive SET P_ReceiveConfirm = "Y" WHERE P_ReceiveID = receiveid;

    SELECT "OK" status, JSON_OBJECT("receive_id", receiveid, "receive_note", IFNULL(P_ReceiveNote, '')) data
    FROM p_receive WHERE P_ReceiveID = receiveid;

    COMMIT;
END IF;



END