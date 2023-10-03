DROP PROCEDURE `sp_dashboard_ratio_activity`;
DELIMITER ;;
CREATE PROCEDURE `sp_dashboard_ratio_activity` (IN `sdate` date, IN `edate` date, IN `thetype` varchar(50))
BEGIN

DECLARE years VARCHAR(4);
DECLARE thetop VARCHAR(50);
DECLARE thebottom VARCHAR(50);

SET thetop = (SELECT M_ACcountReportTypeName FROM m_accountreporttype WHERE M_AccountReportTypeGroup = thetype AND M_ACcountReportTypeIsActive = "Y" AND M_AccountReportTypeAttr = "TOP" LIMIT 1);
SET thebottom = (SELECT M_ACcountReportTypeName FROM m_accountreporttype WHERE M_AccountReportTypeGroup = thetype AND M_ACcountReportTypeIsActive = "Y" AND M_AccountReportTypeAttr = "BOTTOM" LIMIT 1);

set years = year(edate);
set sdate = DATE_FORMAT(sdate, "%Y-01-01");

SELECT *, (`top` / ((`down`+`bdown`)/2)) rst, 'x' as unit FROM (
    SELECT 
    SUM(IF(report_type=thetop, journal_balance, 0)) `top`,
    SUM(IF(report_type=thebottom, journal_balance, 0)) `down`,
    SUM(IF(report_type=thetop, journal_start_balance, 0)) `btop`,
    SUM(IF(report_type=thebottom, journal_start_balance, 0)) `bdown`

    FROM (
        SELECT *,
            (IF(account_type = "A", b_debit - b_credit + j_debit - j_credit, b_credit - b_debit + j_credit - j_debit) - accum_amount) journal_balance,
            (IF(account_type = "A", jb_debit - jb_credit, jb_credit - jb_debit) - accum_amount) journal_start_balance 
        FROM (
            select sum(IFNULL(j_debit, 0)) j_debit, sum(IFNULL(j_credit, 0)) j_credit,
                sum(IFNULL(jb_debit, 0)) jb_debit, sum(IFNULL(jb_credit, 0)) jb_credit,
            0 b_credit, 0 b_debit, IFNULL(accum_amount, 0) accum_amount,
            M_AccountReportType report_type, M_AccountReportTitle report_title, a.M_AccountType account_type,
            b.m_accountcode bcode

            from m_account a
            join m_accountgroup on a.m_accountm_accountgroupid = m_accountgroupid

            JOIN m_accountreport ON ((M_AccountReportM_AccountID = M_AccountID AND M_AccountReportM_AccountID <> 0) OR (M_AccountReportM_AccountGroupID = M_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
            AND M_ACcountReportType LIKE CONCAT(thetype,".%") AND M_AccountReportIsActive = "Y"

            left join m_account b on a.m_accountcode like concat(b.m_accountcode, '%') 
            and a.m_accountcode <> b.m_accountcode
            and b.m_accountisactive = "Y"

            left join (
                select t_journaldetailm_accountid jacc_id, sum(t_journaldetaildebit) j_debit, sum(t_journaldetailcredit) j_credit
                from t_journaldetail
                join t_journal on t_journaldetailt_journalid = t_journalid
                and t_journalisactive = "Y" and t_journaldate between sdate and edate

                where t_journaldetailisactive = "Y"
                group by t_journaldetailm_accountid
            ) j on jacc_id = a.m_accountid

            left join (
                select t_journaldetailm_accountid jbacc_id, sum(t_journaldetaildebit) jb_debit, sum(t_journaldetailcredit) jb_credit
                from t_journaldetail
                join t_journal on t_journaldetailt_journalid = t_journalid
                and t_journalisactive = "Y" and t_journaldate between sdate and edate and t_journaltype = "J.30"

                where t_journaldetailisactive = "Y"
                group by t_journaldetailm_accountid
            ) jb on jbacc_id = a.m_accountid

            left join (
                select m_accumulationm_accountid accum_acc_id, sum(t_journaldetailcredit-t_journaldetaildebit) accum_amount
                from t_journaldetail
                join t_journal on t_journaldetailt_journalid = t_journalid
                and t_journalisactive = "Y" and t_journaldate between sdate and edate
                join m_accumulation on t_journaldetailm_accountid = m_accumulationaccm_accountid
                where t_journaldetailisactive = "Y"
                group by m_accumulationaccm_accountid
            ) accum on accum_acc_id = a.m_accountid

            left join t_balance on t_balancem_accountid = a.m_accountid and t_balanceisactive = "Y" and t_balanceyear = years

            where a.m_accountisactive = "Y"
            -- patch
            and a.m_accountcode NOT LIKE "1-10101%"
            group by left(a.m_accountcode, 7)
            order by a.m_accountcode asc
        ) xyz
        WHERE j_debit > 0 OR j_credit > 0
    ) xxyy
) zz;

END
;;
DELIMITER ;