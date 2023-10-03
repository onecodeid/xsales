DROP PROCEDURE `sp_bill_delete`;
DELIMITER ;;
CREATE PROCEDURE `sp_bill_delete` (IN `billid` int, IN `uid` int)
BEGIN

DECLARE jid INTEGER;
DECLARE dpuse DOUBLE;
DECLARE is_lunas CHAR(1);
DECLARE paid DOUBLE;

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

SET is_lunas = (SELECT F_BillLunas FROM f_bill wHERE F_BillID = billid);
SET paid = (SELECT F_BillPaid FROM f_bill wHERE F_BillID = billid);

IF is_lunas = "Y" THEN
    SELECT "ERR" status, "Tagihan sudah lunas, tidak bisa dihapus :(" message;
    ROLLBACK;
ELSEIF paid > 0 THEN
    SELECT "ERR" status, "Tagihan sudah dibayar, tidak bisa dihapus :(" message;
    ROLLBACK;
ELSE

    UPDATE f_bill SET F_BillIsActive = "N"
    WHERE F_BillID = billid;

    UPDATE f_billdetail SET F_BillDetailIsActive = "N"
    WHERE F_BillDetailF_BillID = billid ANd F_BillDetailISActive = "Y";

    SET jid = (SELECT F_BillT_JournalID FROM f_bill WHERE F_BillID = billid);
    IF jid <> 0 THEN
        UPDATE t_journal SET T_JournalIsActive = "N" WHERE T_JournalID = jid;
        UPDATE t_journaldetail SET t_JournalDetailIsActive = "N" WHERE T_JournalDetailT_JournalID = jid;
    END IF;

    UPDATE f_billdp
    JOIN f_billdpuse ON F_BillDpUseF_BillDpID = F_BillDpID AND F_BillDpUseIsActive = "Y" AND F_BillDpUseF_BillID = billid
    SET F_BillDpUsed = F_BillDpUsed - F_BillDpUseAmount;

    UPDATE f_billdpuse SET F_BillDpUseIsActive = "N" WHERE F_BillDpUseF_BillID = billid;

    -- UPDATE RECEIVE
    UPDATE p_receive SET P_ReceiveF_BillID = 0 WHERE P_ReceiveF_BillID = billid;
    
    SELECT "OK" status, JSON_OBJECT("bill_id", billid) data;
END IF;

COMMIT;

END;;
DELIMITER ;