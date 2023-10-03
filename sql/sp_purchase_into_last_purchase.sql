BEGIN

DECLARE finished INTEGER DEFAULT 0;
	DECLARE itemid INTEGER DEFAULT 0;

	-- declare cursor for employee email
	DEClARE curItem 
		CURSOR FOR 
			SELECT F_BillDetailA_ItemID FROM f_billdetail
            WHERE F_BillDetailIsActive = "Y"
            AND F_BillDetailF_BillID = billid;

	-- declare NOT FOUND handler
	DECLARE CONTINUE HANDLER 
        FOR NOT FOUND SET finished = 1;

	OPEN curItem;

	getItem: LOOP
		FETCH curItem INTO itemid;
		IF finished = 1 THEN 
			LEAVE getItem;
		END IF;

		-- build email list
		CALL sp_master_item_last_purchase(itemid);

	END LOOP getItem;
	CLOSE curItem;

END