BEGIN

DECLARE tmp VARCHAR(1000);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE d_id INTEGEr;
DECLARE d_dp INTEGER;
DECLARE d_amount DOUBLE;
DECLARE o_amount DOUBLE;
DECLARE total DOUBLE DEFAULT 0;
DECLARE ids VARCHAR(255) DEFAULT "";

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

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
	SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    SET d_dp = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.dp'));
	SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.amount'));
    SET d_id = (SELECT F_BillDpUseID FROM f_billdpuse WHERE F_BillDpUseIsActive = "Y" AND F_BillDpUseF_BillID = billid AND F_BillDpUseF_BillDpID = d_dp);

    IF d_id IS NOT NULL THEN
        SET o_amount = (SELECT F_BillDpUseAmount FROM f_billdpuse WHERE F_BillDpUseID = d_id);
        UPDATE f_billdpuse SET F_BillDpUseAmount = d_amount WHERE F_BillDpUseID = d_id;
        
        UPDATE f_billdp SET F_BillDpUsed = F_BillDpUsed - o_amount + d_amount WHERE F_BillDpID = d_dp;
        UPDATE f_billdp SET F_BillDpUnused = F_BillDpAmount - F_BillDpUsed WHERE F_BillDpID = d_dp;

    ELSE
        INSERT INTO f_billdpuse(F_BillDpUseF_BillID, F_BillDpUseF_BillDpID, F_BillDpUseAmount)
        SELECT billid, d_dp, d_amount;
        SET d_id = (SELECT LAST_INSERT_ID());

        UPDATE f_billdp SET F_BillDpUsed = F_BillDpUsed + d_amount WHERE F_BillDpID = d_dp;
        UPDATE f_billdp SET F_BillDpUnused = F_BillDpAmount - F_BillDpUsed WHERE F_BillDpID = d_dp;
    END IF;

    IF ids = "" THEN SET ids = d_id; ELSE SET ids = CONCAT(ids, ",", d_id); END IF;
    
    SET n = n + 1;
    SET total = total + d_amount;
END WHILE

SELECT "OK" status, JSON_OBJECT("bill_id", billid) data;
COMMIT;

END