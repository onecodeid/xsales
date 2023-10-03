DROP PROCEDURE IF EXISTS `sp_journal_cashflow`;
DELIMITER ;;
CREATE PROCEDURE `sp_journal_cashflow` (IN `journalid` int)
BEGIN

DECLARE jtype VARCHAR(10);
DECLARE jcash VARCHAR(25);
DECLARE ncash INTEGER;
DECLARE ncurr INTEGER;
DECLARE nothr INTEGER;
DECLARE ncost INTEGER;
DECLARE nincm_othr INTEGER;
DECLARE nfixd INTEGER;

SET jtype = (SELECT T_JournalType FROM t_journal WHERE T_JournalID = journalid);

SET ncash = (SELECT COUNT(M_AccountID) FROM t_journaldetail JOIN m_account ON M_AccountID = T_JournalDetailM_AccountID ANd M_AccountCode LIKE '1-100%'
    WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y");
SET ncurr = (SELECT COUNT(M_AccountID) FROM t_journaldetail JOIN m_account ON M_AccountID = T_JournalDetailM_AccountID ANd M_AccountCode LIKE '1-101%'
    WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y");
SET nothr = (SELECT COUNT(M_AccountID) FROM t_journaldetail JOIN m_account ON M_AccountID = T_JournalDetailM_AccountID ANd M_AccountCode LIKE '1-103%'
    WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y");
SET nfixd = (SELECT COUNT(M_AccountID) FROM t_journaldetail JOIN m_account ON M_AccountID = T_JournalDetailM_AccountID ANd M_AccountCode LIKE '1-107%'
    WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y");
SET ncost = (SELECT COUNT(M_AccountID) FROM t_journaldetail JOIN m_account ON M_AccountID = T_JournalDetailM_AccountID ANd M_AccountCode LIKE '6-600%'
    WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y");
SET nincm_othr = (SELECT COUNT(M_AccountID) FROM t_journaldetail JOIN m_account ON M_AccountID = T_JournalDetailM_AccountID ANd M_AccountCode LIKE '7-700%'
    WHERE T_JournalDetailT_JournalID = journalid AND T_JournalDetailIsActive = "Y");

IF ncash > 0 THEN
    IF jtype="J.11" OR jtype="J.15" OR jtype="J.17" THEN
        SET jcash = "CF.OPR.CUSTOMER.IN";
    ELSEIF jtype="J.12" OR jtype="J.16" OR jtype="J.19" THEN
        SET jcash = "CF.OPR.CUSTOMER.OUT";
    ELSEIF jtype="J.30" THEN
        SET jcash = "CF.FINANCE.BALANCE";
    ELSEIF ncurr > 0 THEN
        SET jcash = "CF.OPR.ASSET.CURRENT";
    ELSEIF nfixd > 0 THEN
        SET jcash = "CF.INVEST.FIXED";
    ELSEIF nothr > 0 THEN
        SET jcash = "CF.OPR.ASSET.CURRENT";
    ELSEIF ncost > 0 THEN
        SET jcash = "CF.OPR.COST";
    ELSEIF nincm_othr > 0 THEN
        SET jcash = "CF.OPR.INCOME.OTHER";
    END IF;
END IF;


UPDATE t_journal SET T_JournalCashFlow = jcash WHERE T_JournalID = journalid AND T_JournalIsActive = "Y";
-- SELECT journalid, jcash, jtype, ncash, ncurr, nothr, ncost, nincm_othr;

END;;
DELIMITER ;