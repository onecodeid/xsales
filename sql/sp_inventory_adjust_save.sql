BEGIN

DECLARE tmp VARCHAR(255);
DECLARE l INTEGER;
DECLARE i INTEGER DEFAULT 0;
DECLARE item_qty DOUBLE;
DECLARE item_id INTEGER;
DECLARE detail_id INTEGER;
DECLARE adjust_note VARCHAR(2000);
DECLARE warehouse_id INTEGER;

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

SET adjust_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.adjust_note'));
SET warehouse_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.warehouse'));

-- INSERT ADJUSTMENT IF NOT EXISTS
IF adjust_id = 0 THEN
	INSERT INTO i_adjust(I_AdjustNumber, I_AdjustDate, I_AdjustM_WarehouseID, I_AdjustNote)
	SELECT fn_numbering('ADJ'), now(), warehouse_id, adjust_note;
	
	SET adjust_id = (SELECT LAST_INSERT_ID());

-- UPDATE OTHERWISE
ELSE
	UPDATE i_adjust SET I_AdjustNote = adjust_note
	WHERE I_AdjustID = adjust_id;
END IF;
-- END OF INSERT ADJUSTMENT

SET l = JSON_LENGTH(jdata);
WHILE i < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', i,']'));
	
	SET item_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_id'));
	SET item_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_qty'));
	
	SET detail_id = (SELECT I_AdjustDetailID 
					FROM i_adjustdetail
					WHERE I_AdjustDetailM_ItemID = item_id AND I_AdjustDetailIsActive = "Y" AND I_AdjustDetailI_AdjustID = adjust_id);
					
	IF detail_id IS NULL THEN
		INSERt INTO i_adjustdetail(
			I_AdjustDetailI_AdjustID,
			I_AdjustDetailM_ItemID,
			I_AdjustDetailQty,
			I_AdjustDetailBeforeQty,
			I_AdjustDetailAfterQty
		)
		SELECT adjust_id, item_id, item_qty, IFNULL(I_StockQty, 0), IFNULL(I_StockQty, 0) + item_qty
		FROM m_item
		LEFT JOIN i_stock ON I_StockM_ItemID = M_ItemID AND I_StockIsActive = "Y" ANd I_StockM_WarehouseID = warehouse_id
		WHERE M_ItemID = item_id;

		SET detail_id = (SELECT LAST_INSERT_ID());
		UPDATE i_stock
			SET I_StockQty = I_StockQty + item_qty,
			I_StockLastTransCode = 'INV.ADJUSTMENT',
			I_StockLastTransRefID = detail_id,
			I_StockLastTransQty = item_qty,
			I_StockUserID = uid
		WHERE I_StockM_ItemID = item_id
		AND I_StockIsActive = "Y"
        AND I_StockM_WarehouseID = warehouse_id;

	ELSE
		UPDATE i_adjustdetail
		SET I_AdjustDetailQty = item_qty, I_AdjustDetailAfterQty = I_AdjustDetailBeforeQty + item_qty WHERE I_AdjustDetailID = detail_id;
	ENd IF;
	
	SET i = i + 1;
END WHILE;

SELECT "OK" as status, JSON_OBJECT("adjust_id", adjust_id) as data;
COMMIT;

END