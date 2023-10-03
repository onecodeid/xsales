BEGIN

DECLARE banks TEXT;

SET banks = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("no", M_CustomerBankNumber, "name", M_CustomerBankName, 
                "bank", JSON_OBJECT("bank_id", M_BankID, "bank_name", M_BankName)) separator ","), "]")
            FROM m_customerbank
            JOIN m_bank ON M_CustomerBankM_BankID = M_BankID
            WHERE M_CustomerBankM_CustomerID = customerid
            AND M_CustomerBankIsActive="Y");
IF banks IS NULL THEN SET banks = "[]"; END IF;

RETURN banks;

END