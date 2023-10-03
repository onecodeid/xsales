BEGIN

DECLARE pdate DATE;
DECLARE pnumber VARCHAR(25);
DECLARE prefnumber VARCHAR(50);
DECLARE pnote VARCHAR(1000);
DECLARE pmemo VARCHAR(1000);
DECLARE pvendor INTEGER;
DECLARE pwarehouse INTEGER;
DECLARE ptotal DOUBLE DEFAULT 0;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
DECLARE d_purchase INTEGER;
DECLARE d_qty DOUBLE;
DECLARE d_oqty DOUBLE;
DECLARE d_note VARCHAR(255);
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(1000) DEFAULT "";

DECLARE xppn DOUBLE DEFAULT 0.1;
DECLARE prid INTEGER;

DECLARE confirm CHAR(1) DEFAULT "N";
DECLARE bill INTEGER DEFAULT 0;

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

IF pid <> 0 THEN
    SET confirm = (SELECT P_ReceiveConfirm FROM p_receive WHERE P_ReceiveID = pid);
    SET bill = (SELECT P_ReceiveF_BillID FROM p_receive WHERE P_ReceiveID = pid);
END IF;

IF confirm = "Y" OR bill <> 0 THEN
    SELECT "ERR" status, "Tidak bisa disimpan, Order tersebut telah dikonfirmasi / ditagihkan :(" message;
ELSE
    SET pdate = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_date"));
    SET ptotal = 0;
    SET prefnumber = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ref_number"));
    SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));
    SET pmemo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_memo"));
    SET pvendor = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_vendor"));
    SET pwarehouse = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_warehouse"));

    IF pid = 0 THEN

        SET pnumber = (SELECT fn_numbering("RO"));
        INSERT INTO p_receive(P_ReceiveDate,
            P_ReceiveNumber,
            P_ReceiveRefNumber,
            P_ReceiveM_VendorID,
            P_ReceiveM_WarehouseID,
            P_ReceiveTotalQty,
            P_ReceiveNote,
            P_ReceiveMemo)
        SELECT pdate, pnumber, prefnumber, pvendor, pwarehouse, ptotal, pnote, pmemo;

        SET pid = (SELECT LAST_INSERT_ID());
    ELSE

        UPDATE p_receive
        SET P_ReceiveDate = pdate, P_ReceiveNote = pnote, P_ReceiveMemo = pmemo, P_ReceiveRefNumber = prefnumber
        WHERE P_ReceiveID = pid;
    END IF;

    SET l = JSON_LENGTH(jdata);
    WHILE n < l DO
        SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
        SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemid'));
        SET d_purchase = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.purchaseid'));
        SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
        SET d_note = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.note'));

        SET d_id = (SELECT P_ReceiveDetailID FROM p_receivedetail WHERE P_ReceiveDetailIsActive = "Y" AND P_ReceiveDetailP_PurchaseDetailID = d_purchase AND P_ReceiveDetailP_ReceiveID = pid);

        IF d_id IS NULL THEN
            INSERT INTO p_receivedetail(
                P_ReceiveDetailP_ReceiveID,
                P_ReceiveDetailP_PurchaseDetailID,
                P_ReceiveDetailA_ItemID,
                P_ReceiveDetailQty,
                P_ReceiveDetailNote
            )
            SELECT pid, d_purchase, d_item, d_qty, d_note;

            SET d_id = (SELECT LAST_INSERT_ID());

            UPDATE p_purchasedetail
            SET P_PurchaseDetailReceived = P_PurchaseDetailReceived + d_qty
            WHERE P_PurchaseDetailID = d_purchase;
        ELSE
            SET d_oqty = (SELECT P_ReceiveDetailQty FROM p_receivedetail WHERE P_ReceiveDetailID = d_id);

            UPDATE p_receivedetail
            SET P_ReceiveDetailQty = d_qty, P_ReceiveDetailNote = d_note
            WHERE P_ReceiveDetailID = d_id;

            UPDATE p_purchasedetail
            SET P_PurchaseDetailReceived = P_PurchaseDetailReceived + d_qty - d_oqty
            WHERE P_PurchaseDetailID = d_purchase;
        END IF;

        Set prid = (Select P_PurchaseDetailP_PurchaseID from p_purchasedetail where P_PurchaseDetailID = d_id);
        CALL sp_purchase_status_set(prid);

        IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
        SET n = n + 1;
        SET ptotal = ptotal + d_qty;
    END WHILE;

    UPDATE p_receivedetail
    SET P_ReceiveDetailIsActive = "N"
    WHERE P_ReceiveDetailP_ReceiveID = pid
    AND NOT FIND_IN_SET(P_ReceiveDetailID, d_ids) AND P_ReceiveDetailIsActive = "Y" ;

    UPDATE p_receive SET P_ReceiveTotalQty = ptotal WHERE P_ReceiveID = pid;

    
    update p_receive
    join p_receivedetail on p_receivedetailisactive = "Y" and p_receivedetailp_receiveid = p_receiveid
    join p_purchasedetail on p_receivedetailp_purchasedetailid = p_purchasedetailid
    join p_purchase on p_purchasedetailp_purchaseid = p_purchaseid
    set p_receivesecondarynumber = replace(p_purchasenumber, "PO", "RO")
    where p_receiveid = pid;

    SELECT "OK" as status, JSON_OBJECT("receive_id", pid) as data;
END IF;

COMMIT;

END