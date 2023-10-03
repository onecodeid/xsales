BEGIN

DECLARE tmp VARCHAR(255);
DECLARE d_id INTEGER;
DECLARE d_amount DOUBLE;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;

DECLARE dp_id INTEGER;
DECLARE total DOUBLE DEFAULT 0;

UPDATE f_billdpuse
SET F_BillDpUseIsActive = "O"
WHERE F_BillDpUseIsActive = "Y" AND F_BillDpUseF_BillID = billid;

UPDATE f_billdp
JOIN f_billdpuse ON F_BillDpUseF_BillDpID = F_BillDpID AND F_BillDpUseIsActive = "O" AND F_BillDpUseF_BillID = billid
SET F_BillDpUsed = F_BillDpUsed - F_BillDpUseAmount;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT("$[", n, "]"));
    SET d_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.id"));
    SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.amount"));

    SET dp_id = (SELECT F_BillDpUseID FROM f_billdpuse 
                WHERE F_BillDpUseIsActive = "O" AND F_BillDpUseF_BillID = billid AND F_BillDpUseF_BillDpID = d_id);
    IF dp_id IS NULL THEN
        INSERT INTO f_billdpuse(F_BillDpUseF_BillID, F_BillDpUseF_BillDpID, F_BillDpUseAmount)
        SELECT billid, d_id, d_amount;
    ELSE
        UPDATE f_billdpuse
        SET F_BillDpUseAmount = d_amount, F_BillDpUseIsActive = "Y"
        WHERE F_BillDpUseID = dp_id;
    END IF;

    UPDATE f_billdp
    SET F_BillDpUsed = F_BillDpUsed + d_amount
    WHERE F_BillDpID = d_id;

    SET n = n + 1;
    SET total = total + d_amount;
END WHILE;

UPDATE f_billdpuse
SET F_BillDpUseIsActive = "N"
WHERE F_BillDpUseF_BillID = billid AND F_BillDpUseIsActive = "O";

UPDATE f_bill
SET F_BillDp = total
WHERE F_BillID = billid;

END