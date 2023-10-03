BEGIN

DECLARE pdate DATE;
DECLARE pnumber VARCHAR(25);
DECLARE pppn CHAR(1) DEFAULT "Y";
DECLARE pnote VARCHAR(1000);
DECLARE pmemo VARCHAR(1000);
DECLARE pvendor INTEGER;
DECLARE ptotal DOUBLE DEFAULT 0;
DECLARE psubtotal DOUBLE DEFAULT 0;
DECLARE pppntotal DOUBLE DEFAULT 0;
DECLARE pshipping DOUBLE DEFAULT 0;
DECLARE pdisc DOUBLE DEFAULT 0;
DECLARE pdiscrp DOUBLE DEFAULT 0;
DECLARE pstaff INTEGER;
DECLARE ppayment INTEGER;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
DECLARE d_price DOUBLE;
DECLARE d_qty DOUBLE;
DECLARE d_disc DOUBLE;
DECLARE d_ppn CHAR(1) DEFAULT "N";
DECLARE d_ppn_amount DOUBLE;
DECLARE d_subtotal DOUBLE;
DECLARE d_itemtotal DOUBLE;
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
SET ptotal = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_total"));
SET pppn = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ppn"));
SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));
SET pmemo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_memo"));
SET pvendor = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_vendor"));
SET pstaff = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_staff"));
SET ppayment = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_payment"));
SET pdisc = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_disc"));
SET pdiscrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_discrp"));
SET pshipping = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_shipping"));

IF pid = 0 THEN
    
    SET pnumber = (SELECT fn_numbering("PO"));
    INSERT INTO p_purchase(P_PurchaseDate,
        P_PurchaseNumber,
        P_PurchaseM_VendorID,
        P_PurchaseTotal,
        P_PurchaseDisc,
        P_PurchaseDiscRp,
        P_PurchaseShipping,
        P_PurchaseIncludePPN,
        P_PurchaseNote,
        P_PurchaseMemo,
        P_PurchaseS_StaffID,
        P_PurchaseM_PaymentPlanID)
    SELECT pdate, pnumber, pvendor, ptotal, pdisc, pdiscrp, pshipping, pppn, pnote, pmemo, pstaff, ppayment;

    SET pid = (SELECT LAST_INSERT_ID());
ELSE

    UPDATE p_purchase
    SET P_PurchaseDate = pdate, P_PurchaseTotal = ptotal, P_PurchaseDisc = pdisc, P_PurchaseDiscRp = pdiscrp, P_PurchaseShipping = pshipping, P_PurchaseIncludePPN = pppn, P_PurchaseNote = pnote, P_PurchaseMemo = pmemo,
        P_PurchaseS_StaffID = pstaff, P_PurchaseM_PaymentPlanID = ppayment, P_PurchaseM_VendorID = pvendor
    WHERE P_PurchaseID = pid;

    
    UPDATE p_purchasedetail
    SET P_PurchaseDetailIsActive = "O"
    WHERE P_PurchaseDetailIsActive = "Y"
    AND P_PurchaseDetailP_PurchaseID = pid;
END IF;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemid'));
	SET d_price = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.price'));
    SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
	SET d_disc = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.disc'));
	SET d_ppn = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.ppn'));
    SET d_total = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.subtotal'));
    SET d_subtotal = d_price * d_qty * (100-d_disc) / 100;
    SET d_itemtotal = d_subtotal;

    IF d_ppn IS NULL THEN SET d_ppn = "N"; END IF;
    IF d_ppn = "Y" AND pppn = "N" THEN
        SET d_ppn_amount = (d_subtotal * xppn);
        SET d_total = d_subtotal + d_ppn_amount;
    ELSEIF d_ppn = "Y" AND pppn = "Y" THEN
        SET d_total = d_subtotal;
        SET d_subtotal = (d_subtotal / (1+xppn));
        SET d_ppn_amount = d_total - d_subtotal;
    ELSE
        SET d_ppn_amount = 0;
        SET d_total = d_subtotal;
    END IF;

    SET d_id = (SELECT P_PurchaseDetailID FROM p_purchasedetail WHERE P_PurchaseDetailIsActive = "O" AND P_PurchaseDetailA_ItemID = d_item AND P_PurchaseDetailP_PurchaseID = pid);

    IF d_id IS NULL THEN
        INSERT INTO p_purchasedetail(
            P_PurchaseDetailP_PurchaseID,
            P_PurchaseDetailA_ItemID,
            P_PurchaseDetailQty,
            P_PurchaseDetailPrice,
            P_PurchaseDetailDisc,
            P_PurchaseDetailSubTotal,
            P_PurchaseDetailPPN,
            P_PurchaseDetailPPNAmount,
            P_PurchaseDetailTotal
        )
        SELECT pid, d_item, d_qty, d_price, d_disc, d_subtotal,d_ppn, d_ppn_amount, d_total;

        SET d_id = (SELECT LAST_INSERT_ID());
    ELSE
        UPDATE p_purchasedetail
        SET P_PurchaseDetailQty = d_qty, P_PurchaseDetailPrice = d_price, P_PurchaseDetailDisc = d_disc, P_PurchaseDetailSubTotal = d_subtotal, P_PurchaseDetailPPN = d_ppn, P_PurchaseDetailPPNAmount = d_ppn_amount, P_PurchaseDetailTotal = d_total,
        P_PurchaseDetailIsActive = "Y"
        WHERE P_PurchaseDetailID = d_id;

    END IF;

    IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
    SET n = n + 1;
    SET ptotal = (ptotal + d_total) * ((100 - pdisc)/100);
        SET ptotal = ptotal - pdiscrp + pshipping;
    SET psubtotal = psubtotal + d_subtotal;
    SET pppntotal = pppntotal + d_ppn_amount;
END WHILE;

UPDATE p_purchasedetail
SET P_PurchaseDetailIsActive = "N"
WHERE P_PurchaseDetailP_PurchaseID = pid
AND P_PurchaseDetailIsActive = "O" ;



UPDATE p_purchase SET P_PurchaseSubTotal = psubtotal,
    P_PurchaseTotal = (psubtotal * (100-P_PurchaseDisc) / 100) - P_PurchaseDiscRp 
    WHERE P_PurchaseID = pid;


IF d_ppn <> "Y" THEN
    SET xppn = 0; END IF;

UPDATE p_purchase
SET P_PurchasePPN = P_PurchaseTotal * (xppn), 
    P_PurchaseGrandTotal = (P_PurchaseTotal * (1 + xppn)) + pshipping
WHERE P_PurchaseID = pid;
    






SELECT "OK" as status, JSON_OBJECT("purchase_id", pid) as data;

COMMIT;

END