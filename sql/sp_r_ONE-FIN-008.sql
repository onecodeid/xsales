DROP PROCEDURE IF EXISTS `sp_r_ONE-FIN-008-2`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-FIN-008-2` (IN `sdate` date, IN `edate` date)
BEGIN

SELECT t_journalcashflow flow_code, sum(T_JournalDetailCredit) jcredit, sum(T_journalDetailDebit) jdebit
FROM t_journaldetail
JOIN t_journal on t_journaldetailt_journalid = t_journalid 
and t_journaldate between sdate and edate and t_journalisactive = "Y"
and t_journalcashflow LIKE "CF%"
JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID and M_AccountM_AccountGroupID = 1
where t_journaldetailisactive = "Y"
group by t_journalcashflow;

END;;
DELIMITER ;