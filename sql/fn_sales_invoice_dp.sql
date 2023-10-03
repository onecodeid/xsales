BEGIN

DECLARE x TEXT;

SET x = (
    SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("dp", 
        jSON_OBJECT("dp_id", F_PaymentDpID, 
            "dp_amount", F_PaymentDpAmount,
            "dp_used", F_PaymentDpUsed,
            "dp_unused", F_PaymentDpUnused,
            "dp_number", F_PaymentDpNumber,
            "dp_date", F_PaymentDpDate), "amount", L_InvoiceDpAmount) SEPARATOR ", "), "]")
    FROM l_invoicedp
    JOIN f_paymentdp ON L_InvoiceDpF_PaymentDpID = F_PaymentDpID
    WHERE L_InvoiceDpL_InvoiceID = ivid AND L_InvoiceDpIsActive = "Y"
);

IF x IS NULL THEN SET x = "[]"; END IF;

RETURN x;

END