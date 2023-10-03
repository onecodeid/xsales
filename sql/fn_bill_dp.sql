BEGIN

DECLARE x TEXT;

SET x = (
    SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("dp", 
        jSON_OBJECT("dp_id", F_BillDpID, 
            "dp_amount", F_BillDpAmount,
            "dp_used", F_BillDpUsed,
            "dp_unused", F_BillDpUnused,
            "dp_number", F_BillDpNumber,
            "dp_date", F_BillDpDate), "amount", F_BillDpUseAmount) SEPARATOR ", "), "]")
    FROM f_billdpuse
    JOIN f_billdp ON F_BillDpUseF_BillDpID = F_BillDpID
    WHERE F_BillDpUseF_BillID = billid AND F_BillDpUseIsActive = "Y"
);

IF x IS NULL THEN SET x = "[]"; END IF;

RETURN x;

END