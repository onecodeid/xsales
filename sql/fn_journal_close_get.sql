BEGIN

DECLARE jtype VARCHAR(10);
DECLARE rst VARCHAR(100);

IF what = 'PROFITLOSS' THEN
    SET jtype = "J.90";
END IF;

SET rst = (SELECT JSON_OBJECT("credit", IFNULL(T_JournalCloseDetailCredit, 0), "debit", IFNULL(T_JournalCloseDetailDebit, 0))
FROM t_journalclosedetail
JOIN m_account ON T_JournalCloseDetailM_AccountID = M_AccountID AND M_AccountSystemCode = CONCAT("ACC.", what)
JOIN t_journalclose ON T_JournalCloseDetailT_JournalCloseID = T_JournalCloseID
    AND T_JournalCloseISActive = "Y" AND T_JournalCloseType = jtype AND T_JournalCloseDate = pdate
WHERE T_JournalCloseDetailIsActive = "Y");

IF rst IS NULL THEN SET rst = JSON_OBJECT("credit", 0, "debit", 0); END IF;

RETURN rst;

END