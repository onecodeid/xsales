BEGIN

DECLARE ccode VARCHAR(50);
DECLARE sprt CHAR(1) DEFAULT "/";

DECLARE m VARCHAR(50);
DECLARE n INTEGER;

SET ccode = (SELECT M_CustomerCode FROM m_customer WHERE M_CustomerID = cid);
SET m = (SELECT MAX(L_OfferNumber) FROM l_offer WHERE L_OfferIsActive = "Y" ANd L_OfferM_CustomerID = cid AND L_OfferNumber LIKE CONCAT(ccode, "%"));
IF m IS NULL THEN
    SET n = 1;
ELSE
    SET n = ROUND( REPLACE(m, CONCAT(ccode, sprt), "") ) + 1;
END IF;

RETURN CONCAT(ccode, sprt, LPAD(n, 3, "0"));

END