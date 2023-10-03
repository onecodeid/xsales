BEGIN 

DECLARE item_code VARCHAR(50);
DECLARE item_name VARCHAR(100);
DECLARE item_alias VARCHAR(100);
DECLARE item_min_stock DOUBLE;
DECLARE item_unit INTEGER;
DECLARE item_pack INTEGER;
DECLARE item_hpp DOUBLE;
DECLARE item_price DOUBLE;
DECLARE item_category INTEGER;
DECLARE item_viewinpack CHAR(1);

DECLARE l INTEGER;
DECLARE n INTEGER DEFAULT 0;
DECLARE tmp VARCHAR(100);
DECLARE alias_customer INTEGER;
DECLARE alias_name VARCHAR(100);
DECLARE alias_id INTEGER;

DECLARE pack_customer INTEGER;
DECLARE pack_pack INTEGER;
DECLARE pack_id INTEGER;

DECLARE stockmin_warehouse INTEGER;
DECLARE stockmin_qty DOUBLE;
DECLARE stockmin_id INTEGER;

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

SET item_code = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.code"));
SET item_name = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.name"));
SET item_alias = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.alias"));
SET item_min_stock = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.min_stock"));
SET item_unit = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.unit"));
SET item_pack = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.pack"));
SET item_hpp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.hpp"));
SET item_price = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.price"));
SET item_category = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.category"));
SET item_viewinpack = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.viewinpack"));

IF item_code IS NULL THEN SET item_code = ""; END IF;
IF item_alias IS NULL THEN SET item_alias = ""; END IF;
IF item_min_stock IS NULL THEN SET item_min_stock = 0; END IF;
IF item_unit IS NULL THEN SET item_unit = 0; END IF;
IF item_pack IS NULL THEN SET item_pack = 0; END IF;
IF item_category IS NULL THEN SET item_category = 0; END IF;
IF item_price IS NULL THEN SET item_price = 0; END IF;
IF item_hpp IS NULL THEN SET item_hpp = 0; END IF;
IF item_viewinpack IS NULL THEN SET item_viewinpack = "N"; END IF;

IF item_id = 0 THEN
    INSERT INTO m_item(
        M_ItemM_CategoryID,
        M_ItemCode,
        M_ItemName,
        M_ItemAlias,
        M_ItemMinStock,
        M_ItemM_UnitID,
        M_ItemM_PackID,
        M_ItemDefaultHPP,
        M_ItemDefaultPrice,
        M_ItemViewInPack)
    SELECT item_category,
        item_code,
        item_name,
        item_alias,
        item_min_stock,
        item_unit,
        item_pack,
        item_hpp,
        item_price,
        item_viewinpack;
    SET item_id = (SELECT LAST_INSERT_ID());

    INSERt INTO i_stock(
        I_StockM_WarehouseID,
        I_StockM_ItemID,
        I_StockQty,
        I_StockLastTransCode,
        I_StockLastTransRefID,
        I_StockLastTransQty
    )
    SELECT M_WarehouseID, item_id, 0, "", 0, 0
    FROM m_warehouse
    WHERE M_WarehouseIsActive = "Y";


ELSE
    UPDATE m_item
    SET M_ItemM_CategoryID = item_category,
        M_ItemCode = item_code,
        M_ItemName = item_name,
        M_ItemAlias = item_alias,
        M_ItemMinStock = item_min_stock,
        M_ItemM_UnitID = item_unit,
        M_ItemM_PackID = item_pack,
        M_ItemDefaultHPP = item_hpp,
        M_ItemDefaultPrice = item_price,
        M_ItemViewInPack = item_viewinpack
    WHERE M_ItemID = item_id;
END IF;


-- ITEM ALIASES
-- ------------
UPDATE m_itemalias
SET M_ItemAliasIsActive = "O"
WHERE M_ItemAliasIsActive = "Y"
AND M_ItemAliasM_ItemID = item_id;

SET l = JSON_LENGTH(aliases);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(aliases, CONCAT("$[", n, "]"));

    SET alias_customer = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.customer"));
    SET alias_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.name"));

    SET alias_id = (SELECT M_ItemAliasID FROM m_itemalias WHERE M_ItemAliasM_ItemID = item_id aNd M_ItemAliasIsActive = "O" AND M_ItemAliasM_CustomerID = alias_customer);

    IF alias_id IS NOT NULL tHEN
        UPDATE m_itemalias SET M_ItemAliasIsActive = "Y" , M_ItemAliasName = alias_name WHERe M_ItemAliasID = alias_id;
    ELSE

        INSERT INTO m_itemalias(M_ItemAliasM_customerID, M_ItemAliasM_ItemID, M_ItemAliasName) SELECT alias_customer, item_id, alias_name;
    END IF;

    SET n = n + 1;
END WHILE;

UPDATE m_itemalias SET M_ItemAliasIsACtive = "N" WHERe M_ItemAliasIsActive = "O" and M_ItemAliasM_ItemID = item_id;

-- ITEM PACKS
-- ----------
UPDATE m_itempack
SET M_ItemPackIsActive = "O"
WHERE M_ItemPackIsActive = "Y"
AND M_ItemPackM_ItemID = item_id;

SET l = JSON_LENGTH(packs);
SET n = 0;
WHILE n < l DO
    SET tmp = JSON_EXTRACT(packs, CONCAT("$[", n, "]"));

    SET pack_customer = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.customer"));
    SET pack_pack = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.pack"));

    SET pack_id = (SELECT M_ItemPackID FROM m_itempack WHERE M_ItemPackM_ItemID = item_id aNd M_ItemPackIsActive = "O" AND M_ItemPackM_CustomerID = pack_customer);

    IF pack_id IS NOT NULL tHEN
        UPDATE m_itempack SET M_ItemPackIsActive = "Y" , M_ItemPackM_PackID = pack_pack WHERe M_ItemPackID = pack_id;
    ELSE

        INSERT INTO m_itempack(M_ItemPackM_customerID, M_ItemPackM_ItemID, M_ItemPackM_PackID) SELECT pack_customer, item_id, pack_pack;
    END IF;

    SET n = n + 1;
END WHILE;

UPDATE m_itempack SET M_ItemPackIsACtive = "N" WHERe M_ItemPackIsActive = "O" and M_ItemPackM_ItemID = item_id;

-- ITEM STOKMINS
-- -------------

-- UPDATE i_stock
-- SET I_StockIsActive = "O"
-- WHERE I_StockIsActive = "Y"
-- AND I_StockM_ItemID = item_id;

SET l = JSON_LENGTH(stockmins);
SET n = 0;
WHILE n < l DO
    SET tmp = JSON_EXTRACT(stockmins, CONCAT("$[", n, "]"));

    SET stockmin_warehouse = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.warehouse"));
    SET stockmin_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.qty"));

    SET stockmin_id = (SELECT I_StockID FROM i_stock WHERE I_StockM_ItemID = item_id aNd I_StockIsActive = "Y" AND I_StockM_WarehouseID = stockmin_warehouse);

    IF stockmin_id IS NOT NULL tHEN
        UPDATE i_stock SET I_StockIsActive = "Y" , I_StockMinQty = stockmin_qty WHERe I_StockID = stockmin_id;
    -- ELSE

    --    INSERT INTO m_itempack(M_ItemPackM_customerID, M_ItemPackM_ItemID, M_ItemPackM_PackID) SELECT pack_customer, item_id, pack_pack;
    END IF;

    SET n = n + 1;
END WHILE;

-- UPDATE m_itempack SET M_ItemPackIsACtive = "N" WHERe M_ItemPackIsActive = "O" and M_ItemPackM_ItemID = item_id;




SELECT "OK" status, JSON_OBJECT("item_id", item_id) data;

COMMIT;

END