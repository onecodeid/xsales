BEGIN

DECLARE retur_date DATE;
DECLARE retur_number VARCHAR(25);
DECLARE customer_id INTEGER;
DECLARE invoice_id INTEGER;
DECLARE retur_note TEXT;
DECLARE warehouse_id INTEGER;
DECLARE total DOUBLE DEFAULT 0;
DECLARE subtotal_before_ppn DOUBLE DEFAULT 0;

DECLARE ppn CHAR(1) DEFAULT "N";
DECLARE ppn_inc CHAR(1) DEFAULT "N";
DECLARE ppn_val DOUBLE DEFAULT 0;
DECLARE ppn_amount DOUBLE DEFAULT 0;

DECLARE l INTEGER;
DECLARE n INTEGER;
DECLARE tmp VARCHAR(255);

DECLARE d_id INTEGER;
DECLARE d_invoice INTEGER;
DECLARE d_item INTEGER;
DECLARE d_qty DOUBLE;
DECLARE d_note VARCHAR(255);
DECLARE d_price DOUBLE;
DECLARE d_hpp DOUBLE;

DECLARE old_qty DOUBLE;
DECLARE log_id INTEGER;

-- MEMO
DECLARE memo_id INTEGER DEFAULT 0;
DECLARE memo_number VARCHAR(25);

-- ACCOUNTS
DECLARE total_hpp DOUBLE DEFAULT 0;
DECLARE account_stock_id INTEGER;
DECLARE account_hpp_id INTEGER;
DECLARE account_retur_id INTEGER;
DECLARE account_ppn_id INTEGER;
DECLARE account_debt_id INTEGER;
DECLARE journal_id INTEGER DEFAULT 0;
DECLARE retur_journal_id INTEGER DEFAULT 0;
DECLARE adata TEXT;

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

SET retur_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.date'));
SET customer_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.customer'));
SET invoice_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.invoice'));
SET retur_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.note'));
SET warehouse_id = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.warehouse'));

SET ppn = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.ppn'));
SET ppn_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.ppn_amount'));
SET total = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.total'));
SET subtotal_before_ppn = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.subtotal'));

-- ACCOUNT
SET account_stock_id = (SELECT fn_master_get_account_id("ACC.STOCK"));
SET account_hpp_id = (SELECT fn_master_get_account_id("ACC.PURCHASE"));
SET account_retur_id = (SELECT fn_master_get_account_id("ACC.SALES.RETUR"));
SET account_ppn_id = (SELECT fn_master_get_account_id("ACC.TAX.PPN"));
SET account_debt_id = (SELECT fn_master_get_account_id("ACC.DEBT"));

--     L_ReturPPN	char(1) [N]	
-- L_ReturSubTotalBeforePPN	double [0]	
-- L_ReturPPNAmount

IF returid = 0 THEN
    SET retur_number = (SELECT fn_numbering('INVR'));
    INSERT INTO l_retur(
        L_ReturDate,
        L_ReturNumber,
        L_ReturM_CustomerID,
        L_ReturL_InvoiceID,
        L_ReturM_WarehouseID,
        L_ReturPPN,
        L_ReturSubTotalBeforePPN,
        L_ReturPPNAmount,
        L_ReturTotal,
        L_ReturNote
    )
    SELECT retur_date, retur_number, customer_id, invoice_id, warehouse_id, ppn, subtotal_before_ppn, ppn_amount, total, retur_note;

    SET returid = (SELECT LAST_INSERT_ID());
ELSE
    UPDATE l_retur
    SET L_ReturDate = retur_date,
        L_ReturM_WarehouseID = warehouse_id,
        L_ReturPPN = ppn,
        L_ReturSubTotalBeforePPN = subtotal_before_ppn,
        L_ReturPPNAmount = ppn_amount,
        L_ReturTotal = total,
        L_ReturNote = retur_note
    WHERE L_ReturID = returid;

    SET retur_number = (SELECT L_ReturNumber FROM l_retur WHERE L_ReturID = returid);
    SET memo_id = (SELECT L_ReturF_MemoID FROM l_retur WHERE L_ReturID = returid);
    SET retur_journal_id = (SELECT L_ReturT_JournalID FROM l_retur WHERE L_ReturID = returid);
END IF;

SET l = JSON_LENGTH(jdata);
SET n = 0;
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    SET d_invoice = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.invoice'));
    SET d_item = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.item'));
    SET d_qty = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.qty'));
    SET d_note = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.note'));
    SET d_price = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.price'));

    IF d_qty > 0 THEN

        SET d_id = (SELECT L_ReturDetailID
                    FROM l_returdetail
                    WHERE L_ReturDetailL_InvoiceDetailID = d_invoice AND L_ReturDetailIsActive = "Y" AND L_ReturDetailL_ReturID = returid);
        IF d_id IS NULL THEN
            INSERT INTO l_returdetail(
                L_ReturDetailL_ReturID,
                L_ReturDetailL_InvoiceDetailID,
                L_ReturDetailM_ItemID,
                L_ReturDetailPrice,
                L_ReturDetailQty,
                L_ReturDetailTotal,
                L_ReturDetailNote
            )   
            SELECT returid, d_invoice, d_item, d_price, d_qty, d_qty * d_price, d_note;

            SET d_id = (SELECT LAST_INSERT_ID());
            -- SET total = total + (d_price * d_qty);

            -- UPDATE STOCK
            UPDATE i_stock
            SET I_StockQty = I_StockQty + d_qty,
                I_StockLastTransCode = "SALES.RETUR",
                I_StockLastTransDate = CONCAT(retur_date, " 23:59:59"),
                I_StockLastTransRefID = d_id,
                I_StockLastTransQty = d_qty,
                I_StockLastTransCreated = now()
            WHERE I_StockM_ItemID = d_item AND I_StockM_WarehouseID = warehouse_id AND I_StockIsActive = "Y";

            -- GET AND SET LOG ID
            SET log_id = (SELECT MAX(Log_StockID) FROM one_account_aw_log.log_stock
                            WHERE Log_StockCode	= "SALES.RETUR" AND Log_StockRefID = d_id AND Log_StockIsActive = "Y");
            UPDATE l_returdetail SET L_ReturDetailLogID = log_id WHERE L_ReturDetailID = d_id;

            -- UPDATE INVOICE DETAIL
            UPDATE l_invoicedetail SET L_InvoiceDetailReturQty = L_InvoiceDetailReturQty + d_qty,
                L_InvoiceDetailReturNominal = L_InvoiceDetailReturNominal + (d_qty * d_price)
            WHERE L_InvoiceDetailID = d_invoice;
        
        ELSE
            UPDATE l_returdetail
            SET L_ReturDetailQty = d_qty,
                L_ReturDetailTotal = d_qty * d_price,
                L_ReturDetailNote = d_note
            WHERE L_ReturDetailID = d_id;

            -- SET total = total + (d_price * d_qty);

            SELECT L_ReturDetailQty, L_ReturDetailLogID INTO old_qty, log_id
            FROM l_returdetail
            WHERE L_ReturDetailID = d_id;

            UPDATE i_stock
            SET I_StockQty = I_StockQty + d_qty - old_qty,
                I_StockLastTransCode = "LOG.ADJUST",
                I_StockLastTransDate = CONCAT(retur_date, " 23:59:59"),
                I_StockLastTransRefID = d_id,
                I_StockLastTransQty = d_qty,
                I_StockLastTransCreated = now()
            WHERE I_StockM_ItemID = d_item AND I_StockM_WarehouseID = warehouse_id AND I_StockIsActive = "Y";

            -- UPDATE LOG
            UPDATE one_account_aw_log.log_stock
            SET Log_StockQty = d_qty
            WHERE Log_StockID = log_id;

            CALL one_account_aw_log.sp_log_stock_adjust(d_item, warehouse_id, retur_date);

            -- UPDATE INVOICE DETAIL
            UPDATE l_invoicedetail SET L_InvoiceDetailReturQty = L_InvoiceDetailReturQty + d_qty - old_qty,
                L_InvoiceDetailReturNominal = L_InvoiceDetailReturNominal + ((d_qty - old_qty) * d_price)
            WHERE L_InvoiceDetailID = d_invoice;

        END IF;

        SET d_hpp = (SELECT L_SalesDetailHPP FROM l_invoicedetail 
                    JOIN l_deliverydetail ON L_InvoiceDetailA_ItemID = L_DeliveryDetailA_ItemID
                        AND L_InvoiceDetailL_DeliveryID = L_DeliveryDetailL_DeliveryID
                        AND L_DeliveryDetailIsActive = "Y"
                    JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
                    WHERE L_InvoiceDetailID = d_invoice);
        IF d_hpp Is NULL THEN SET d_hpp = 0; END IF;
        SET total_hpp = total_hpp + (d_hpp * d_qty);
    END IF;

    SET n = n + 1;

    -- PPN
    -- SELECT L_InvoiceDetailPPN, L_InvoiceDetailIncludePPN INTO ppn, ppn_inc
    -- FROM l_invoicedetail WHERE L_InvoiceDetailID = d_invoice;
END WHILE;

-- PPN
-- SELECT L_InvoicePPNValue INTO ppn_val 
-- FROM l_invoice WHERE L_InvoiceID = invoice_id;

-- SET TOTAL
-- UPDATE l_retur SET L_ReturTotal = total WHERE L_ReturID = returid;

-- MEMO
IF memo_id = 0 THEN
    SET memo_number = (SELECT fn_numbering("FMEMO"));

    INSERT INTO f_memo(
        F_MemoDate,
        F_MemoNumber,	
        F_MemoM_CustomerID,	
        F_MemoL_InvoiceID,	
        F_MemoAmount,	
        F_MemoNote,
        F_MemoTags
    )
    SELECT retur_date, memo_number, customer_id, invoice_id, total, CONCAT("Invoice #", L_InvoiceNumber, ", Retur #", retur_number), "[]"
    FROM l_invoice WHERE L_InvoiceID = invoice_id;

    SET memo_id = (SELECT LAST_INSERT_ID());
    
    UPDATE l_retur
    SET L_ReturF_MemoID = memo_id
    WHERE L_ReturID = returid;
ELSE
    UPDATE f_memo
    JOIN l_invoice ON L_InvoiceID = invoice_id
    SET F_MemoAmount = total,
        F_MemoNote = CONCAT("Invoice #", L_InvoiceNumber, ", Retur #", retur_number)
    WHERE F_MemoID = memo_id;

    SELECT IFNULL(F_MemoNumber, ''), F_MemoT_JournalID INTO memo_number, journal_id FROM f_memo WHERE F_MemoID = memo_id;
END IF;

-- ACCOUNT SECTION
IF ppn_amount <> 0 THEN
    SET adata = JSON_ARRAY(JSON_OBJECT("account", account_retur_id, "debit", subtotal_before_ppn, "credit", 0),
			JSON_OBJECT("account", account_ppn_id, "debit", ppn_amount, "credit", 0),
            JSON_OBJECT("account", account_debt_id, "debit", 0, "credit", total ));
ELSE
    SET adata = JSON_ARRAY(JSON_OBJECT("account", account_retur_id, "debit", total, "credit", 0),
            JSON_OBJECT("account", account_debt_id, "debit", 0, "credit", total ));
END IF;

CALL sp_journal_save_notrans_noreturn(journal_id, retur_date, memo_number, retur_note, adata, "J.17", account_retur_id );

IF journal_id = 0 THEN
	SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = memo_number AND T_JournalIsActive = "Y" ANd T_JournalType = "J.17");
    UPDATE f_memo SET F_MemoT_JournalID = journal_id WHERE F_MemoID = memo_id;
	-- UPDATE l_retur SET L_ReturT_JournalID = journal_id WHERE L_ReturID = returid;
END IF;

-- ACCOUNT RETUR
SET adata = JSON_ARRAY(JSON_OBJECT("account", account_stock_id, "debit", total_hpp, "credit", 0),
            JSON_OBJECT("account", account_hpp_id, "debit", 0, "credit", total_hpp ));
CALL sp_journal_save_notrans_noreturn(retur_journal_id, retur_date, retur_number, retur_note, adata, "J.22", account_stock_id );
IF retur_journal_id = 0 THEN
	SET retur_journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalReceipt = retur_number AND T_JournalIsActive = "Y" ANd T_JournalType = "J.22");
	UPDATE l_retur SET L_ReturT_JournalID = retur_journal_id WHERE L_ReturID = returid;
END IF;

SELECT "OK" as status, JSON_OBJECT("retur_id", returid, "retur_number", retur_number) as data;


COMMIT;

END