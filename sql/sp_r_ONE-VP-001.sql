BEGIN

(SELECT A_CustomerID, A_CustomerName, L_InvoiceID, L_InvoiceDate, L_InvoiceDueDate, L_InvoiceNumber, L_InvoiceGrandTotal, 
	L_InvoicePaid as paid,
	IFNULL(SUM(IF(M_PaymentTypeID = 1, F_Payment2DetailAmount, 0)), 0) as cash,
	IFNULL(SUM(IF(M_PaymentTypeID = 2, F_Payment2DetailAmount, 0)), 0) as transfer,
	IFNULL(SUM(IF(M_PaymentTypeID = 3, F_Payment2DetailAmount, 0)), 0) as bg,
	S_StaffID, CONCAT(S_StaffFirstName, ' ', S_StaffLastName) as S_StaffName, IFNULL(A_CustomerRegionName, "") as A_CustomerRegionName,
        CONCAT('[', GROUP_CONCAT( CONCAT('{"amount":"', F_Payment2DetailAmount,'", "date":"', F_Payment2Date,'", "type":"', 'CASH','"}') SEPARATOR ","), ']') as paids,
	IF (M_PaymentTypeID = 2, bb.M_BankName, IF(M_PaymentTypeID = 3, ba.M_BankName, '')) bank_name,
	IF (M_PaymentTypeID = 2, F_Payment2TransferDate, IF(M_PaymentTypeID = 3, F_Payment2GiroDate, '')) bank_date,
	IFNULL (F_Payment2GiroNumber, '') giro_number
FROM one_iv.l_invoice
LEFT JOIN f_payment2detail ON F_Payment2DetailL_InvoiceID = L_InvoiceID AND F_Payment2DetailIsActive = "Y"
LEFT JOIN f_payment2 ON F_Payment2DetailF_Payment2ID = F_Payment2ID
LEFT JOIN one_iv.m_paymenttype ON F_Payment2M_PaymentTypeID = M_PaymentTypeID
LEFT JOIN one_iv.a_bankaccount ON F_Payment2A_BankAccountID = A_BankAccountID
LEFT JOIN one_iv.m_bank ba ON F_Payment2M_BankID = ba.M_BankID
LEFT JOIN one_iv.m_bank bb ON A_BankAccountM_BankID = bb.M_BankID

JOIN one_iv.a_customer ON L_InvoiceA_CustomerID = A_CustomerID AND ((A_CustomerA_CustomerRegionID = regionid AND regionid > 0) OR regionid = 0)
LEFT JOIN one_iv.a_customerregion ON A_CustomerRegionID = A_CustomerA_CustomerRegionID
JOIN one_iv.s_staff ON L_InvoiceS_StaffID = S_StaffID
WHERE L_InvoiceMetaActive = 'Y'
AND L_InvoiceGrandTotal > 0
AND L_InvoiceS_StaffID = salesid
AND L_InvoiceDate BETWEEN sdate AND edate
AND L_InvoiceLunas = "N"
GROUP BY L_InvoiceID

ORDER BY A_CustomerName, L_InvoiceNumber);

END