BEGIN

DECLARE pdate DATE;
DECLARE pnumber VARCHAR(25);
DECLARE pref VARCHAR(50);
DECLARE pppn CHAR(1) DEFAULT "Y";
DECLARE pnote VARCHAR(1000);
DECLARE pmemo VARCHAR(1000);
DECLARE pcustomer INTEGER;
DECLARE ptotal DOUBLE DEFAULT 0;
DECLARE ptotalhpp DOUBLE DEFAULT 0;
DECLARE pshipping DOUBLE DEFAULT 0;
DECLARE pstaff DOUBLE DEFAULT 0;
DECLARE poffer INTEGER;
DECLARE o_offer INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
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

SET pdate = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_date"));
SET ptotal = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_total"));
SET pshipping = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_shipping"));
SET pppn = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ppn"));
SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));
SET pmemo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_memo"));
SET pref = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ref"));
SET pcustomer = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_customer"));
SET pstaff = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_staff"));
SET poffer = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_offer"));

IF poffer IS NULL THEN SET poffer = 0; END IF;
IF pshipping IS NULL THEN SET pshipping = 0; END IF;

IF pid = 0 THEN
    
    SET pnumber = (SELECT fn_numbering("SO"));
    INSERT INTO l_sales(L_SalesDate,
        L_SalesNumber,
        L_SalesRef,
        L_SalesM_CustomerID,
        L_SalesTotal,
        L_SalesShipping,
        L_SalesIncludePPN,
        L_SalesNote,
        L_SalesMemo,
        L_SalesS_StaffID,
        L_SalesL_OfferID)
    SELECT pdate, pnumber, pref, pcustomer, ptotal, pshipping, pppn, pnote, pmemo, pstaff, poffer;

    SET pid = (SELECT LAST_INSERT_ID());
ELSE
    SET o_offer = (SELECT L_SalesL_OfferID FROM l_sales WHERE L_SalesID = pid);
    UPDATE l_offer SET L_OfferUsed = "N" WHERE L_OfferID = o_offer;
    UPDATE l_sales
    SET L_SalesDate = pdate, L_SalesRef = pref, L_SalesTotal = ptotal, L_SalesShipping = pshipping, L_SalesIncludePPN = pppn, L_SalesNote = pnote, L_SalesMemo = pmemo, L_SalesS_StaffID = pstaff, L_SalesL_OfferID = poffer
    WHERE L_SalesID = pid;
END IF;

UPDATE l_offer SET L_OfferUsed = "Y" WHERE L_OfferID = poffer;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemid'));
	SET d_price = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.price'));
    SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
	SET d_disc = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.disc'));
    SET d_discrp = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.discrp'));
	SET d_ppn = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.ppn'));
    SET d_total = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.subtotal'));
    -- SET d_subtotal = d_price * d_qty * (100-d_disc) / 100;
    SET d_subtotal = ((d_price * (100-d_disc) / 100) - d_discrp) * d_qty;

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

    SET d_id = (SELECT L_SalesDetailID FROM l_salesdetail WHERE L_SalesDetailIsActive = "Y" AND L_SalesDetailA_ItemID = d_item AND L_SalesDetailL_SalesID = pid);

    IF d_id IS NULL THEN
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
            L_SalesDetailHPP
        )
        SELECT pid, d_item, d_qty, d_price, d_disc, d_discrp, d_subtotal,d_ppn, d_ppn_amount, d_total, M_ItemDefaultHPP
        FROM m_item WHERE M_ItemID = d_item;

        SET d_id = (SELECT LAST_INSERT_ID());
    ELSE
        UPDATE l_salesdetail
        SET L_SalesDetailQty = d_qty, L_SalesDetailPrice = d_price, L_SalesDetailDisc = d_disc, L_SalesDetailDiscRp = d_discrp, L_SalesDetailSubTotal = d_subtotal, L_SalesDetailPPN = d_ppn, L_SalesDetailPPNAmount = d_ppn_amount, L_SalesDetailTotal = d_total
        WHERE L_SalesDetailID = d_id;

    END IF;

    IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
    SET n = n + 1;
    SET ptotal = ptotal + d_total;
END WHILE;

UPDATE l_salesdetail
SET L_SalesDetailIsActive = "N"
WHERE L_SalesDetailL_SalesID = pid
AND NOT FIND_IN_SET(L_SalesDetailID, d_ids) AND L_SalesDetailIsActive = "Y" ;

-- TOTAL HPP
SET ptotalhpp = (SELECT SUM(L_SalesDetailHPP)
    FROM l_salesdetail WHERE L_SalesDetailL_SalesID = pid AND L_SalesDetailIsactive = "Y");

UPDATE l_sales SET L_SalesTotal = ptotal, L_SalesTotalHPP = ptotalhpp, L_SalesGrandTotal = ptotal + pshipping WHERE L_SalesID = pid;


SELECT "OK" as status, JSON_OBJECT("sales_id", pid) as data;

COMMIT;

END