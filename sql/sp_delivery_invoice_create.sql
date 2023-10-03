BEGIN

DECLARE cinvoice_id INTEGER;

DECLARE delivery_id INTEGER;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;

DECLARE success INTEGER DEFAULT 1;

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

SET l = JSON_LENGTH(delivery_ids);
rcvloop : WHILE n < l Do
    SET delivery_id = JSON_UNQUOTE(JSON_EXTRACT(delivery_ids, CONCAT("$[", n, "]")));
    SET cinvoice_id = (SELECT L_DeliveryL_InvoiceID FROM l_delivery WHERE L_DeliveryID = delivery_id);

    IF cinvoice_id <> 0 THEN
        
        SET success = 0;
        LEAVE rcvloop;
    END IF;

    SET n = n + 1;
END WHILE rcvloop;

IF success = 1 THEN

    CALL sp_invoice_save_notrans(invoice_id, hdata, delivery_ids, uid);
    COMMIT;
ELSE
    SELECT "ERR" status, "Invoice sudah dibuat untuk faktur tersebut :(" message;
    ROLLBACK;
END IF;

END