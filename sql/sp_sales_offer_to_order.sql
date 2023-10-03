BEGIN

DECLARE p_used CHAR(1);
DECLARE p_number VARCHAR(25);
DECLARE sales_id INTEGER;

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

SET p_used = (SELECT L_OfferUsed FROM l_offer WHERE L_OfferID = offerid);
IF p_used = "Y" THEN
    SELECT "ERR" status, "Penawaran tersebut telah dibuat Order, mohon cek kembali :)" message;
    ROLLBACK;

ELSE
    SET p_number = (SELECT fn_numbering('SO'));
    INSERT INTO l_sales(L_SalesDate,
        L_SalesNumber,
        L_SalesM_CustomerID,
        L_SalesTotal,
        L_SalesIncludePPN,
        L_SalesNote,
        L_SalesMemo,
        L_SalesS_StaffID,
        L_SalesL_OfferID)
    SELECT date(now()), p_number, L_OfferM_CustomerID, L_OfferTotal, L_OfferIncludePPN, 
        L_OfferNote, L_OfferMemo, L_OfferS_StaffID, offerid
    FROM l_offer WHERE L_OfferID = offerid;

    SET sales_id = (SELECT LAST_INSERT_ID());
    
    INSERT INTO l_salesdetail(
        L_SalesDetailL_SalesID,
        L_SalesDetailA_ItemID,
        L_SalesDetailQty,
        L_SalesDetailPrice,
        L_SalesDetailDisc,
        L_SalesDetailSubTotal,
        L_SalesDetailPPN,
        L_SalesDetailPPNAmount,
        L_SalesDetailTotal,
        L_SalesDetailHPP
    )
    SELECT sales_id, L_OfferDetailA_ItemID, L_OfferDetailQty, L_OfferDetailPrice, L_OfferDetailDisc, 
    L_OfferDetailSubTotal, L_OfferDetailPPN, L_OfferDetailPPNAmount, L_OfferDetailTotal, M_ItemDefaultHPP
    FROM l_offerdetail
    JOIN m_item ON L_OfferDetailM_ItemID = M_ItemID
    WHERE L_OfferDetailL_OfferID = offerid AND L_OfferDetailIsActive = "Y";

    UPDATE l_offer SET L_OfferUsed = "Y" WHERE L_OfferID = offerid;

    COMMIT;
    
END IF;
-- L_OfferDate, L_OfferNumber, L_OfferM_CustomerID, L_OfferTotal, L_OfferIncludePPN
-- L_OfferM_PaymentPlanID, L_OfferFranco, L_OfferDelivery, L_OfferDone, L_OfferNote
-- L_OfferS_StaffID, L_OfferMemo, L_OfferStatus, L_OfferUsed

-- L_OfferDetailL_OfferID, L_OfferDetailA_ItemID, L_OfferDetailQty, L_OfferDetailPrice, L_OfferDetailDisc,  	
-- L_OfferDetailDiscRp, L_OfferDetailSubTotal, L_OfferDetailPPN, L_OfferDetailPPNAmount,  	
-- L_OfferDetailTotal, L_OfferDetailSent, L_OfferDetailUnSent, L_OfferDetailDone, L_OfferDetailNote

-- L_SalesDate, L_SalesNumber, L_SalesL_OfferID, L_SalesM_CustomerID, L_SalesTotal, L_SalesTotalHPP, L_SalesIncludePPN
-- L_SalesDone, L_SalesNote, L_SalesS_StaffID, L_SalesMemo, L_SalesStatus

-- L_SalesDetailL_SalesID, L_SalesDetailA_ItemID L_SalesDetailQty, L_SalesDetailPrice, L_SalesDetailDisc, L_SalesDetailDiscRp
-- L_SalesDetailSubTotal, L_SalesDetailPPN, L_SalesDetailPPNAmount, L_SalesDetailTotal, L_SalesDetailHPP, L_SalesDetailSent
-- L_SalesDetailUnSent, L_SalesDetailDone, L_SalesDetailNote

END