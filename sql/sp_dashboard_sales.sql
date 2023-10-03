DROP PROCEDURE `sp_dashboard_sales`;
DELIMITER ;;
CREATE PROCEDURE `sp_dashboard_sales` (IN `section` varchar(50), IN `sdate` date, IN `edate` date, IN `staffid` INTEGER)
BEGIN

DECLARE psdate DATE;
DECLARE pedate DATE;
DECLARE account_sales_id INTEGER;
DECLARE account_hpp_id INTEGER;
DECLARE account_shipping_id INTEGER;

SET lc_time_names = 'id_ID';
SET account_sales_id = (SELECT fn_master_get_account_id("ACC.SALES"));
SET account_hpp_id = (SELECT fn_master_get_account_id("ACC.PURCHASE"));
SET account_shipping_id = (SELECT fn_master_get_account_id("ACC.SHIPPING"));

IF section = 'SALES.CUSTOMER.001' THEN
    SET psdate = DATE_SUB(sdate, INTERVAL 1 MONTH);
    SET pedate = LAST_DAY(psdate);

    SELECT IFNULL(SUM(IF(L_InvoiceDate BETWEEN sdate AND edate AND L_InvoiceIsNew = "Y", 1, 0)), 0) cmonth_customer, 
        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND pedate AND L_InvoiceIsNew = "Y", 1, 0)), 0) pmonth_customer,

        IFNULL(SUM(IF(L_InvoiceDate BETWEEN sdate AND edate AND L_InvoiceIsNew = "Y", L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0), 0)), 0) cmonth_omzet, 
        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND pedate AND L_InvoiceIsNew = "Y", L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0), 0)), 0) pmonth_omzet,

        IFNULL(SUM(IF(L_InvoiceDate BETWEEN sdate AND edate AND L_InvoiceIsNew = "N", L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0), 0)), 0) cmonth_omzet_repeat, 
        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND pedate AND L_InvoiceIsNew = "N", L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0), 0)), 0) pmonth_omzet_repeat,

        IFNULL(SUM(IF(L_InvoiceDate BETWEEN sdate AND edate AND L_InvoiceIsNew = "Y", IFNULL(j_sales, 0) - IFNULL(j_hpp, 0) - IFNULL(j_shipping, 0), 0)), 0) cmonth_profit, 
        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND pedate AND L_InvoiceIsNew = "Y", IFNULL(j_sales, 0) - IFNULL(j_hpp, 0) - IFNULL(j_shipping, 0), 0)), 0) pmonth_profit,

        IFNULL(SUM(IF(L_InvoiceDate BETWEEN sdate AND edate AND L_InvoiceIsNew = "N", IFNULL(j_sales, 0) - IFNULL(j_hpp, 0) - IFNULL(j_shipping, 0), 0)), 0) cmonth_profit_repeat, 
        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND pedate AND L_InvoiceIsNew = "N", IFNULL(j_sales, 0) - IFNULL(j_hpp, 0) - IFNULL(j_shipping, 0), 0)), 0) pmonth_profit_repeat,

        monthname(sdate) as month_name, year(sdate) as year_name
    FROM l_invoice
    LEFT JOIN (
        SELECT L_ReturL_InvoiceID retur_invoice, SUM(L_ReturSubTotalBeforePPN) retur_total
        FROM l_retur
        WHERE L_ReturIsActive = "Y"
        GROUP BY L_ReturL_InvoiceID
    ) retur ON L_InvoiceID = retur_invoice
    
    LEFT JOIN (
        SELECT SUM(IF(T_JournalDetailM_AccountID = account_sales_id, T_JournalDetailCredit - T_JournalDetailDebit, 0)) j_sales, T_JournalRefID j_ref,
            SUM(IF(T_JournalDetailM_AccountID = account_shipping_id, T_JournalDetailCredit - T_JournalDetailDebit, 0)) j_shipping
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalIsActive = "Y" AND T_JournalDate BETWEEN psdate AND edate
            AND T_JournalType = "J.20"
        WHERE T_JournalDetailIsActive = "Y" and (T_JournalDetailM_AccountID = account_sales_id OR T_JournalDetailM_AccountID = account_shipping_id)
        GROUP BY T_JournalRefID
    ) jsales ON j_ref = L_InvoiceID

    LEFT JOIN (
        SELECT IF(T_JournalDetailM_AccountID = account_hpp_id, T_JournalDetailDebit - T_JournalDetailCredit, 0) j_hpp, L_DeliveryL_InvoiceID j_invoice
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalIsActive = "Y" AND T_JournalDate BETWEEN psdate AND edate
            AND T_JournalType = "J.21"
        JOIN l_delivery ON T_JournalRefID = L_DeliveryID
        WHERE T_JournalDetailIsActive = "Y" and T_JournalDetailM_AccountID = account_hpp_id
        GROUP BY L_DeliveryL_InvoiceID
    ) jdlv ON j_invoice = L_InvoiceID

    WHERE L_InvoiceIsActive = "Y" AND L_InvoiceDate BETWEEN psdate AND edate
        AND ((L_InvoiceS_StaffID = staffid AND staffid <> 0) OR staffid = 0);

ELSEIF section = 'SALES.002' THEN

    SELECT SUM((L_InvoiceDetailSubTotal * (100-L_InvoiceDiscountTotal) / 100) - L_InvoiceDetailReturNominal - 0) omzet_nominal,
        SUM(L_InvoiceDetailReturNominal) retur_nominal,
        M_CategoryName category_name,
        monthname(sdate) as month_name, year(sdate) as year_name
    FROM l_invoicedetail
    JOIN l_invoice ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
        AND L_InvoiceDate BETWEEN sdate AND edate
        AND ((L_InvoiceS_StaffID = staffid AND staffid <> 0) OR staffid = 0)
        AND L_InvoiceIsActive = "Y"
    JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
    JOIN m_category ON M_ItemM_CategoryID = M_CategoryID
    WHERE L_InvoiceDetailIsActive = "Y"
    GROUP BY M_ItemM_CategoryID;

ELSEIF section = 'SALES.003' THEN

    SET psdate = DATE_SUB(sdate, INTERVAL 5 MONTH);

    SELECT 
        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND edate AND L_InvoiceIsNew = "Y", L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0), 0)), 0) omzet, 

        IFNULL(SUM(IF(L_InvoiceDate BETWEEN psdate AND edate AND L_InvoiceIsNew = "N", L_InvoiceGrandTotal + L_InvoiceDP - L_InvoiceShipping - L_InvoicePPN - IFNULL(retur_total, 0), 0)), 0) omzet_repeat, 

        monthname(l_invoicedate) as month_name, year(l_invoicedate) as year_name
    FROM l_invoice
    LEFT JOIN (
        SELECT L_ReturL_InvoiceID retur_invoice, SUM(L_ReturSubTotalBeforePPN) retur_total
        FROM l_retur
        WHERE L_ReturIsActive = "Y"
        GROUP BY L_ReturL_InvoiceID
    ) retur ON L_InvoiceID = retur_invoice

    WHERE L_InvoiceIsActive = "Y" AND L_InvoiceDate BETWEEN psdate AND edate
        AND ((L_InvoiceS_StaffID = staffid AND staffid <> 0) OR staffid = 0)
    GROUP BY month(L_InvoiceDate);

END IF;

END;;
DELIMITER ;

CALL sp_dashboard_sales("SALES.002", "2023-07-01", "2023-07-29", 0);