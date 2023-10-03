BEGIN

DECLARE d_id INTEGER;
DECLARE d_bill INTEGER;
DECLARE d_dp_id INTEGER;
DECLARE d_retur_id INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_change DOUBLE;
DECLARE d_type_id INTEGER;
DECLARE d_type_code VARCHAR(15);
DECLARE journal_id INTEGER;
DECLARE journal_post CHAR(1);
DECLARE finished INTEGER DEFAULT 0;

DECLARE payment_cursor
    CURSOR FOR
        SELECT F_BillPaymentDetailID, F_BillPaymentDetailF_BillID, F_BillPaymentDetailF_BillDpID,
        F_BillPaymentDetailP_PurchaseReturID, F_BillPaymentDetailAmount, F_BillPaymentDetailChange,
        F_BillPaymentDetailM_PaymentDetailID, M_PaymentDetailCode
        FROM f_billpaymentdetail
        JOIN m_paymentdetail ON F_BillPaymentDetailM_PaymentDetailID = M_PaymentDetailID
        WHERE F_BillPaymentDetailF_BillPaymentID = paymentid
        AND F_BillPaymentDetailIsActive = "Y";

DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

OPEN payment_cursor;

getPayment: LOOP
    FETCH payment_cursor INTO d_id, d_bill, d_dp_id, d_retur_id, d_amount, d_change,
    d_type_id,d_type_code;
    IF finished = 1 THEN
        LEAVE getPayment;
    END IF;

    UPDATE f_bill
    SET F_BillPaid = F_BillPaid - d_amount
    WHERE F_BillID = d_bill;

    IF d_type_code = "PAY.DP" THEN
        UPDATE f_billdp SET F_BillDpUsed = F_BillDpUsed - d_amount WHERE F_BillDpID = d_dp_id;
    END IF;

    IF d_type_code = "PAY.RETUR" THEN
        UPDATE p_purchaseretur SET P_PurchaseReturUsed = P_PurchaseReturUsed - d_amount WHERE P_PurchaseReturID = d_retur_id;
    END IF;

    UPDATE f_billpaymentdetail
    SET F_BillPaymentDetailIsActive = "N" 
    WHERE F_BillPaymentDetailID = d_id;

END LOOP getPayment;
CLOSE payment_cursor;

UPDATE f_billpayment SET F_BillPaymentIsActive = "N" WHERE F_BillPaymentID = paymentid;
-- SELECT "OK" status, JSON_OBJECT("payment_id", paymentid) data;

END