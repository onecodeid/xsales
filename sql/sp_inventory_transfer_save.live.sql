BEGIN

DECLARE tmp VARCHAR(255);
DECLARE l INTEGER;
DECLARE i INTEGER DEFAULT 0;
DECLARE item_qty DOUBLE;
DECLARE item_id INTEGER;
DECLARE detail_id INTEGER;
DECLARE transfer_note VARCHAR(2000);
DECLARE warehouse_id INTEGER;
DECLARE to_warehouse_id INTEGER;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

START TRANSACTION;

SET transfer_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.transfer_note'));
SET warehouse_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.from'));
SET to_warehouse_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.to'));

IF transfer_id = 0 THEN
	INSERT INTO i_transfer(I_TransferNumber, I_TransferDate, I_TransferM_WarehouseID, I_TransferToM_WarehouseID, I_TransferNote)
	SELECT fn_numbering('TRF'), now(), warehouse_id, to_warehouse_id, transfer_note;
	
	SET transfer_id = (SELECT LAST_INSERT_ID());
ELSE
	UPDATE i_transfer SET I_TransferNote = transfer_note
	WHERE I_TransferID = transfer_id;
END IF;

SET l = JSON_LENGTH(jdata);
WHILE i < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', i,']'));
	
	SET item_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_id'));
	SET item_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_qty'));
	
	SET detail_id = (SELECT I_TransferDetailID 
					FROM i_transferdetail
					WHERE I_TransferDetailM_ItemID = item_id AND I_TransferDetailIsActive = "Y" AND I_TransferDetailI_TransferID = transfer_id);
					
	IF detail_id IS NULL THEN
		INSERt INTO i_transferdetail(
			I_TransferDetailI_TransferID,
			I_TransferDetailM_ItemID,
			I_TransferDetailQty,
			I_TransferDetailBeforeQty,
			I_TransferDetailAfterQty,
			I_TransferDetailToBeforeQty,
			I_TransferDetailToAfterQty
		)
		SELECT transfer_id, item_id, item_qty, IFNULL(f.I_StockQty, 0), IFNULL(f.I_StockQty, 0) - item_qty, 
            IFNULL(t.I_StockQty, 0), IFNULL(t.I_StockQty, 0) + item_qty
		FROM m_item
		LEFT JOIN i_stock f ON f.I_StockM_ItemID = M_ItemID AND f.I_StockIsActive = "Y" ANd f.I_StockM_WarehouseID = warehouse_id
        LEFT JOIN i_stock t ON t.I_StockM_ItemID = M_ItemID AND t.I_StockIsActive = "Y" ANd t.I_StockM_WarehouseID = to_warehouse_id
		WHERE M_ItemID = item_id;

		SET detail_id = (SELECT LAST_INSERT_ID());
		
        UPDATE i_stock
			SET I_StockQty = I_StockQty - item_qty,
			I_StockLastTransCode = 'INV.TRANSFER.OUT',
			I_StockLastTransRefID = detail_id,
			I_StockLastTransQty = item_qty,
			I_StockUserID = uid
		WHERE I_StockM_ItemID = item_id
		AND I_StockIsActive = "Y"
        AND I_StockM_WarehouseID = warehouse_id;

        UPDATE i_stock
			SET I_StockQty = I_StockQty + item_qty,
			I_StockLastTransCode = 'INV.TRANSFER.IN',
			I_StockLastTransRefID = detail_id,
			I_StockLastTransQty = item_qty,
			I_StockUserID = uid
		WHERE I_StockM_ItemID = item_id
		AND I_StockIsActive = "Y"
        AND I_StockM_WarehouseID = to_warehouse_id;

	ELSE
		UPDATE i_transferdetail
		SET I_TransferDetailQty = item_qty, I_TransferDetailAfterQty = I_TransferDetailBeforeQty + item_qty WHERE I_TransferDetailID = detail_id;
	ENd IF;
	
	SET i = i + 1;
END WHILE;

SELECT "OK" as status, JSON_OBJECT("transfer_id", transfer_id) as data;
COMMIT;

END