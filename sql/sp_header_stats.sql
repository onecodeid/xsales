DROP PROCEDURE `sp_header_stats`;
DELIMITER ;;
CREATE PROCEDURE `sp_header_stats` (IN `what` varchar(15))
BEGIN

DECLARE invoice_total VARCHAR(255);
DECLARE very_start_date DATE DEFAULT "2023-01-01";
DECLARE behind30 DATE;
DECLARE payment DOUBLE;

SET behind30 = DATE(DATE_SUB(now(), INTERVAL 30 DAY));

IF `what` = 'SALES' THEN
    SET payment = (SELECT SUM(F_Pay2Total)
                    FROM f_pay2
                    JOIN l_invoice ON F_Pay2L_InvoiceID = L_InvoiceID AND L_InvoiceLunas = "Y"
                    WHERE F_Pay2IsActive = "Y"
                    AND F_Pay2Date >= behind30);
    IF payment IS NULL THEN SET payment = 0; END IF;

    SET invoice_total = 
        (SELECT JSON_OBJECT('total_unpaid', SUM(L_InvoiceUnpaid),
                            'total_due', SUM(IF(L_InvoiceDueDate<DATE(now()), L_InvoiceUnpaid, 0)),
                            'total_payment', payment)
                            FROM l_invoice
                            WHERE L_InvoiceIsActive = "Y"
                            AND L_InvoiceUnpaid > 0 AND L_InvoiceDate >= very_start_date);

    SELECT invoice_total as `stats`;

ELSEIF `what` = 'PURCHASE' THEN
    SET payment = (SELECT SUM(F_BillPay2Total)
                    FROM f_billpay2
                    JOIN f_bill ON F_BillPay2F_BillID = F_BillID AND F_BillLunas = "Y"
                    WHERE F_BillPay2IsActive = "Y"
                    AND F_BillPay2Date >= behind30);
    IF payment IS NULL THEN SET payment = 0; END IF;

    SET invoice_total =
        (SELECT JSON_OBJECT('total_unpaid', SUM(IF(F_BillDueDate>=DATE(very_start_date), F_BillUnpaid, 0)),
                        'total_due', SUM(IF(F_BillDueDate<DATE(now()) AND F_BillDate>=DATE(very_start_date), F_BillUnpaid, 0)),
                        'total_payment', payment)
                        FROM f_bill
                        WHERE F_BillIsActive = "Y"
                        AND F_BillUnpaid > 0);

    SELECT invoice_total as `stats`;
END IF;

END;;
DELIMITER ;