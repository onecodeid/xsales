BEGIN

DECLARE confs TEXT;
DECLARE tmp TEXT;
DECLARE ack_id INTEGER;
DECLARE admin_id INTEGER;
DECLARE ack_name VARCHAR(100);
DECLARE admin_name VARCHAR(100);

SET confs = 
-- (SELECT JSON_OBJECT("company", JSON_OBJECT("name", S_ConfCompanyName, "address", S_ConfCompanyAddress, "phones", S_ConfCompanyPhones, "email", S_ConfCompanyEmail), "pic", S_ConfPIC, "ppn", S_ConfPPN)
--             FROM s_conf
--             WHERE S_ConfIsActive = "Y"
--             LIMIT 1);
            (SELECT CONCAT('{"company":{"name":"', S_ConfCompanyName, '","address":', S_ConfCompanyAddress, ',"phones":', S_ConfCompanyPhones,',"email":"', S_ConfCompanyEmail,'"},"pic":', S_ConfPIC,',"ppn":', S_ConfPPN,'}')
            FROM s_conf
            WHERE S_ConfIsActive = "Y"
            LIMIT 1);

IF ckey = "" OR ckey IS NULL THEN
    RETURN confs;
ELSEIF ckey = "delivery" THEN
    SET tmp = JSON_EXTRACT(JSON_EXTRACT(confs, "$.pic"), "$.delivery");
    SET ack_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.ack"));
    SET admin_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.admin"));
    
    SET ack_name = (SELECT S_StaffName FROM s_staff WHERE S_StaffID = ack_id);
    SET admin_name = (SELECT S_StaffName FROM s_staff WHERE S_StaffID = admin_id);

    RETURN JSON_OBJECT("ack",JSON_OBJECT("id",ack_id,"name",ack_name),"admin",JSON_OBJECT("id",admin_id,"name",admin_name));
ELSEIF ckey = "invoice" THEN
    SET tmp = JSON_EXTRACT(JSON_EXTRACT(confs, "$.pic"), "$.invoice");
    SET ack_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.ack"));
    SET admin_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.admin"));
    
    SET ack_name = (SELECT S_StaffName FROM s_staff WHERE S_StaffID = ack_id);
    SET admin_name = (SELECT S_StaffName FROM s_staff WHERE S_StaffID = admin_id);

    RETURN JSON_OBJECT("ack",JSON_OBJECT("id",ack_id,"name",ack_name),"admin",JSON_OBJECT("id",admin_id,"name",admin_name));
ELSEIF ckey = "company" THEN
    SET tmp = JSON_EXTRACT(confs, "$.company");
    RETURN tmp;
ELSEIF ckey = "ppn" THEN
    SET @x = (SELECT S_ConfPPN
                FROM s_conf
                WHERE S_ConfIsActive = "Y"
                LIMIT 1);
    RETURN @x;
END IF;

END