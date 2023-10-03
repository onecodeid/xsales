BEGIN




DECLARE finished INTEGER DEFAULT 0;
DECLARE billid INTEGER DEFAULT 0;
declare billdate date;
declare billtotal double;

	-- declare cursor for employee email
	DEClARE curBill
		CURSOR FOR 
			SELECT F_BillID, F_BillDate, F_BillUnpaid FROM f_bill
            WHERE F_BillIsActive = "Y" AND F_BillDate < "2022-12-01";

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

	OPEN curBill;

	getBill: LOOP
		FETCH curBill INTO billid, billdate, billtotal;
		IF finished = 1 THEN 
			LEAVE getBill;
		END IF;

		-- build email list
		-- CALL sp_master_item_last_purchase_0(itemid);
        -- {"pay_date":"2023-04-08","pay_amount":"90000","pay_disc":"0","pay_discrp":"0","pay_discamount":0,"pay_note":"asd","pay_credit_account":119,"pay_disc_account":0,"bill_id":"90"}
        CALL sp_finance_billpay2_save(0, 
            JSON_OBJECT("pay_date", billdate, "pay_amount", billtotal, "pay_disc", 0, "pay_discrp", 0, "pay_discamount", 0, "pay_note", "", "pay_credit_account", 119, "pay_disc_account", 0, "bill_id", billid), 1);

	END LOOP getBill;
	CLOSE curBill;

COMMIT;

END