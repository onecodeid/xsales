DROP PROCEDURE `sp_invoice_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_invoice_delete` (IN `invoiceid` int, IN `uid` int)
BEGIN

DECLARE is_lunas CHAR(1);
DECLARE paid DOUBLE;
DECLARE journalid INTEGER;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

START TRANSACTION;

SET is_lunas = (SELECT L_InvoiceLunas FROM l_invoice wHERE L_InvoiceID = invoiceid);
SET paid = (SELECT L_InvoicePaid FROM l_invoice wHERE L_InvoiceID = invoiceid);
SET journalid = (SELECT L_InvoiceT_JournalID FROM l_invoice wHERE L_InvoiceID = invoiceid);

IF is_lunas = "Y" THEN
    SELECT "ERR" status, "Tagihan sudah lunas, tidak bisa dihapus :(" message;
ELSEIF paid > 0 THEN
    SELECT "ERR" status, "Tagihan sudah dibayar, tidak bisa dihapus :(" message;
ELSE
    UPDATE l_delivery SET L_DeliveryL_InvoiceID = 0 WHERE L_DeliveryL_InvoiceID = invoiceid AND L_DeliveryIsActive = "Y";
    UPDATE l_invoicedetail SET L_InvoiceDetailISACtive = "N" WHERE L_InvoiceDetailL_InvoiceID = invoiceid;
    UPDATE l_invoice SET L_InvoiceIsActive = "N" WHERE L_InvoiceID = invoiceid;

    -- UPDATE JOURNAL
    UPDATE t_journaldetail SET T_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = journalid;
    UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_JournalID = journalid;
    
    SELECT "OK" status, JSON_OBJECT("invoice_id", invoiceid) data;
END IF;

COMMIT;
END
;;
DELIMITER ;