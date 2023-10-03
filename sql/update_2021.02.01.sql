alter table p_purchiveorder
add column P_PurchiveOrderPaid double not null default 0 after P_PurchiveOrderGrandTotal,
add column P_PurchiveOrderUnpaid double not null default 0 after	P_PurchiveOrderPaid,
add column P_PurchiveOrderLunas char(1) not null default "N" after P_PurchiveOrderUnpaid,
add index(P_PurchiveOrderPaid), add index(P_PurchiveOrderUnpaid), add index(P_PurchiveOrderLunas);

SET GLOBAL log_bin_trust_function_creators = 1;

DELIMITER ;;
CREATE FUNCTION `fn_invoice_payable_30_adminer_60176a301e1be` () RETURNS double
BEGIN

DECLARE total DOUBLE;
DECLARE maxdate DATE;

SET maxdate = DATE_ADD(date(now()), INTERVAL 30 DAY);
SET total = (SELECT SUM(L_InvoiceUnpaid)
FROM l_invoice
WHERE L_InvoiceLunas = "N"
AND L_InvoiceMetaActive = "Y"
AND L_InvoiceDueDate <= maxdate);

RETURN total;

END;;
DELIMITER ;
DROP FUNCTION `fn_invoice_payable_30_adminer_60176a301e1be`;
DROP FUNCTION `fn_invoice_payable_30`;
DELIMITER ;;
CREATE FUNCTION `fn_invoice_payable_30` () RETURNS double
BEGIN

DECLARE total DOUBLE;
DECLARE maxdate DATE;

SET maxdate = DATE_ADD(date(now()), INTERVAL 30 DAY);
SET total = (SELECT SUM(L_InvoiceUnpaid)
FROM l_invoice
WHERE L_InvoiceLunas = "N"
AND L_InvoiceMetaActive = "Y"
AND L_InvoiceDueDate <= maxdate);

RETURN total;

END;;
DELIMITER ;

DELIMITER ;;
CREATE FUNCTION `fn_purchase_debt_30_adminer_60176a4255666` () RETURNS double
BEGIN

DECLARE total DOUBLE;
DECLARE maxdate DATE;

SET maxdate = DATE_ADD(date(now()), INTERVAL 30 DAY);
SET total = (SELECT SUM(P_PurchiveOrderUnpaid)
FROM p_purchiveorder
WHERE P_PurchiveOrderLunas = "N"
AND P_PurchiveOrderMetaActive = "Y");
-- AND L_InvoiceDueDate <= maxdate);

RETURN total;

END;;
DELIMITER ;
DROP FUNCTION `fn_purchase_debt_30_adminer_60176a4255666`;
DROP FUNCTION `fn_purchase_debt_30`;
DELIMITER ;;
CREATE FUNCTION `fn_purchase_debt_30` () RETURNS double
BEGIN

DECLARE total DOUBLE;
DECLARE maxdate DATE;

SET maxdate = DATE_ADD(date(now()), INTERVAL 30 DAY);
SET total = (SELECT SUM(P_PurchiveOrderUnpaid)
FROM p_purchiveorder
WHERE P_PurchiveOrderLunas = "N"
AND P_PurchiveOrderMetaActive = "Y");
-- AND L_InvoiceDueDate <= maxdate);

RETURN total;

END;;
DELIMITER ;