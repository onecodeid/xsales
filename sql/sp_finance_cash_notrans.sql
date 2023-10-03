DROP PROCEDURE `sp_finance_cash_save_notrans`;
DELIMITER ;;
CREATE PROCEDURE `sp_finance_cash_save_notrans` (IN `cash_id` integer, IN `hdata` text, IN `jdata` text, IN `uid` integer)
BEGIN

DECLARE cash_date DATE;
DECLARE cash_number VARCHAR(25);
DECLARE cash_from TEXT;
DECLARE cash_from_account INTEGER;
DECLARE cash_to_account INTEGEr;
DECLARE cash_from_account_code VARCHAR(20);
DECLARE cash_to_account_code VARCHAR(20);
DECLARE cash_amount DOUBLE;
DECLARE cash_disc DOUBLE;
DECLARE cash_discrp DOUBLE;
DECLARE cash_subtotal DOUBLE;
DECLARE cash_tax INTEGER;
DECLARE cash_tax_amount DOUBLE DEFAULT 0;
DECLARE cash_total DOUBLE;
DECLARE cash_note TEXT;
DECLARE cash_memo TEXT;
DECLARE cash_tags TEXT;
DECLARE cash_receipt TEXT;
DECLARE cash_type_code VARCHAR(50);
DECLARE cash_type_id INTEGER;
DECLARE cash_md5 CHAR(32);

DECLARE journal_id INTEGER DEFAULT 0;
DECLARE journal_type CHAR(4);
DECLARE main_account_id INTEGER;
DECLARE adata TEXT DEFAULT "";

-- DETAILS
DECLARE d_account INTEGER;
DECLARE d_debit DOUBLE;
DECLARE d_credit DOUBLE;
DECLARE d_id INTEGER;
DECLARE tmp VARCHAR(255);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;

SET cash_date = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_date"));
SET cash_from = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_from"));
SET cash_from_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_from_account"));
SET cash_to_account = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_to_account"));
SET cash_from_account_code = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_from_account_code"));
SET cash_to_account_code = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_to_account_code"));
SET cash_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_amount"));
SET cash_disc = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_disc"));
SET cash_discrp = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_discrp"));
-- SET cash_subtotal
SET cash_tax = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_tax"));
SET cash_tax_amount = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_tax_amount"));
SET cash_note = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_note"));
SET cash_memo = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_memo"));
SET cash_tags = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_tags"));
SET cash_receipt = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_receipt"));
SET cash_type_code = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_type_code"));
SET cash_md5 = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.cash_md5"));

SET cash_subtotal = (cash_amount * (100 - cash_disc) / 100) - cash_discrp;
SET cash_total = cash_subtotal * (100 + cash_tax_amount) / 100;

SET cash_type_id = (SELECT M_CashTypeID FROM m_cashtype WHERE M_CashTypeCode = cash_type_code AND M_CashTypeIsActive = "Y" LIMIT 1);

IF cash_from_account_code IS NOT NULL THEN
    SET cash_from_account = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = cash_from_account_code AND M_AccountIsActive = "Y" LIMIT 1);
END IF;

IF cash_to_account_code IS NOT NULL THEN
    SET cash_to_account = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = cash_to_account_code AND M_AccountIsActive = "Y" LIMIT 1);
END IF;

IF cash_md5 IS NULL THEN
    SET cash_md5 = md5(concat(cash_date, cash_from));
END IF;

IF cash_id = 0 THEN
    SET cash_number = (SELECT fn_numbering("FCASH"));

    INSERT INTO f_cash(
        F_CashM_CashTypeID,
        F_CashDate,
        F_CashNumber,
        F_CashFrom,
        F_CashFromM_AccountID,
        F_CashToM_AccountID,
        F_CashAmount,
        F_CashDisc,
        F_CashDiscRp,
        F_CashSubTotal,
        F_CashM_TaxID,
        F_CashM_TaxAmount,
        F_CashTotal,
        F_CashNote,
        F_CashMemo,
        F_CashTags,
        F_CashReceipt,
        F_CashMd5
    )
    SELECT cash_type_id,
        cash_date,
        cash_number,
        cash_from,
        cash_from_account,
        cash_to_account,
        cash_amount,
        cash_disc,
        cash_discrp,
        cash_subtotal,
        cash_tax,
        cash_tax_amount,
        cash_total,
        cash_note,
        cash_memo,
        cash_tags,
        cash_receipt,
        cash_md5;

    SET cash_id = (SELECT LAST_INSERT_ID());
ELSE
    SET cash_number = (SELECT F_CashNumber FROM f_cash WHERE F_CashID = cash_id);
    SET journal_id = (SELECT F_CashT_JournalID FROM f_cash WHERE F_CashID = cash_id);

    UPDATE f_cash
    SET F_CashDate = cash_date,
        F_CashFrom = cash_from,
        F_CashFromM_AccountID = cash_from_account,
        F_CashToM_AccountID = cash_to_account,
        F_CashAmount = cash_amount,
        F_CashDisc = cash_disc,
        F_CashDiscRp = cash_discrp,
        F_CashSubTotal = cash_subtotal,
        F_CashM_TaxID = cash_tax,
        F_CashM_TaxAmount = cash_tax_amount,
        F_CashTotal = cash_total,
        F_CashNote = cash_note,
        F_CashMemo = cash_memo,
        F_CashTags = cash_tags,
        F_CashReceipt = cash_receipt
    WHERE F_CashID = cash_id;
END IF;

-- DETAILS
SET cash_total = 0;
UPDATE f_cashdetail SET F_CashDetailIsActive = "O" WHERE F_CashDetailF_CashID = cash_id AND F_CashDetailIsActive = "Y";

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n,']'));
    SET d_account = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account'));
    SET d_debit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.debit'));
    SET d_credit = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.credit'));
    SET d_id = (SELECT F_CashDetailID
        FROM f_cashdetail 
        WHERE F_CashDetailF_CashID = cash_id AND F_CashDetailIsActive = "O"
        AND F_CashDetailM_AccountID = d_account
        LIMIT 1
        );

    IF d_id IS NULL THEN
        INSERT INTO f_cashdetail(F_CashDetailF_CashID, F_CashDetailM_AccountID, F_CashDetailDebit, F_CashDetailCredit)
        SELECT cash_id, d_account, d_debit, d_credit;
    ELSE
        UPDATE f_cashdetail SET F_CashDetailDebit = d_debit, F_CashDetailCredit = d_credit, F_CashDetailIsActive = "Y" 
        WHERE F_CashDetailID = d_id;
    END IF;
    
    SET n = n + 1;

    IF cash_type_code = 'CASH.RECEIVE' THEN
        SET cash_total = cash_total + d_debit;
    ELSEIF cash_type_code = 'CASH.PAY' THEN
        SET cash_total = cash_total + d_credit;
    END IF;

    IF adata <> "" THEN SET adata = CONCAT(adata, ","); END IF;
    SET adata = CONCAT(adata, JSON_OBJECT("account", d_account, "debit", d_debit, "credit", d_credit, "ledger_note", cash_note));
END WHILE;
UPDATE f_cashdetail SET F_CashDetailIsActive = "N" WHERE F_CashDetailF_CashID = cash_id AND F_CashDetailIsActive = "O";


-- END OF DETAILS

SET journal_type = (SELECT M_CashTypeJournalType FROM m_cashtype WHERE M_CashTypeID = cash_type_id);
SET main_account_id = cash_to_account;
IF cash_type_code = 'CASH.RECEIVE' THEN
    UPDATE f_cash SET F_CashAmount = cash_total, F_CashSubTotal = cash_total, F_CashTotal = cash_total WHERE F_CashID = cash_id;
    -- SET adata = CONCAT("[", JSON_OBJECT("account", cash_to_account, "debit", cash_total, "credit", 0, "ledger_note", cash_note), ",", adata, "]");
    SET adata = CONCAT("[", adata, "]");
    
    CALL sp_journal_save_notrans_noreturn(journal_id, cash_date, cash_number, cash_note, adata, journal_type, cash_to_account );
ELSEIF cash_type_code = 'CASH.PAY' THEN
    UPDATE f_cash SET F_CashAmount = cash_total, F_CashSubTotal = cash_total, F_CashTotal = cash_total WHERE F_CashID = cash_id;
    -- SET adata = CONCAT("[", adata, ",", JSON_OBJECT("account", cash_to_account, "debit", cash_total, "credit", 0, "ledger_note", cash_note), "]");
    SET adata = CONCAT("[", adata, "]");

    CALL sp_journal_save_notrans_noreturn(journal_id, cash_date, cash_number, cash_note, adata, journal_type, cash_to_account );
ELSEIF cash_type_code = 'CASH.TRANSFER' THEN
    SET adata = JSON_ARRAY(JSON_OBJECT("account", cash_from_account, "debit", 0, "credit", cash_amount, "ledger_note", cash_note),
                JSON_OBJECT("account", cash_to_account, "debit", cash_amount, "credit", 0, "ledger_note", cash_note));

    CALL sp_journal_save_notrans_noreturn(journal_id, cash_date, cash_number, cash_note, adata, journal_type, cash_to_account );
END IF;

IF journal_id = 0 THEN
    SET journal_id = (SELECT MAX(T_JournalID) FROM t_journal WHERE T_JournalType = journal_type AND T_JournalDate = cash_date AND T_JournalIsActive = "Y"
                    AND T_JournalMainM_AccountID = main_account_id);
    UPDATE f_cash SET F_CashT_JournalID = journal_id WHERE F_CashID = cash_id;
END IF;

UPDATE t_journal JOIN f_cash ON F_CashID = cash_id and F_CashT_JournalID = T_JournalID 
SET T_JournalTags = F_CashTags, T_JournalRefNote = F_CashFrom, T_JournalRefID = F_CashID WHERE T_JournalID = journal_id;


-- SELECT "OK" as status, JSON_OBJECT("cash_id", cash_id, "cash_number", cash_number) data;

END;;
DELIMITER ;