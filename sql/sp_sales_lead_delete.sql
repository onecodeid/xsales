BEGIN

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

UPDATE l_lead SET L_LeadIsActive = "N" WHERE L_LeadID = leadid;
UPDATE l_leaddetail SET L_LeadDetailIsActive = "N" WHERE L_LeadDetailL_LeadID = leadid;
SELECT "OK" status, JSON_OBJECT("lead_id", leadid) data;
COMMIT;

END