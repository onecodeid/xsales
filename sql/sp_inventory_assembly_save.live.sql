BEGIN

DECLARE tmp VARCHAR(255);
DECLARE l INTEGER;
DECLARE i INTEGER DEFAULT 0;
DECLARE item_qty DOUBLE;
DECLARE item_id INTEGER;
DECLARE detail_id INTEGER;
DECLARE assembly_date DATE;
DECLARE assembly_note VARCHAR(2000);
DECLARE warehouse_id INTEGER;
DECLARE to_item_id INTEGER;
DECLARE to_item_qty DOUBLE;
DECLARE minus_cnt INTEGER;

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

SET assembly_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.assembly_date'));
SET assembly_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.assembly_note'));
SET warehouse_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.warehouse'));
SET to_item_qty = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.to_item_qty'));
SET to_item_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.to_item_id'));

IF assembly_date IS NULL THEN SET assembly_date = date(now()); ENd IF;


IF assembly_id = 0 THEN
	INSERT INTO i_assembly(I_AssemblyNumber, I_AssemblyDate, I_AssemblyM_WarehouseID, I_AssemblyNote,
		I_AssemblyOutM_ItemID, I_AssemblyOutQty)
	SELECT fn_numbering('ASS'), assembly_date, warehouse_id, assembly_note, to_item_id, to_item_qty;
	
	SET assembly_id = (SELECT LAST_INSERT_ID());


ELSE
	UPDATE i_assembly SET I_AssemblyNote = assembly_note
	WHERE I_AssemblyID = assembly_id;
END IF;


SET l = JSON_LENGTH(jdata);
WHILE i < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', i,']'));
	
	SET item_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_id'));
	SET item_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_qty'));
	
	SET detail_id = (SELECT I_AssemblyDetailID 
					FROM i_assemblydetail
					WHERE I_AssemblyDetailM_ItemID = item_id AND I_AssemblyDetailIsActive = "Y" AND I_AssemblyDetailI_AssemblyID = assembly_id);
					
	IF detail_id IS NULL THEN
		INSERt INTO i_assemblydetail(
			I_AssemblyDetailI_AssemblyID,
			I_AssemblyDetailM_ItemID,
			I_AssemblyDetailQty
		)
		SELECT assembly_id, item_id, item_qty
		FROM m_item
		WHERE M_ItemID = item_id;

		SET detail_id = (SELECT LAST_INSERT_ID());

		UPDATE i_stock
			SET I_StockQty = I_StockQty - item_qty,
			I_StockLastTransCode = 'INV.ASSEMBLY.OUT',
			I_StockLastTransRefID = detail_id,
			I_StockLastTransQty = item_qty,
			I_StockUserID = uid
		WHERE I_StockM_ItemID = item_id
		AND I_StockIsActive = "Y"
        AND I_StockM_WarehouseID = warehouse_id;

	ELSE
		UPDATE i_assemblydetail
		SET I_AssemblyDetailQty = item_qty WHERE I_AssemblyDetailID = detail_id;
	ENd IF;
	
	SET i = i + 1;
END WHILE;

UPDATE i_stock
	SET I_StockQty = I_StockQty + to_item_qty,
	I_StockLastTransCode = 'INV.ASSEMBLY.IN',
	I_StockLastTransRefID = assembly_id,
	I_StockLastTransQty = to_item_qty,
	I_StockUserID = uid
WHERE I_StockM_ItemID = to_item_id
AND I_StockIsActive = "Y"
AND I_StockM_WarehouseID = warehouse_id;

SET minus_cnt = (
	SELECT COUNT(I_StockID)
	FROM i_stock
	JOIN i_assemblydetail
		ON I_AssemblyDetailM_ItemID = item_id 
		AND I_AssemblyDetailIsActive = "Y" 
		AND I_AssemblyDetailI_AssemblyID = assembly_id
	WHERE I_StockIsActive = "Y"
		AND I_StockM_WarehouseID = warehouse_id
		AND I_StockQty < 0);

IF minus_cnt IS NOT NULL AND minus_cnt > 0 THEN
	SELECT "ERR" status, "Cek kembali stok item anda :)" message;
	ROLLBACK;

ELSE
	SELECT "OK" status, JSON_OBJECT("assembly_id", assembly_id) data;
	COMMIT;
END IF;


END