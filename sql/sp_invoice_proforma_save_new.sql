BEGIN

-- DECLARE deliveryid INTEGER;
DECLARE pf CHAR(1);
DECLARE pfnumber VARCHAR(25);
DECLARE pduedate DATE;

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

SET pf = (SELECT L_SalesProforma FROM l_sales WHERE L_SalesID = salesid);
SET pduedate = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.duedate'));

IF pf = "N" THEN
    SET pfnumber = (SELECT fn_numbering('INVP'));

    UPDATE l_sales
    SET L_SalesProforma = "Y", 
    L_SalesProformaNumber = pfnumber, 
    L_SalesProformaDueDate = pduedate
    WHERE L_SalesID = salesid;
ELSE
    SET pfnumber = (SELECT L_SalesProformaNumber FROM l_sales WHERE L_SalesID = salesid);

    UPDATE l_sales
    SET L_SalesProformaDueDate = pduedate
    WHERE L_SalesID = salesid;
END IF;


SELECT "OK" status, JSON_OBJECT("sales_id", salesid, "proforma_number", pfnumber) data;
COMMIT;

eND