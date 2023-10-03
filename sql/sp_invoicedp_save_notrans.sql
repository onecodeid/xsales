BEGIN

DECLARE tmp VARCHAR(255);
DECLARE d_id INTEGER;
DECLARE d_amount DOUBLE;
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;

DECLARE dp_id INTEGER;
DECLARE total DOUBLE DEFAULT 0;

UPDATE l_invoicedp
SET L_InvoiceDpIsActive = "O"
WHERE L_InvoiceDpIsActive = "Y" AND L_InvoiceDpL_InvoiceID = ivid;

UPDATE f_paymentdp
JOIN l_invoicedp ON L_InvoiceDpF_PaymentDpID = F_PaymentDpID AND L_InvoiceDpIsActive = "O" AND L_InvoiceDpL_InvoiceID = ivid
SET F_PaymentDpUsed = F_PaymentDpUsed - L_InvoiceDpAmount;

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT("$[", n, "]"));
    SET d_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.id"));
    SET d_amount = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.amount"));

    SET dp_id = (SELECT L_InvoiceDpID FROM l_invoicedp 
                WHERE L_InvoiceDpIsActive = "Y" AND L_InvoiceDpL_InvoiceID = ivid AND L_InvoiceDpF_PaymentDpID = d_id);
    IF dp_id IS NULL THEN
        INSERT INTO l_invoicedp(L_InvoiceDpL_InvoiceID, L_InvoiceDpF_PaymentDpID, L_InvoiceDpAmount)
        SELECT ivid, d_id, d_amount;
    ELSE
        UPDATE l_invoicedp
        SET L_InvoiceDpAmount = d_amount, L_InvoiceDpIsActive = "Y"
        WHERE L_InvoiceDpID = dp_id;
    END IF;

    UPDATE f_paymentdp
    SET F_PaymentDpUsed = F_PaymentDpUsed + d_amount
    WHERE F_PaymentDpID = d_id;

    SET n = n + 1;
    SET total = total + d_amount;
END WHILE;

UPDATE l_invoicedp
SET L_InvoiceDpIsActive = "N"
WHERE L_InvoiceDpL_InvoiceID = ivid AND L_InvoiceDpIsActive = "O";

UPDATE l_invoice
SET L_InvoiceDp = total
WHERE L_InvoiceID = ivid;

END