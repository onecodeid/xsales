BEGIN

DECLARE sales_number VARCHAR(25);
DECLARE sales_id INTEGER;
DECLARE used CHAR(1);

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

SET sales_id = (SELECT L_SalesID FROM l_sales WHERE L_SalesL_OfferID = offerid AND L_SalesIsActive = "Y" LIMIT 1);

IF sales_id IS NOT NULL THEN

    SELECT "ERR" status, "Penawaran tersebut sudah dikonversikan menjadi penjualan" message;
    ROLLBACK;
ELSE

    -- INSERT SALES
    SET sales_number = (SELECT fn_numbering('SO'));
    INSERT INTO l_sales(
        L_SalesDate,
        L_SalesNumber,
        L_SalesRef,
        L_SalesL_OfferID,
        L_SalesM_CustomerID,
        L_SalesTotal,
        L_SalesIncludePPN,
        L_SalesShipping,
        L_SalesGrandTotal,
        L_SalesNote,
        L_SalesM_TermID,
        L_SalesS_StaffID)
    SELECT date(now()), sales_number, '', L_OfferID, L_OfferM_CustomerID, L_OfferTotal, L_OfferIncludePPN, 
        L_OfferShipping, L_OfferGrandTotal, L_OfferNote, L_OfferM_TermID, L_OfferS_StaffID
    FROM l_offer WHERE L_OfferID = offerid;

    SET sales_id = (SELECT LAST_INSERT_ID());

    -- INSERT DETAIL
    INSERT INTO l_salesdetail(
        L_SalesDetailL_SalesID,
        L_SalesDetailA_ItemID,
        L_SalesDetailQty,
        L_SalesDetailPrice,
        L_SalesDetailDisc,
        L_SalesDetailDiscRp,
        L_SalesDetailSubTotal,
        L_SalesDetailPPN,
        L_SalesDetailPPNAmount,
        L_SalesDetailTotal,
        L_SalesDetailHPP,
        L_SalesDetailUnSent,
        L_SalesDetailNote
    )
    SELECT sales_id, L_OfferDetailA_ItemID, L_OfferDetailQty, L_OfferDetailPrice, L_OfferDetailDisc, L_OfferDetailDiscRp,
        L_OfferDetailSubTotal, L_OfferDetailPPN, L_OfferDetailPPNAmount, L_OfferDetailTotal, M_ItemDefaultHPP, L_OfferDetailQty, 
        L_OfferDetailNote
    FROM l_offerdetail JOIN m_item ON L_OfferDetailA_ItemID = M_ItemID
    WHERE L_OfferDetailL_OfferID = offerid AND L_OfferDetailIsActive = "Y" AND L_OfferDetailA_ItemID <> 0;

    -- UPDATE OFFER
    UPDATE l_offer SET L_OfferUsed = "Y" WHERE L_OfferID = offerid;
    UPDATE l_offerdetail SET L_OfferDetailDone = "Y"
    WHERE L_OfferDetailL_OfferID = offerid AND L_OfferDetailIsActive = "Y" AND L_OfferDetailA_ItemID <> 0;

    SELECT "OK" status, JSON_OBJECT("sales_id", sales_id, "sales_number", sales_number) data;
    COMMIT;
END IF;


END