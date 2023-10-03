BEGIN

DECLARE pdate DATE;
DECLARE pnumber VARCHAR(25);
DECLARE prefnumber VARCHAR(50);
DECLARE pnote VARCHAR(1000);
DECLARE pmemo VARCHAR(1000);
DECLARE pcustomer INTEGER;
DECLARE pwarehouse INTEGER;
DECLARE pstaff INTEGER;
DECLARE ptype INTEGER;
DECLARE psendnote VARCHAR(100);
DECLARE ptotal DOUBLE DEFAULT 0;
DECLARE pproforma CHAR(1) DEFAULT "Y";

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE fail INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
DECLARE d_sales INTEGER;
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
    SET confirm = (SELECT L_DeliveryConfirm FROM l_delivery WHERE L_DeliveryID = pid);
END IF;

IF confirm = "Y" THEN
    SELECT "ERR" status, "Tidak bisa disimpan, Order tersebut telah dikonfirmasi / ditagihkan :(" message;
ELSE
    SET pnumber = (SELECT fn_numbering("DO"));
    SET pdate = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_date"));
    SET ptotal = 0;
    SET prefnumber = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_ref_number"));
    SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));
    SET pmemo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_memo"));
    SET pcustomer = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_customer"));
    SET pwarehouse = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_warehouse"));
    SET pstaff = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_staff"));
    SET ptype = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_type"));
    SET psendnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_send_note"));

    IF ptype IS NULL THEN SET ptype = 1; END IF;
    IF psendnote IS NULL THEN SET psendnote = ""; END IF;

    UPDATE l_delivery
    SET L_DeliveryNumber = pnumber, L_DeliveryDate = pdate, L_DeliveryNote = pnote, L_DeliveryMemo = pmemo, L_DeliveryRefNumber = prefnumber, L_DeliveryS_StaffID = pstaff, L_DeliveryM_DeliveryTypeID = ptype, L_DeliverySendNote = psendnote, L_DeliveryM_WarehouseID = pwarehouse
    WHERE L_DeliveryID = pid;

    -- CONFIRM SEND
    SET fail = (SELECT SUM(IF(IFNULL(I_StockQty, 0) < L_DeliveryDetailQty, 1, 0))
        FROM l_deliverydetail
        LEFT JOIN i_stock ON I_StockM_ItemID = L_DeliveryDetailA_ItemID
            AND I_StockM_WarehouseID = pwarehouse
            AND I_StockIsActive = "Y"
        WHERE L_DeliveryDetailL_DeliveryID = pid
            AND L_DeliveryDetailIsActive = "Y");
    IF fail IS NULL THEN SET fail = 0; END IF;

    IF fail > 0 THEN
        SELECT "ERR" status, "Ada item / produk yang memiliki stok di bawah permintaan, mohon cek kembali :(" message;
        ROLLBACK;
    ELSE
        UPDATE i_stock
        JOIN l_deliverydetail ON L_DeliveryDetailA_ItemID = I_StockM_ItemID
            AND L_DeliveryDetailL_DeliveryID = pid
            AND L_DeliveryDetailIsActive = "Y"
        SET I_StockQty = I_StockQty - L_DeliveryDetailQty,
            I_StockLastTransCode = "SALES.DELIVERY",
            I_StockLastTransRefID = L_DeliveryDetailID,
            I_StockLastTransQty = (0 - L_DeliveryDetailQty)
        WHERE I_StockM_WarehouseID = pwarehouse
            AND I_StockIsActive = "Y";

        SET ptotal = (SELECT SUM(L_DeliveryDetailQty) FROM l_deliverydetail 
                    WHERE L_DeliveryDetailIsActive = "Y" AND L_DeliveryDetailL_DeliveryID = pid);

        UPDATE l_delivery SET L_DeliveryConfirm = "Y", L_DeliveryTotalQty = ptotal WHERE L_DeliveryID = pid;

        SELECT "OK" status, JSON_OBJECT("delivery_id", pid) data;
        COMMIT;
    END IF;
    -- WHILE n < l DO
    --     SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    --     SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemid'));
    --     SET d_sales = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.salesid'));
    --     SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
    --     SET d_note = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.note'));

    --     SET d_id = (SELECT L_DeliveryDetailID FROM l_deliverydetail WHERE L_DeliveryDetailIsActive = "Y" AND L_DeliveryDetailL_SalesDetailID = d_sales AND L_DeliveryDetailL_DeliveryID = pid);

    --     IF d_id IS NULL THEN
    --         INSERT INTO l_deliverydetail(
    --             L_DeliveryDetailL_DeliveryID,
    --             L_DeliveryDetailL_SalesDetailID,
    --             L_DeliveryDetailA_ItemID,
    --             L_DeliveryDetailQty,
    --             L_DeliveryDetailNote
    --         )
    --         SELECT pid, d_sales, d_item, d_qty, d_note;

    --         SET d_id = (SELECT LAST_INSERT_ID());

    --         UPDATE l_salesdetail
    --         SET L_SalesDetailSent = L_SalesDetailSent + d_qty
    --         WHERE L_SalesDetailID = d_sales;
    --     ELSE
    --         SET d_oqty = (SELECT L_DeliveryDetailQty FROM l_deliverydetail WHERE L_DeliveryDetailID = d_id);

    --         UPDATE l_deliverydetail
    --         SET L_DeliveryDetailQty = d_qty, L_DeliveryDetailNote = d_note
    --         WHERE L_DeliveryDetailID = d_id;

    --         UPDATE l_salesdetail
    --         SET L_SalesDetailSent = L_SalesDetailSent + d_qty - d_oqty
    --         WHERE L_SalesDetailID = d_sales;
    --     END IF;

    --     Set prid = (Select L_SalesDetailL_SalesID from l_salesdetail where L_SalesDetailID = d_sales);
    --     CALL sp_sales_status_set(prid);

    --     IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
    --     SET n = n + 1;
    --     SET ptotal = ptotal + d_qty;
    -- END WHILE;

    -- UPDATE l_deliverydetail
    -- SET L_DeliveryDetailIsActive = "N"
    -- WHERE L_DeliveryDetailL_DeliveryID = pid
    -- AND NOT FIND_IN_SET(L_DeliveryDetailID, d_ids) AND L_DeliveryDetailIsActive = "Y" ;

    -- UPDATE l_delivery SET L_DeliveryTotalQty = ptotal WHERE L_DeliveryID = pid;

    -- SELECT "OK" as status, JSON_OBJECT("delivery_id", pid) as data;
END IF;

-- COMMIT;

END