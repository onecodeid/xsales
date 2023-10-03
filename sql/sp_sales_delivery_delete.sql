BEGIN

DECLARE p_confirm CHAR(1);
DECLARE p_invoice INTEGER;
DECLARE p_warehouse INTEGER;
DECLARE so_id INTEGER;

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

SELECT L_DeliveryConfirm, L_DeliveryL_InvoiceID, L_DeliveryM_WarehouseID
INTO p_confirm, p_invoice, p_warehouse
FROM l_delivery
WHERE L_DeliveryID = deliveryid;

IF p_invoice <> 0 THEN
    SELECT "ERR" status, "Order ini sudah dibuat invoice, tidak bisa dihapus :(";
    ROLLBACK;
ELSE
    IF p_confirm = "Y" THEN

        -- Update stock
        UPDATE i_stock
        JOIN l_deliverydetail ON L_DeliveryDetailA_ItemID = I_StockM_ItemID
            AND L_DeliveryDetailL_DeliveryID = deliveryid
            AND L_DeliveryDetailIsActive = "Y"
        SET I_StockQty = I_StockQty + L_DeliveryDetailQty,
            I_StockLastTransCode = "SALES.DELIVERY.DELETE",
            I_StockLastTransRefID = L_DeliveryDetailID,
            I_StockLastTransQty = L_DeliveryDetailQty
        WHERE I_StockM_WarehouseID = p_warehouse
            AND I_StockIsActive = "Y";
    END IF;

    -- Update Sales Detail
    UPDATE l_salesdetail
    JOIN l_deliverydetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
        AND L_DeliveryDetailL_DeliveryID = deliveryid
        AND L_DeliveryDetailIsActive = "Y"
    SET L_SalesDetailSent = L_SalesDetailSent - L_DeliveryDetailQty;

    -- Update sales done
    SET so_id = (SELECT DISTINCT L_SalesDetailL_SalesID FROM l_deliverydetail JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    WHERE L_DeliveryDetailIsActive = "Y" AND L_DeliveryDetailL_DeliveryID = deliveryid LIMIT 1);
    CALL sp_sales_status_set(so_id);

    UPDATE l_delivery SET L_DeliveryIsActive = "N" WHERE L_DeliveryID = deliveryid;
    UPDATE l_deliverydetail SET L_DeliveryDetailIsActive = "N" WHERE L_DeliveryDetailL_DeliveryID = deliveryid;

    SELECT "OK" status, JSON_OBJECT("delivery_id", deliveryid) data;

    COMMIT;
END IF;



END