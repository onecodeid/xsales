BEGIN

DECLARE items VARCHAR(255);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE tmp VARCHAR(255);
DECLARE item_id INTEGEr;
DECLARE item_qty DOUBLE;

DECLARE warehouse_id INTEGER;
DECLARE out_item_id INTEGER;
DECLARE out_item_qty DOUBLE;

DECLARE cnt_minus INTEGER;

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

SELECT I_AssemblyM_WarehouseID, I_AssemblyOutM_ItemID, I_AssemblyOutQty
INTO warehouse_id, out_item_id, out_item_qty
FROM i_assembly WHERE I_AssemblyID = assemblyid;
SET items = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("item_id", I_AssemblyDetailM_ItemID, "item_qty", I_AssemblyDetailQty)), "]")
    FROM i_assemblydetail WHERE I_AssemblyDetailI_AssemblyID = assemblyid AND I_AssemblyDetailIsActive = "Y" );

SET l = JSON_LENGTH(items);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(items, CONCAT('$[', n, ']'));
    SET item_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_id'));
    SET item_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item_qty'));

    UPDATE i_stock
	SET I_StockQty = I_StockQty + item_qty,
        I_StockLastTransCode = 'INV.ASSEMBLY.CANCEL',
        I_StockLastTransRefID = assemblyid,
        I_StockLastTransQty = item_qty,
        I_StockUserID = uid
    WHERE I_StockM_ItemID = item_id
    AND I_StockIsActive = "Y"
    AND I_StockM_WarehouseID = warehouse_id;

    SET n = n + 1;
END WHILE;

UPDATE i_stock
SET I_StockQty = I_StockQty - out_item_qty,
    I_StockLastTransCode = 'INV.ASSEMBLY.CANCEL',
    I_StockLastTransRefID = assemblyid,
    I_StockLastTransQty = (0 - out_item_qty),
    I_StockUserID = uid
WHERE I_StockM_ItemID = out_item_id
AND I_StockIsActive = "Y"
AND I_StockM_WarehouseID = warehouse_id;

SET cnt_minus = (SELECT COUNT(I_StockID) FROM i_assemblydetail 
    JOIN i_stock ON I_StockM_ItemID = I_AssemblyDetailM_ItemID
        AND I_StockM_WarehouseID = warehouse_id AND I_StockIsActive = "Y"
        AND I_StockQty < 0
    WHERE I_AssemblyDetailI_AssemblyID = assemblyid AND I_AssemblyDetailIsActive = "Y"
    );

IF cnt_minus IS NULL OR cnt_minus = 0 THEN
    UPDATE i_assembly
    SET I_AssemblyIsActive = "N" WHERE I_AssemblyID = assemblyid;

    UPDATE i_assemblydetail
    SET I_AssemblyDetailIsActive = "N" WHERE I_AssemblyDetailI_AssemblyID = assemblyid;

    SELECT "OK" as status, JSON_OBJECT("assembly_id", assemblyid) as data;
    COMMIT;
ELSE
    SELECT "ERR" status, "Produk output sudah kehabisan stok, tidak bisa menghapus" message;
    ROLLBACK;
END IF;

END