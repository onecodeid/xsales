DROP PROCEDURE `sp_offer_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_offer_save` (IN `pid` int, IN `hdata` text, IN `jdata` text, IN `uid` int)
BEGIN

DECLARE pdate DATE;
DECLARE pnumber VARCHAR(25);
DECLARE pppn CHAR(1) DEFAULT "Y";
DECLARE pnote text;
DECLARE pmemo VARCHAR(1000);
DECLARE pcustomer INTEGER;
DECLARE pcustomer_name VARCHAR(100);
DECLARE pleadtype INTEGER;
DECLARE ptotal DOUBLE DEFAULT 0;
DECLARE pshipping DOUBLE DEFAULT 0;
DECLARE pstaff DOUBLE DEFAULT 0;
DECLARE ppayment INTEGER;
DECLARE pterm INTEGER;
DECLARE pfranco VARCHAR(100);
DECLARE pstock VARCHAR(255);
DECLARE pdelivery VARCHAR(100);
DECLARE pvalidity VARCHAR(255);

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
DECLARE d_other CHAR(1);
DECLARE d_other_name VARCHAR(100);
DECLARE d_pack INTEGER;
DECLARE d_unit INTEGER;
DECLARE d_price DOUBLE;
DECLARE d_qty DOUBLE;
DECLARE d_disc DOUBLE;
DECLARE d_discrp DOUBLE;
DECLARE d_ppn CHAR(1) DEFAULT "N";
DECLARE d_ppn_amount DOUBLE;
DECLARE d_subtotal DOUBLE;
DECLARE d_total DOUBLE;
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(1000) DEFAULT "";

DECLARE xppn DOUBLE DEFAULT 0.1;

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

SET xppn = (SELECT fn_conf('ppn')) / 100;

SET pdate = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_date"));
SET pnumber = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_number"));
SET ptotal = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_total"));
SET pshipping = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_shipping"));
SET pppn = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ppn"));
SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));
SET pmemo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_memo"));
SET pcustomer = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_customer"));
SET pcustomer_name = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_customer_name"));
SET pleadtype = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_leadtype"));
SET pstaff = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_staff"));
SET ppayment = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_payment"));
SET pterm = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_term"));
SET pfranco = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_franco"));
SET pstock = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_stock"));
SET pdelivery = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_delivery"));
SET pvalidity = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_validity"));

IF ppayment IS NULL THEN SET ppayment = 0; END IF;
IF pterm IS NULL THEN SET pterm = 0; END IF;
IF pfranco IS NULL THEN SET pfranco = ""; eNd IF;
IF pstock IS NULL THEN SET pstock = ""; eNd IF;
IF pdelivery IS NULL THEN SET pdelivery = ""; END IF;
IF pvalidity IS NULL THEN SET pvalidity = ""; END IF;
IF pshipping IS NULL THEN SET pshipping = 0; END IF;

IF pid = 0 THEN
    
--    SET pnumber = (SELECT fn_numbering("SF"));
    INSERT INTO l_offer(L_OfferDate,
        L_OfferNumber,
        L_OfferM_CustomerID,
        L_OfferM_CustomerName,
        L_OfferM_LeadTypeID,
        L_OfferTotal,
        L_OfferShipping,
        L_OfferIncludePPN,
        L_OfferNote,
        L_OfferMemo,
        L_OfferS_StaffID,
        L_OfferM_PaymentPlanID,
        L_OfferM_TermID,
        L_OfferFranco,
        L_OfferDelivery,
        L_OfferValidity,
        L_OfferStockNote,
        L_OfferUID)
    SELECT pdate, pnumber, pcustomer, pcustomer_name, pleadtype, ptotal, pshipping, pppn, pnote, pmemo, pstaff, ppayment, pterm, pfranco, pdelivery, pvalidity, pstock, uid;

    SET pid = (SELECT LAST_INSERT_ID());
    CALL sp_log_activity("CREATE", "SALES.OFFER", pid, uid);
ELSE

    UPDATE l_offer
    SET L_OfferNumber = pnumber, L_OfferM_CustomerID = pcustomer, L_OfferM_CustomerName = pcustomer_name, L_OfferDate = pdate, L_OfferTotal = ptotal, L_OfferShipping = pshipping, L_OfferIncludePPN = pppn, L_OfferNote = pnote, L_OfferMemo = pmemo, 
    L_OfferS_StaffID = pstaff, L_OfferM_PaymentPlanID = ppayment, L_OfferM_TermID = pterm, L_OfferFranco = pfranco, L_OfferDelivery = pdelivery,
    L_OfferM_LeadTypeID = pleadtype, L_OfferValidity = pvalidity, L_OfferStockNote = pstock, L_OfferUID = uid
    WHERE L_OfferID = pid;

    UPDATE l_offerdetail
    SET L_OfferDetailIsActive = "O"
    WHERE L_OfferDetailL_OfferID = pid AND L_OfferDetailIsActive = "Y";
    CALL sp_log_activity("MODIFY", "SALES.OFFER", pid, uid);
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemid'));
    SET d_other = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.other'));
    SET d_other_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.other_name'));
    SET d_pack = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.pack'));
    SET d_unit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.unit'));
	SET d_price = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.price'));
    SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
	SET d_disc = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.disc'));
    SET d_discrp = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.discrp'));
	SET d_ppn = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.ppn'));
    SET d_total = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.subtotal'));
    SET d_subtotal = ((d_price * (100-d_disc) / 100) - d_discrp) * d_qty;

    IF d_other IS NULL THEN SET d_other = "N"; eND IF;
    IF d_other_name IS NULL THEN SET d_other_name = ""; eND IF;
    IF d_pack IS NULL THEN SET d_pack = 0; eND IF;
    IF d_unit IS NULL THEN SET d_unit = 0; eND IF;

    
    

    IF d_ppn IS NULL THEN SET d_ppn = "N"; END IF;
    IF d_ppn = "Y" AND pppn = "N" THEN
        SET d_ppn_amount = (d_subtotal * xppn);
        SET d_total = d_subtotal + d_ppn_amount;
    ELSEIF d_ppn = "Y" AND pppn = "Y" THEN
        SET d_ppn_amount = (d_subtotal / (1+xppn));
        SET d_total = d_subtotal;
        SET d_subtotal = d_subtotal - d_ppn_amount;
    ELSE
        SET d_ppn_amount = 0;
        SET d_total = d_subtotal;
    END IF;

    
    

    IF d_other <> "Y" THEN
        SET d_id = (SELECT L_OfferDetailID FROM l_offerdetail WHERE L_OfferDetailIsActive = "O" AND L_OfferDetailA_ItemID = d_item AND L_OfferDetailL_OfferID = pid AND L_OfferDetailOther <> "Y");
    ELSE
        SET d_id = (SELECT L_OfferDetailID FROM l_offerdetail WHERE L_OfferDetailIsActive = "O" AND L_OfferDetailL_OfferID = pid AND L_OfferDetailOther = "Y" LIMIT 1);
    END IF;

    IF d_id IS NULL THEN
        INSERT INTO l_offerdetail(
            L_OfferDetailL_OfferID,
            L_OfferDetailA_ItemID,
            L_OfferDetailOther,
            L_OfferDetailOtherName,
            L_OfferDetailM_PackID,
            L_OfferDetailM_UnitID,
            L_OfferDetailQty,
            L_OfferDetailPrice,
            L_OfferDetailDisc,
            L_OfferDetailDiscRp,
            L_OfferDetailSubTotal,
            L_OfferDetailPPN,
            L_OfferDetailPPNAmount,
            L_OfferDetailTotal
        )
        SELECT pid, d_item, d_other, d_other_name, d_pack, d_unit, d_qty, d_price, d_disc, d_discrp, d_subtotal,d_ppn, d_ppn_amount, d_total;

        SET d_id = (SELECT LAST_INSERT_ID());
    ELSE
        UPDATE l_offerdetail
        SET L_OfferDetailA_ItemID = d_item, L_OfferDetailOther = d_other, L_OfferDetailOtherName = d_other_name, L_OfferDetailQty = d_qty, L_OfferDetailPrice = d_price, L_OfferDetailDisc = d_disc, L_OfferDetailDiscRp = d_discrp, L_OfferDetailSubTotal = d_subtotal, L_OfferDetailPPN = d_ppn, L_OfferDetailPPNAmount = d_ppn_amount, L_OfferDetailTotal = d_total, L_OfferDetailIsActive = "Y",
        L_OfferDetailM_PackID = d_pack, L_OfferDetailM_UnitID = d_unit
        WHERE L_OfferDetailID = d_id;


    END IF;

    SET n = n + 1;
    SET ptotal = ptotal + d_total;
END WHILE;

UPDATE l_offerdetail
SET L_OfferDetailIsActive = "N"
WHERE L_OfferDetailL_OfferID = pid
AND L_OfferDetailIsActive = "O" ;

UPDATE l_offer SET L_OfferTotal = ptotal, L_OfferGrandTotal = ptotal + pshipping WHERE L_OfferID = pid;

SELECT "OK" as status, JSON_OBJECT("sales_id", pid) as data;

COMMIT;

END;;
DELIMITER ;