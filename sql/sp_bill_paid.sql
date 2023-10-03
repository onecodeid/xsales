BEGIN

DECLARE total DOUBLE;
-- DECLARE total_retur DOUBLE;
DECLARE type_dp_id INTEGER;

SET type_dp_id = (SELECT M_PaymentDetailID FROM m_paymentdetail WHERE M_PaymentDetailCode = "PAY.DP" AND M_PaymentDetailIsActive = "Y");
IF billid <> 0 THEN
    SET total = (SELECT SUM(F_BillPaymentDetailAmount)
    -- , IFNULL(SUM(IF(F_BillPaymentDetailM_PaymentDetailID=type_dp_id,F_BillPaymentDetailAmount,0)), 0)
    -- INTO total, total_dp
    FROM f_billpaymentdetail 
    WHERE F_BillPaymentDetailIsActive = "Y" AND F_BillPaymentDetailF_BillID = billid);
    
    
    UPDATE f_bill
    SET F_BillPaid = total
    WHERE F_BillID = billid;
END IF;

END