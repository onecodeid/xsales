BEGIN




DECLARE finished INTEGER DEFAULT 0;
DECLARE invoiceid INTEGER DEFAULT 0;
declare invoicedate date;
declare invoicetotal double;

	-- declare cursor for employee email
	DEClARE curInvoice
		CURSOR FOR 
			SELECT L_InvoiceID, L_InvoiceDate, L_InvoiceUnpaid FROM l_invoice
            WHERE L_InvoiceIsActive = "Y" AND L_InvoiceDate < "2022-12-01";

	-- declare NOT FOUND handler
	DECLARE CONTINUE HANDLER 
        FOR NOT FOUND SET finished = 1;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;


START TRANSACTION;

	OPEN curInvoice;

	getInvoice: LOOP
		FETCH curInvoice INTO invoiceid, invoicedate, invoicetotal;
		IF finished = 1 THEN 
			LEAVE getInvoice;
		END IF;

		-- build email list
		-- CALL sp_master_item_last_purchase_0(itemid);
        -- {"pay_date":"2023-04-08","pay_amount":"90000","pay_disc":"0","pay_discrp":"0","pay_discamount":0,"pay_note":"asd","pay_credit_account":119,"pay_disc_account":0,"invoice_id":"90"}
        -- {"pay_date":"10-05-2023","pay_amount":"30000","pay_disc":0,"pay_discrp":0,"pay_discamount":0,"pay_note":"","pay_credit_account":"119","pay_disc_account":0,"invoice_id":"1491","memos":"[]","memo_amount":0}
        CALL sp_finance_pay2_save(0, 
            JSON_OBJECT("pay_date", invoicedate, "pay_amount", invoicetotal, "pay_disc", 0, "pay_discrp", 0, "pay_discamount", 0, "pay_note", "", "pay_credit_account", 119, "pay_disc_account", 0, "invoice_id", invoiceid), 1);

	END LOOP getInvoice;
	CLOSE curInvoice;

COMMIT;

END