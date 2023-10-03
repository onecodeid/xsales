BEGIN

DECLARE cbill_id INTEGER; 

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

SET cbill_id = (SELECT P_ReceiveF_BillID FROM p_receive WHERE P_ReceiveID = receive_id);

IF cbill_id <> 0 THEN
    SELECT "ERR" status, "Invoice sudah dibuat untuk faktur tersebut :(" message;
    ROLLBACK;
ELSE
    CALL sp_bill_save_single_notrans(bill_id, hdata, receive_id, uid);
    COMMIT;
END IF;
        
END