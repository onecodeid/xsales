BEGIN

DECLARE deliveryid INTEGER;

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


IF invoiceid = 0 THEN
    -- INSERT INTO DELIVERY
    INSERT INTO l_delivery(
        L_DeliveryM_CustomerID,
        L_DeliveryS_StaffID,
        L_DeliveryProforma
    )
    SELECT L_SalesM_CustomerID, uid, "Y"
    FROM l_sales WHERE L_SalesID = salesid;

    SET deliveryid = (SELECT LAST_INSERT_ID());

    INSERT INTO l_deliverydetail(
        L_DeliveryDetailL_DeliveryID,
        L_DeliveryDetailL_SalesDetailID,
        L_DeliveryDetailA_ItemID,
        L_DeliveryDetailQty,
        L_DeliveryDetailNote)
    SELECT deliveryid, l_salesdetailid, l_salesdetaila_itemid, l_salesdetailqty, l_salesdetailnote
    FROM l_salesdetail
    WHERE L_SalesDetailL_SalesID = salesid AND L_SalesDetailIsActive = "Y";
    -- END OF INSERT DELIVERY
    
    -- INSERT INTO INVOICE
    CALL sp_invoice_save_notrans(invoiceid, hdata, JSON_ARRAY(deliveryid), uid);
    SET invoiceid = (SELECT L_InvoiceID FROM l_invoice WHERE L_InvoiceIsActive = "Y" ORDER BY L_InvoiceID DESC LIMIT 1);
    -- END OF INSERT INVOICE

    UPDATE l_sales
    SET L_SalesProforma = "Y", L_SalesL_InvoiceID = invoiceid
    WHERE L_SalesID = salesid;
    
ELSE
    SET deliveryid = (SELECT L_InvoiceDetailL_DeliveryID FROM l_invoicedetail 
                    WHERE L_InvoiceDetailL_InvoiceID = invoiceid
                    AND L_InvoiceDetailIsActive = "Y" LIMIT 1);
    CALL sp_invoice_save_notrans(invoiceid, hdata, JSON_ARRAY(deliveryid), uid);
END IF;

COMMIT;


eND