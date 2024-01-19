DROP PROCEDURE `sp_sales_retur_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_sales_retur_delete` (IN `retur_id` int, IN `uid` int)
BEGIN

DECLARE done BOOLEAN DEFAULT FALSE;

DECLARE sales_id INTEGER;
DECLARE warehouse_id INTEGER;
DECLARE is_active CHAR(1);
DECLARE d_id INTEGER;
DECLARE d_item_id INTEGER;
DECLARE d_qty DOUBLE;
DECLARE d_sales_detail_id INTEGER;

-- Declare the cursor
DECLARE retur_cursor CURSOR FOR
    SELECT L_ReturDetailID, L_ReturDetailM_ItemID, L_ReturDetailQty, L_ReturDetailL_SalesDetailID
    FROM l_returdetail WHERE L_ReturDetailL_ReturID = retur_id AND L_ReturDetailISACtive = "Y";

-- Declare continue handler to exit the loop
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
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

SELECT L_ReturL_SalesID, L_ReturM_WarehouseID, L_ReturIsActive
INTO sales_id, warehouse_id, is_active
FROM l_retur WHERE L_ReturID = retur_id;

IF is_active <> "Y" THEN
    SELECT "ERR" as status, "Retur tersebut sudah dihapus :(" as message;
    ROLLBACK;

ELSE

    UPDATE l_retur
    SET L_ReturIsActive = "N"
    WHERE L_ReturID = retur_id;

    -- Open the cursor
    OPEN retur_cursor;
        
        -- Start fetching and processing records
        retur_loop: LOOP
            -- Fetch the next record into variables
            FETCH retur_cursor INTO d_id, d_item_id, d_qty, d_sales_detail_id;

            -- Check if no more records
            IF done THEN
                LEAVE retur_loop;
            END IF;

            UPDATE l_salesdetail SET L_SalesDetailReturQty = L_SalesDetailReturQty - d_qty,
                    L_SalesDetailReturNominal = L_SalesDetailReturNominal - (d_qty * L_SalesDetailPrice * (100-L_SalesDetailDisc) / 100)
                WHERE L_SalesDetailID = d_sales_detail_id;

            UPDATE l_returdetail
            SET L_ReturDetailIsActive = "N"
            WHERE L_ReturDetailID = d_id;

            UPDATE i_stock
                SET I_StockQty = I_StockQty - d_qty,
                    I_StockLastTransCode = "SALES.RETUR.DELETE",
                    I_StockLastTransDate = now(),
                    I_StockLastTransRefID = d_id,
                    I_StockLastTransQty = (0-d_qty),
                    I_StockLastTransCreated = now()
                WHERE I_StockM_ItemID = d_item_id AND I_StockM_WarehouseID = warehouse_id AND I_StockIsActive = "Y";
        END LOOP;

    -- Close the cursor
    CLOSE retur_cursor;

    SELECT "OK" as status, JSON_OBJECT("retur_id", retur_id) as data;
    COMMIT;

END IF;
END