BEGIN

DECLARE t_sales INTEGER;
DECLARE t_retur INTEGER;
DECLARE t_offer INTEGER;
DECLARE t_pay INTEGER;
DECLARE t_adjust INTEGER;
DECLARE t_stock INTEGER;
DECLARE t_item INTEGER;
DECLARE t_vendor INTEGER;
DECLARE t_customer INTEGER;

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

SET t_sales = JSON_UNQUOTE(JSON_EXTRACT(w, "$.sales"));
SET t_retur = JSON_UNQUOTE(JSON_EXTRACT(w, "$.retur"));
SET t_offer = JSON_UNQUOTE(JSON_EXTRACT(w, "$.offer"));
SET t_pay = JSON_UNQUOTE(JSON_EXTRACT(w, "$.pay"));
SET t_adjust = JSON_UNQUOTE(JSON_EXTRACT(w, "$.adjust"));
SET t_stock = JSON_UNQUOTE(JSON_EXTRACT(w, "$.stock"));
SET t_item = JSON_UNQUOTE(JSON_EXTRACT(w, "$.item"));
SET t_customer = JSON_UNQUOTE(JSON_EXTRACT(w, "$.customer"));
SET t_vendor = JSON_UNQUOTE(JSON_EXTRACT(w, "$.vendor"));

IF (t_sales = 1) THEN
    TRUNCATE table l_sales;
    TRUNCATE table l_salesdetail;
    INSERT INTO s_erase(S_EraseTable) VALUES("sales");
END IF;

IF (t_retur = 1) THEN
    TRUNCATE table l_retur;
    TRUNCATE table l_returdetail;
    INSERT INTO s_erase(S_EraseTable) VALUES("retur");
END IF;

IF (t_offer = 1) THEN
    TRUNCATE table l_offer;
    TRUNCATE table l_offerdetail;
    INSERT INTO s_erase(S_EraseTable) VALUES("offer");
END IF;

IF (t_pay = 1) THEN
    TRUNCATE table f_spay;
    INSERT INTO s_erase(S_EraseTable) VALUES("pay");
END IF;

IF (t_adjust = 1) THEN
    TRUNCATE table i_adjust;
    TRUNCATE table i_adjustdetail;
    INSERT INTO s_erase(S_EraseTable) VALUES("adjust");
END IF;

IF (t_item = 1) THEN
    TRUNCATE table m_item;
    INSERT INTO s_erase(S_EraseTable) VALUES("item");
END IF;

IF (t_stock = 1) THEN
    TRUNCATE table i_stock;

    INSERt INTO i_stock(
        I_StockM_WarehouseID,
        I_StockM_ItemID,
        I_StockQty,
        I_StockLastTransCode,
        I_StockLastTransRefID,
        I_StockLastTransQty
    )
    SELECT M_WarehouseID, M_ItemID, 0, "", 0, 0
    FROM m_warehouse
    JOIN m_item ON M_ItemIsActive = "Y"
    WHERE M_WarehouseIsActive = "Y";

    INSERT INTO s_erase(S_EraseTable) VALUES("stock");
END IF;

IF (t_customer = 1) THEN
    TRUNCATE table m_customer;
    INSERT INTO s_erase(S_EraseTable) VALUES("customer");
END IF;

IF (t_vendor = 1) THEN
    TRUNCATE table m_vendor;
    INSERT INTO s_erase(S_EraseTable) VALUES("vendor");
END IF;

SELECT "OK" as status, w as data;
COMMIT;

END