DROP PROCEDURE `sp_purchase_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_purchase_save` (IN `pid` int, IN `hdata` varchar(2000), IN `jdata` text, IN `uid` int)
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
DECLARE pdp DOUBLE DEFAULT 0;
DECLARE pdpid INTEGER DEFAULT 0;
DECLARE pdpacc CHAR(1);
DECLARE pdppayid INTEGER;
DECLARE pstaff INTEGER;
DECLARE ppayment INTEGER;
DECLARE pterm INTEGER;
DECLARE warehouse_id INTEGER DEFAULT 1;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
DECLARE d_price DOUBLE;
DECLARE d_qty DOUBLE;
DECLARE d_oqty DOUBLE;
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
SET pnumber = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_number"));
SET ptotal = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_total"));
SET pppn = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ppn"));
SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));
SET pmemo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_memo"));
SET pvendor = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_vendor"));
SET pstaff = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_staff"));
SET ppayment = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_payment"));
SET pterm = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_term"));
SET pdisc = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_disc"));
SET pdiscrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_discrp"));
SET pshipping = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_shipping"));
SET pdp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_dp"));

IF pdp IS NULL THEN SET pdp = 0; END IF;

IF pid = 0 THEN
    
    -- SET pnumber = (SELECT fn_numbering("PO"));
    INSERT INTO p_purchase(P_PurchaseDate,
        P_PurchaseNumber,
        P_PurchaseM_VendorID,
        P_PurchaseTotal,
        P_PurchaseDisc,
        P_PurchaseDiscRp,
        P_PurchaseShipping,
        P_PurchaseDp,
        P_PurchaseIncludePPN,
        P_PurchaseNote,
        P_PurchaseMemo,
        P_PurchaseS_StaffID,
        P_PurchaseM_TermID,
        P_PurchaseUID)
    SELECT pdate, pnumber, pvendor, ptotal, pdisc, pdiscrp, pshipping, pdp, pppn, pnote, pmemo, pstaff, pterm, uid;

    SET pid = (SELECT LAST_INSERT_ID());
--    CALL sp_log_activity("CREATE", "PURCHASE.ORDER", pid, uid);
ELSE

    UPDATE p_purchase
    SET P_PurchaseDate = pdate, P_PurchaseNumber = pnumber, P_PurchaseTotal = ptotal, P_PurchaseDisc = pdisc, P_PurchaseDiscRp = pdiscrp, P_PurchaseShipping = pshipping, P_PurchaseDp = pdp, P_PurchaseIncludePPN = pppn, P_PurchaseNote = pnote, P_PurchaseMemo = pmemo,
        P_PurchaseS_StaffID = pstaff, P_PurchaseM_TermID = pterm, P_PurchaseM_VendorID = pvendor
    WHERE P_PurchaseID = pid;
    
    UPDATE p_purchasedetail
    SET P_PurchaseDetailIsActive = "O"
    WHERE P_PurchaseDetailIsActive = "Y"
    AND P_PurchaseDetailP_PurchaseID = pid;

    SET pnumber = (SELECT P_PurchaseNumber FROM p_purchase WHERE P_PurchaseID = pid);
    SET pdpid = (SELECT P_PurchaseF_BillDpID FROM p_purchase WHERE P_PurchaseID = pid);
--    CALL sp_log_activity("MODIFY", "PURCHASE.ORDER", pid, uid);
END IF;



IF pdp > 0 THEN
    IF pdpid = 0 THEN
        INSERT INTO f_pay(F_PayTotal)
        SELECT pdp;

        SET pdppayid = (SELECT LAST_INSERT_ID());
        INSERT INTO f_billdp(
            F_BillDpDate,
            F_BillDpNumber,
            F_BillDPF_PayID,
            F_BillDpM_VendorID,
            F_BillDpAmount,
            F_BillDpUsed,
            F_BillDpUnused,
            F_BillDpFullyUsed,
            F_BillDpNote)
        SELECT date(now()), fn_numbering("BILLDP"), pdppayid, pvendor, pdp, pdp, 0, "Y", pnumber;

        SET pdpid = (SELECT LAST_INSERT_ID());
    ELSE
        UPDATE f_billdp
        SET F_BillDpAmount = pdp
        WHERE F_BillDpID = pdpid;
    END IF;
ELSE
    IF pdpid <> 0 THEN
        UPDATE f_billdp
        SET F_BillDpAmount = pdp
        WHERE F_BillDpID = pdpid;
    END IF;
END IF;

UPDATE p_purchase SET P_PurchaseF_BillDPID = pdpid WHERE P_PurchaseID = pid;

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

    SET d_id = (SELECT P_PurchaseDetailID FROM p_purchasedetail WHERE P_PurchaseDetailIsActive = "O" AND P_PurchaseDetailA_ItemID = d_item AND P_PurchaseDetailP_PurchaseID = pid LIMIT 1);

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
        SET d_oqty = (SELECT P_PurchaseDetailQty FROM p_purchasedetail WHERE P_PurchaseDetailID = d_id);

        IF d_qty <> d_oqty THEN
            UPDATE p_purchasedetail
            SET P_PurchaseDetailQty = d_qty, P_PurchaseDetailPrice = d_price, P_PurchaseDetailDisc = d_disc, P_PurchaseDetailSubTotal = d_subtotal, P_PurchaseDetailPPN = d_ppn, P_PurchaseDetailPPNAmount = d_ppn_amount, P_PurchaseDetailTotal = d_total,
            P_PurchaseDetailIsActive = "Y"
            WHERE P_PurchaseDetailID = d_id;

            -- UPDATE STOCK
            UPDATE i_stock
            JOIN p_purchasedetail ON P_PurchaseDetailA_ItemID = I_StockM_ItemID
                AND P_PurchaseDetailID = d_id
                AND P_PurchaseDetailIsActive = "Y"
            SET I_StockQty = I_StockQty - d_oqty,
                I_StockLastTransCode = "PURCHASE.MODIFY",
                I_StockLastTransRefID = P_PurchaseDetailID,
                I_StockLastTransQty = (0-d_oqty)
            WHERE I_StockM_WarehouseID = warehouse_id
            AND I_StockIsActive = "Y";
            -- END OF UPDATE STOK

        ELSE
            UPDATE p_purchasedetail
            SET P_PurchaseDetailIsActive = "Y"
            WHERE P_PurchaseDetailID = d_id;
        END IF;
    END IF;

    IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
    SET n = n + 1;
    SET ptotal = (ptotal + d_total) * ((100 - pdisc)/100);
        SET ptotal = ptotal - pdiscrp + pshipping;
    SET psubtotal = psubtotal + d_subtotal;
    SET pppntotal = pppntotal + d_ppn_amount;
    
    CALL sp_master_item_last_purchase_0(d_item);
END WHILE;

-- HAPUS STOK PENERIMAAN
-- UPDATE i_stock
-- JOIN p_purchasedetail ON P_PurchaseDetailA_ItemID = I_StockM_ItemID
--    AND P_PurchaseDetailP_PurchaseID = pid
--    AND P_PurchaseDetailIsActive = "O"
-- SET I_StockQty = I_StockQty - P_PurchaseDetailQty,
--    I_StockLastTransCode = "PURCHASE.DELETE",
--    I_StockLastTransRefID = P_PurchaseDetailID,
--    I_StockLastTransQty = (0-P_PurchaseDetailQty)
-- WHERE I_StockM_WarehouseID = warehouse_id
--    AND I_StockIsActive = "Y";

-- STOK
UPDATE i_stock
JOIN p_purchasedetail ON P_PurchaseDetailA_ItemID = I_StockM_ItemID
    AND P_PurchaseDetailP_PurchaseID = pid
    AND P_PurchaseDetailIsActive = "O"
SET I_StockQty = I_StockQty - P_PurchaseDetailQty,
    I_StockLastTransCode = "PURCHASE.DELETE",
    I_StockLastTransRefID = P_PurchaseDetailID,
    I_StockLastTransQty = (0 - P_PurchaseDetailQty),
I_StockLastTransDate = concat(pdate, " 00:00:00")
WHERE I_StockM_WarehouseID = warehouse_id
    AND I_StockIsActive = "Y";

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
    P_PurchaseGrandTotal = (P_PurchaseTotal * (1 + xppn)) + pshipping - pdp
WHERE P_PurchaseID = pid;
    
-- STOK
UPDATE i_stock
JOIN p_purchasedetail ON P_PurchaseDetailA_ItemID = I_StockM_ItemID
    AND P_PurchaseDetailP_PurchaseID = pid
    AND P_PurchaseDetailIsActive = "Y"
SET I_StockQty = I_StockQty + P_PurchaseDetailQty,
    I_StockLastTransCode = "PURCHASE.RECEIVE",
    I_StockLastTransRefID = P_PurchaseDetailID,
    I_StockLastTransQty = P_PurchaseDetailQty,
I_StockLastTransDate = concat(pdate, " 00:00:00")
WHERE I_StockM_WarehouseID = warehouse_id
    AND I_StockIsActive = "Y";


SELECT "OK" as status, JSON_OBJECT("purchase_id", pid) as data;

COMMIT;

END;;
DELIMITER ;