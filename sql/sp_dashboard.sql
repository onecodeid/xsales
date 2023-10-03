DROP PROCEDURE `sp_dashboard`;
DELIMITER ;;
CREATE PROCEDURE `sp_dashboard` (IN `section` varchar(25), IN `sdate` date, IN `edate` date)
BEGIN

DECLARE invoice_total VARCHAR(255);
DECLARE invoice_due_monthly TEXT;
DECLARE invoice_due_yearly TEXT;

DECLARE very_start_date DATE DEFAULT "2023-01-01";

IF section = 'SALES.001' THEN
    SELECT IFNULL(SUM(total), 0) grand_total, IFNULL(SUM(`target`), 0) grand_target,
        IFNULL(
            CONCAT("[", GROUP_CONCAT(JSON_OBJECT("sales_id", sales_id, "sales_name", sales_name, "sales_short_name", sales_short_name, "total", 
            IFNULL(total, 0), "target", IFNULL(`target`, 0))), "]"), "[]") omzets
    FROM (
        SELECT S_StaffID sales_id, S_StaffName sales_name, S_StaffShortName sales_short_name,
            IFNULL(SUM(L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0)), 0) total,
            S_StaffTargetMonth `target`
        FROM s_staff
        JOIN s_position ON S_StaffS_PositionID = S_PositionID 
            AND (S_PositionCode = 'POS.ADMIN')
        LEFT JOIN l_invoice ON L_InvoiceS_StaffID = S_StaffID
            AND L_InvoiceIsActive = "Y"
            AND L_InvoiceDate BETWEEN sdate AND edate
        LEFT JOIN (
            SELECT L_ReturL_InvoiceID retur_invoice, SUM(L_ReturSubTotalBeforePPN) retur_total
            FROM l_retur
            WHERE L_ReturIsActive = "Y"
            GROUP BY L_ReturL_InvoiceID
        ) retur ON L_InvoiceID = retur_invoice

        WHERE S_StaffIsActive = "Y"
        GROUP BY S_StaffID
    ) x;

-- ELSEIF section = 'FINANCE.001' THEN


--     SELECT DISTINCT M_AccountID accid
--     FROM m_account
--     JOIN m_accountreport ON M_AccountReportIsActive = "Y"
--         AND ((M_AccountReportM_AccountID = M_AccountID AND M_AccountReportM_AccountID <> 0)
--             OR (M_AccountReportM_AccountGroupID = M_AccountGroupID AND M_AccountReportM_AccountGroupID <> 0))
--         AND M_AccountReportType LIKE 'CASHFLOW.%'
--     WHERE M_AccountIsActive "Y"
--     select * from m_accountreport where m_accountreporttype like 'CASHFLOW%';

ELSEIF section = 'FINANCE.002.M' THEN

    SET invoice_total = (SELECT JSON_OBJECT('total_unpaid', SUM(IF(L_InvoiceDueDate>=DATE(very_start_date), L_InvoiceUnpaid, 0)),
                        'total_due', SUM(IF(L_InvoiceDueDate<DATE(now()) AND L_InvoiceDueDate>=DATE(very_start_date), L_InvoiceUnpaid, 0)))
                        FROM l_invoice
                        WHERE L_InvoiceIsActive = "Y"
                        AND L_InvoiceUnpaid > 0);

    SET @n = 6;
    SET @sdate0 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 MONTH), "%Y-%m-01");
    SET @sdate1 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate2 = DATE_FORMAT(now(), "%Y-%m-01");
    SET @sdate3 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate4 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), "%Y-%m-01");

    SET @edate0 = DATE_FORMAT(LAST_DAY(@sdate0), "%Y-%m-%d");
    SET @edate1 = DATE_FORMAT(LAST_DAY(@sdate1), "%Y-%m-%d");
    SET @edate2 = DATE_FORMAT(LAST_DAY(@sdate2), "%Y-%m-%d");
    SET @edate3 = DATE_FORMAT(LAST_DAY(@sdate3), "%Y-%m-%d");
    SET @edate4 = DATE_FORMAT(LAST_DAY(@sdate4), "%Y-%m-%d");
    SET @edate5 = DATE_FORMAT(DATE_ADD(@edate4, INTERVAL 1 DAY), "%Y-%m-%d");

    SELECT DATE_FORMAT(@sdate0, "%b %y") date_before, DATE_FORMAT(@edate5, "%b %y") date_after,
        JSON_ARRAY(DATE_FORMAT(@sdate0, "%b %y"), DATE_FORMAT(@sdate1, "%b %y"),
        DATE_FORMAT(@sdate2, "%b %y"), DATE_FORMAT(@sdate3, "%b %y"),
        DATE_FORMAT(@sdate4, "%b %y"), DATE_FORMAT(@edate5, "%b %y") ) periods,
        
        CONCAT('[', GROUP_CONCAT(JSON_OBJECT('period', date_month, 'total', total_unpaid)),']') datas,
        invoice_total summary
        FROM (
            SELECT DATE_FORMAT(datex, "%b %y") date_month, SUM(unpaidx) total_unpaid
            FROM (
                SELECT IF(L_InvoiceDueDate < @sdate1, @sdate0, IF(L_InvoiceDueDate > @edate4, @edate5, L_InvoiceDueDate)) datex, L_InvoiceUnpaid unpaidx
                FROM l_invoice
                WHERE L_InvoiceIsActive = "Y"
                AND L_InvoiceUnpaid > 0
                AND L_InvoiceDate >= very_start_date
            ) x GROUP BY LEFT(datex, 7)
        ) y;

ELSEIF section = 'FINANCE.002.D' THEN

    SET very_start_date = DATE_FORMAT(sdate, "%Y-%m-01");
    SET invoice_total = (SELECT JSON_OBJECT('total_unpaid', SUM(L_InvoiceUnPaid),
                        'total_due', SUM(IF(L_InvoiceDueDate<DATE(now()), L_InvoiceUnpaid, 0)))
                        FROM l_invoice
                        WHERE L_InvoiceIsActive = "Y"
                        AND L_InvoiceUnpaid > 0 AND L_InvoiceDate BETWEEN sdate AND edate);

    SET @n = 6;
    SET @sdate0 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 MONTH), "%Y-%m-01");
    SET @sdate1 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate2 = DATE_FORMAT(now(), "%Y-%m-01");
    SET @sdate3 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate4 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), "%Y-%m-01");

    SET @edate0 = DATE_FORMAT(LAST_DAY(@sdate0), "%Y-%m-%d");
    SET @edate1 = DATE_FORMAT(LAST_DAY(@sdate1), "%Y-%m-%d");
    SET @edate2 = DATE_FORMAT(LAST_DAY(@sdate2), "%Y-%m-%d");
    SET @edate3 = DATE_FORMAT(LAST_DAY(@sdate3), "%Y-%m-%d");
    SET @edate4 = DATE_FORMAT(LAST_DAY(@sdate4), "%Y-%m-%d");
    SET @edate5 = DATE_FORMAT(DATE_ADD(@edate4, INTERVAL 1 DAY), "%Y-%m-%d");

    SELECT 
        JSON_ARRAY(DATE_FORMAT(now(), "Jan %y"), DATE_FORMAT(now(), "Feb %y"),
        DATE_FORMAT(now(), "Mar %y"), DATE_FORMAT(now(), "Apr %y"),
        DATE_FORMAT(now(), "Mei %y"), DATE_FORMAT(now(), "Jun %y"),
        DATE_FORMAT(now(), "Jul %y"), DATE_FORMAT(now(), "Agu %y"),
        DATE_FORMAT(now(), "Sep %y"), DATE_FORMAT(now(), "Okt %y"),
        DATE_FORMAT(now(), "Nop %y"), DATE_FORMAT(now(), "Des %y") ) periods,
        
        CONCAT('[', GROUP_CONCAT(JSON_OBJECT('period', date_month, 'total', total_unpaid)),']') datas,
        invoice_total summary
        FROM (
            SELECT DATE_FORMAT(datex, "%b %y") date_month, SUM(unpaidx) total_unpaid
            FROM (
                SELECT L_InvoiceDate datex, L_InvoiceUnpaid unpaidx
                FROM l_invoice
                WHERE L_InvoiceIsActive = "Y"
                AND L_InvoiceUnpaid > 0
                AND L_InvoiceDate BETWEEN DATE_FORMAT(now(), "%Y-01-01") AND DATE_FORMAT(now(), "%Y-12-31")
            ) x GROUP BY LEFT(datex, 7)
        ) y;

ELSEIF section = 'FINANCE.003.M' THEN

    SET @bill_total = (SELECT JSON_OBJECT('total_unpaid', SUM(IF(F_BillDueDate>=DATE(very_start_date), F_BillUnpaid, 0)),
                        'total_due', SUM(IF(F_BillDueDate<DATE(now()) AND F_BillDueDate>=DATE(very_start_date), F_BillUnpaid, 0)))
                        FROM f_bill
                        WHERE F_BillIsActive = "Y"
                        AND F_BillUnpaid > 0);

    SET @n = 6;
    SET @sdate0 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 MONTH), "%Y-%m-01");
    SET @sdate1 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate2 = DATE_FORMAT(now(), "%Y-%m-01");
    SET @sdate3 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate4 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), "%Y-%m-01");

    SET @edate0 = DATE_FORMAT(LAST_DAY(@sdate0), "%Y-%m-%d");
    SET @edate1 = DATE_FORMAT(LAST_DAY(@sdate1), "%Y-%m-%d");
    SET @edate2 = DATE_FORMAT(LAST_DAY(@sdate2), "%Y-%m-%d");
    SET @edate3 = DATE_FORMAT(LAST_DAY(@sdate3), "%Y-%m-%d");
    SET @edate4 = DATE_FORMAT(LAST_DAY(@sdate4), "%Y-%m-%d");
    SET @edate5 = DATE_FORMAT(DATE_ADD(@edate4, INTERVAL 1 DAY), "%Y-%m-%d");

    SELECT DATE_FORMAT(@sdate0, "%b %y") date_before, DATE_FORMAT(@edate5, "%b %y") date_after,
        JSON_ARRAY(DATE_FORMAT(@sdate0, "%b %y"), DATE_FORMAT(@sdate1, "%b %y"),
        DATE_FORMAT(@sdate2, "%b %y"), DATE_FORMAT(@sdate3, "%b %y"),
        DATE_FORMAT(@sdate4, "%b %y"), DATE_FORMAT(@edate5, "%b %y") ) periods,
        
        CONCAT('[', GROUP_CONCAT(JSON_OBJECT('period', date_month, 'total', total_unpaid)),']') datas,
        @bill_total summary
        FROM (
            SELECT DATE_FORMAT(datex, "%b %y") date_month, SUM(unpaidx) total_unpaid
            FROM (
                SELECT IF(F_BillDueDate < @sdate1, @sdate0, IF(F_BillDueDate > @edate4, @edate5, F_BillDueDate)) datex, F_BillUnpaid unpaidx
                FROM f_bill
                WHERE F_BillIsActive = "Y"
                AND F_BillUnpaid > 0
                AND F_BillDate >= very_start_date
            ) x GROUP BY LEFT(datex, 7)
        ) y;

ELSEIF section = 'FINANCE.003.D' THEN

    SET @bill_total = (SELECT JSON_OBJECT('total_unpaid', F_BillUnpaid,
                        'total_due', SUM(IF(F_BillDueDate<DATE(now()), F_BillUnpaid, 0)))
                        FROM f_bill
                        WHERE F_BillIsActive = "Y"
                        AND F_BillUnpaid > 0 AND F_BillDate BETWEEN sdate AND edate);

    SET @year = year(now());
    SET @n = 6;
    SET @sdate0 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 MONTH), "%Y-%m-01");
    SET @sdate1 = DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate2 = DATE_FORMAT(now(), "%Y-%m-01");
    SET @sdate3 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 MONTH), "%Y-%m-01");
    SET @sdate4 = DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 MONTH), "%Y-%m-01");

    SET @edate0 = DATE_FORMAT(LAST_DAY(@sdate0), "%Y-%m-%d");
    SET @edate1 = DATE_FORMAT(LAST_DAY(@sdate1), "%Y-%m-%d");
    SET @edate2 = DATE_FORMAT(LAST_DAY(@sdate2), "%Y-%m-%d");
    SET @edate3 = DATE_FORMAT(LAST_DAY(@sdate3), "%Y-%m-%d");
    SET @edate4 = DATE_FORMAT(LAST_DAY(@sdate4), "%Y-%m-%d");
    SET @edate5 = DATE_FORMAT(DATE_ADD(@edate4, INTERVAL 1 DAY), "%Y-%m-%d");

    SELECT 
        JSON_ARRAY(DATE_FORMAT(now(), "Jan %y"), DATE_FORMAT(now(), "Feb %y"),
        DATE_FORMAT(now(), "Mar %y"), DATE_FORMAT(now(), "Apr %y"),
        DATE_FORMAT(now(), "Mei %y"), DATE_FORMAT(now(), "Jun %y"),
        DATE_FORMAT(now(), "Jul %y"), DATE_FORMAT(now(), "Agu %y"),
        DATE_FORMAT(now(), "Sep %y"), DATE_FORMAT(now(), "Okt %y"),
        DATE_FORMAT(now(), "Nop %y"), DATE_FORMAT(now(), "Des %y") ) periods,
        
        CONCAT('[', GROUP_CONCAT(JSON_OBJECT('period', date_month, 'total', total_unpaid)),']') datas,
        @bill_total summary
        FROM (
            SELECT DATE_FORMAT(datex, "%b %y") date_month, SUM(unpaidx) total_unpaid
            FROM (
                SELECT F_BillDate datex, F_BillUnpaid unpaidx
                FROM f_bill
                WHERE F_BillIsActive = "Y"
                AND F_BillUnpaid > 0
                AND F_BillDate BETWEEN DATE_FORMAT(now(), "%Y-01-01") AND DATE_FORMAT(now(), "%Y-12-31")
            ) x GROUP BY LEFT(datex, 7)
        ) y;

ELSEIF section = 'FINANCE.004.M' THEN

    SELECT M_AccountID account_id, M_AccountName account_name, SUM(T_JournalDetailDebit) jdebit, SUM(T_journalDetailCredit) jcredit
    FROM t_journaldetail
    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID ANd T_JournalDate BETWEEN DATE_FORMAT(now(), "%Y-%m-01") AND DATE(now())
    JOIN m_account ON T_journaldetailM_AccountID = M_AccountID AND M_AccountM_AccountGroupID = 16
    WHERE T_JournalDetailIsActive = "Y"
    GROUP BY M_AccountID
    ORDER BY jdebit DESC;

ELSEIF section = 'FINANCE.004.Y' THEN

    SELECT M_AccountID account_id, M_AccountName account_name, SUM(T_JournalDetailDebit) jdebit, SUM(T_journalDetailCredit) jcredit
    FROM t_journaldetail
    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID ANd T_JournalDate BETWEEN DATE_FORMAT(now(), "%Y-01-01") AND DATE(now())
    JOIN m_account ON T_journaldetailM_AccountID = M_AccountID AND M_AccountM_AccountGroupID = 16
    WHERE T_JournalDetailIsActive = "Y"
    GROUP BY M_AccountID
    ORDER BY jdebit DESC;

ELSEIF section = 'FINANCE.004.D' THEN

    SELECT M_AccountID account_id, M_AccountName account_name, SUM(T_JournalDetailDebit) jdebit, SUM(T_journalDetailCredit) jcredit,
        M_AccountM_AccountGroupID group_id
    FROM t_journaldetail
    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID ANd T_JournalDate BETWEEN sdate AND edate
    JOIN m_account ON T_journaldetailM_AccountID = M_AccountID AND (M_AccountM_AccountGroupID = 16 OR M_AccountM_AccountGroupID = 15)
    WHERE T_JournalDetailIsActive = "Y"
    GROUP BY M_AccountID
    ORDER BY group_id DESC, jdebit DESC;

ELSEIF section = "RATIO.CURRENT" OR section = "RATIO.QUICK" OR section = "RATIO.CASH" THEN
    CALL sp_dashboard_ratio(sdate, edate, section);

ELSEIF section = "RATIO.ACT.INVENTORY" OR section = "RATIO.ACT.FIXED" OR section = "RATIO.ACT.RECEIVABLE"
    OR section = "RATIO.ACT.ACTIVA.TOTAL" OR section = "RATIO.ACT.CAPITAL" THEN
    CALL sp_dashboard_ratio_activity(sdate, edate, section);

END IF;


END;;
DELIMITER ;