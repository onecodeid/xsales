DROP PROCEDURE `sp_sales_delivery_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_sales_delivery_save` (IN `pid` int, IN `hdata` varchar(2000), IN `jdata` text, IN `uid` int)
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
DECLARE pexp INTEGER;
DECLARE psendnote VARCHAR(100);
DECLARE ptotal DOUBLE DEFAULT 0;
DECLARE pproforma CHAR(1);
DECLARE customer_name VARCHAR(100);
DECLARE ledger VARCHAR(255);
DECLARE old_date DATE;

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;

DECLARE d_item INTEGER;
DECLARE d_item_name VARCHAR(255);
DECLARE d_sales INTEGER;
DECLARE d_qty DOUBLE;
DECLARE d_oqty DOUBLE;
DECLARE d_note VARCHAR(255);
DECLARE d_id INTEGER;
DECLARE d_ids VARCHAR(1000) DEFAULT "";
DECLARE d_hpp DOUBLE;

DECLARE xppn DOUBLE DEFAULT 0.1;
DECLARE prid INTEGER;

DECLARE confirm CHAR(1) DEFAULT "N";
DECLARE bill INTEGER DEFAULT 0;

-- ACCOUNTS
DECLARE total_hpp DOUBLE DEFAULT 0;
DECLARE account_stock_id INTEGER;
DECLARE account_hpp_id INTEGER;
DECLARE journal_id INTEGER DEFAULT 0;
DECLARE adata TEXT;

-- CURSOR
DECLARE finished INTEGER DEFAULT 0;
DECLARE log_id INTEGER DEFAULT 0;
DECLARE log_idx VARCHAR(50);
DECLARE log_item INTEGER;
DECLARE log_qty DOUBLE;

-- declare cursor
DEClARE curDelivery
    CURSOR FOR 
        SELECT Log_StockID, Log_StockIndex, Log_StockM_ItemID, Log_StockQty FROM one_account_aw_log.log_stock WHERE Log_StockCode = "SALES.DELIVERY" AND Log_StockRefID IN (
            SELECT l_deliverydetailid FROM l_deliverydetail WHERE l_deliverydetaill_deliveryid = pid and l_deliverydetailisactive = "Y"
        ) AND Log_StockIsActive = "Y";

-- declare NOT FOUND handler
DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

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

-- ACCOUNT
SET account_stock_id = (SELECT fn_master_get_account_id("ACC.STOCK"));
SET account_hpp_id = (SELECT fn_master_get_account_id("ACC.PURCHASE"));

IF pid <> 0 THEN
    SET confirm = (SELECT L_DeliveryConfirm FROM l_delivery WHERE L_DeliveryID = pid);
    SET bill = (SELECT L_DeliveryL_InvoiceID FROM l_delivery WHERE L_DeliveryID = pid);
END IF;

IF bill <> 0 THEN
    SELECT "ERR" status, "Tidak bisa disimpan, Order tersebut telah ditagihkan :(" message;
-- ELSEIF confirm = "Y"
    -- SELECT "ERR" status, "Tidak bisa disimpan, Order tersebut telah dikonfirmasi / ditagihkan :(" message;
ELSE
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
    SET pexp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_exp"));

    IF ptype IS NULL THEN SET ptype = 1; END IF;
    IF psendnote IS NULL THEN SET psendnote = ""; END IF;

    IF pid = 0 THEN
-- show columns from one_account_aw_log.log_stock;
        SET pnumber = (SELECT fn_numbering("DO"));
        INSERT INTO l_delivery(L_DeliveryDate,
            L_DeliveryNumber,
            L_DeliveryRefNumber,
            L_DeliveryM_CustomerID,
            L_DeliveryM_WarehouseID,
            L_DeliveryTotalQty,
            L_DeliveryNote,
            L_DeliveryMemo,
            L_DeliveryS_StaffID,
            L_DeliveryM_DeliveryTypeID,
            L_DeliveryM_ExpeditionID,
            L_DeliverySendNote,
            L_DeliveryUID)
        SELECT pdate, pnumber, prefnumber, pcustomer, pwarehouse, ptotal, pnote, pmemo, pstaff, ptype, pexp, psendnote, uid;

        SET pid = (SELECT LAST_INSERT_ID());
        CALL sp_log_activity("CREATE", "SALES.DELIVERY", pid, uid);
    ELSE

        -- IF CONFIRMED, THEN DELETE LOG FIRST
        IF confirm = "Y" THEN
            UPDATE l_delivery SET L_DeliveryConfirm = "N" WHERE L_DeliveryID = pid;

            OPEN curDelivery;

            getDelivery: LOOP
                FETCH curDelivery INTO log_id, log_idx, log_item, log_qty;
                IF finished = 1 THEN 
                    LEAVE getDelivery;
                END IF;

                -- work here
                UPDATE one_account_aw_log.log_stock
                SET Log_StockIsActive = "N" WHERE Log_StockID = log_id;

                UPDATE one_account_aw_log.log_stock
                SET Log_StockBeforeQty = Log_StockBeforeQty - log_qty,
                Log_StockAfterQty = Log_StockAfterQty - log_qty
                WHERE Log_StockIndex > log_idx AND Log_StockM_ItemID = log_item
                AND Log_StockM_WarehouseID = pwarehouse 
                AND Log_StockIsActive = "Y";

                -- Adjust Stock
                UPDATE i_stock
                SET I_StockQty = I_StockQty - log_qty, I_StockLastTransCode = "LOG.ADJUST", I_StockLastTransDate = pdate, 
                I_StockLastTransRefID = 0, I_StockLastTransQty = log_qty
                WHERE I_StockM_WarehouseID = pwarehouse
                AND I_StockM_ItemID = log_item
                AND I_StockISActive = "Y";

            END LOOP getDelivery;
            CLOSE curDelivery;

        END IF;

        SET old_date = (SELECT L_DeliveryDate FROM l_delivery WHERE L_DeliveryID = pid);

        UPDATE l_delivery
        SET L_DeliveryDate = pdate, L_DeliveryNote = pnote, L_DeliveryMemo = pmemo, L_DeliveryRefNumber = prefnumber, L_DeliveryS_StaffID = pstaff, 
        L_DeliveryM_DeliveryTypeID = ptype, L_DeliverySendNote = psendnote, L_DeliveryM_ExpeditionID = pexp, L_DeliveryUID = uid
        WHERE L_DeliveryID = pid;

        SET journal_id = (SELECT L_DeliveryT_JournalID FROM l_delivery WHERE L_DeliveryID = pid);
        SET pnumber = (SELECT L_DeliveryNumber FROM l_delivery WHERE L_DeliveryID = pid);
        CALL sp_log_activity("MODIFY", "SALES.DELIVERY", pid, uid);
    END IF;

    SET l = JSON_LENGTH(jdata);
    WHILE n < l DO
        SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
        SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemid'));
        SET d_item_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.itemname'));
        SET d_sales = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.salesid'));
        SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
        SET d_note = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.note'));

        SET d_id = (SELECT L_DeliveryDetailID FROM l_deliverydetail WHERE L_DeliveryDetailIsActive = "Y" AND L_DeliveryDetailL_SalesDetailID = d_sales AND L_DeliveryDetailL_DeliveryID = pid);

        IF d_id IS NULL THEN
            INSERT INTO l_deliverydetail(
                L_DeliveryDetailL_DeliveryID,
                L_DeliveryDetailL_SalesDetailID,
                L_DeliveryDetailA_ItemID,
                L_DeliveryDetailA_ItemName,
                L_DeliveryDetailQty,
                L_DeliveryDetailNote
            )
            SELECT pid, d_sales, d_item, d_item_name, d_qty, d_note;

            SET d_id = (SELECT LAST_INSERT_ID());

            UPDATE l_salesdetail
            SET L_SalesDetailSent = L_SalesDetailSent + d_qty
            WHERE L_SalesDetailID = d_sales;
        ELSE
            SET d_oqty = (SELECT L_DeliveryDetailQty FROM l_deliverydetail WHERE L_DeliveryDetailID = d_id);

            UPDATE l_deliverydetail
            SET L_DeliveryDetailQty = d_qty, L_DeliveryDetailNote = d_note
            WHERE L_DeliveryDetailID = d_id;

            UPDATE l_salesdetail
            SET L_SalesDetailSent = L_SalesDetailSent + d_qty - d_oqty
            WHERE L_SalesDetailID = d_sales;
        END IF;

        Set prid = (Select L_SalesDetailL_SalesID from l_salesdetail where L_SalesDetailID = d_sales);
        CALL sp_sales_status_set(prid);

        IF d_ids = "" THEN SET d_ids = d_id; ELSE SET d_ids = CONCAT(d_ids, ",", d_id); END IF;
        SET n = n + 1;
        SET ptotal = ptotal + d_qty;

        -- HPP
        SET d_hpp = (SELECT L_SalesDetailHPP FROM l_salesdetail WHERE L_SalesDetailID = d_sales);
        SET total_hpp = total_hpp + (d_hpp * d_qty);
    END WHILE;

    UPDATE l_deliverydetail
    SET L_DeliveryDetailIsActive = "N"
    WHERE L_DeliveryDetailL_DeliveryID = pid
    AND NOT FIND_IN_SET(L_DeliveryDetailID, d_ids) AND L_DeliveryDetailIsActive = "Y" ;

    UPDATE l_delivery SET L_DeliveryTotalQty = ptotal WHERE L_DeliveryID = pid;

    -- ACCOUNT SECTION
    SET ledger = (SELECT CONCAT("Pengiriman #", pnumber, " ", M_CustomerName) FROM m_customer WHERE M_CustomerID = pcustomer);
    SET adata = JSON_ARRAY(JSON_OBJECT("account", account_hpp_id, "debit", total_hpp, "credit", 0, "ledger_note", ledger),
            JSON_OBJECT("account", account_stock_id, "debit", 0, "credit", total_hpp, "ledger_note", ledger));

    CALL sp_journal_save_notrans_noreturn(journal_id, pdate, pnumber, pnote, adata, "J.21", account_stock_id );

    IF journal_id = 0 THEN
        SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = pnumber AND T_JournalIsActive = "Y" ANd T_JournalType = "J.21");
        UPDATE l_delivery SET L_DeliveryT_JournalID = journal_id WHERE L_DeliveryID = pid;
    END IF;

    -- UPDATE REF ID
    UPDATE t_journal SET T_JournalRefID = pid WHERE T_JournalID = journal_id;

    SELECT "OK" as status, JSON_OBJECT("delivery_id", pid) as data;
END IF;

COMMIT;

END;;
DELIMITER ;