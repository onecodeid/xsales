BEGIN

DECLARE pdate DATE;
DECLARE pnumber VARCHAR(25);
DECLARE pstaff INTEGER DEFAULT 0;
DECLARE pnote VARCHAR(255);
DECLARE error INTEGER DEFAULT 0;

DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE tmp VARCHAR(255);
DECLARE d_type CHAR(1);
DECLARE d_b2b INTEGER;
DECLARE d_b2c INTEGER;
DECLARE d_did INTEGER;
DECLARE d_id INTEGER;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME, @clm = COLUMN_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name, @clm as column_name;

ROLLBACK;
END;

START TRANSACTION;

SET pdate = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_date"));
SET pstaff = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_staff"));
SET pnote = JSON_UNQUOTE(JSON_EXTRACT(hdata, "$.p_note"));

IF leadid = 0 THEN
    SET leadid = (SELECT L_LeadID FROM l_lead WHERE L_LeadDate = pdate AND L_LeadS_StaffID = pstaff AND L_LeadIsActive = "Y");
    IF leadid IS NOT NULL THEN
        SET error = 1;
        SELECT "ERR" status, "Lead untuk tanggal tersebut sudah ada, silahkan cek kembali tanggal :(" message;
        ROLLBACK;
    END IF;
    SET leadid = 0;
END IF;

IF error = 0 THEN
    IF leadid = 0 THEN
        SET pnumber = (SELECT fn_numbering('LEAD'));
        INSERT INTO l_lead(L_LeadDate, L_LeadNumber, L_LeadS_StaffID, L_LeadNote)
        SELECT pdate, pnumber, pstaff, pnote;

        SET leadid = (SELECT LAST_INSERT_ID());
    ELSE
        UPDATE l_lead
        SET L_LeadNote = pnote
        WHERE L_LeadID = leadid;

        SET pnumber = (SELECT L_LeadNumber FROM l_lead WHERe L_LeadID = leadid);
    END IF;

    SET l = JSON_LENGTH(jdata);
    WHILE n < l DO
        SET tmp = JSON_EXTRACT(jdata, CONCAT("$[",n,"]"));
        SET d_type = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.type"));
        SET d_did = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.did"));
        SET d_b2b = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.b2b"));
        SET d_b2c = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.b2c"));
        SET d_id = (SELECT L_LeadDetailID FROM l_leaddetail
                    WHERE L_LeadDetailL_LeadID = leadid
                    AND L_LeadDetailIsActive = "Y"
                    AND L_LeadDetailType = d_type
                    aND ((L_LeadDetailM_ProspectID = d_did AND d_type = "P") OR (L_LEadDetailM_CategoryID = d_did AND d_type = "C")) );

        IF d_id IS NULL THEN
            INSERt INTO l_leaddetail(L_LeadDetailL_LeadID,
                L_LeadDetailType,
                L_LeadDetailM_ProspectID,
                L_LeadDetailM_CategoryID,
                L_LEaddetailB2B,
                L_LeadDetailB2C)
            SELECT leadid, d_type, IF(d_type="P", d_did, 0), IF(d_type="C", d_did, 0), d_b2b, d_b2c;
        ELSE
            UPDATE l_leaddetail
            SET L_LEaddetailB2B = d_b2b, L_LeadDetailB2C = d_b2c
            WHERE L_LEadDetailID = d_id;
        END IF;

        SET n = n + 1;
    END WHILE;

    SELECT "OK" status, JSON_OBJECT("lead_id", leadid, "lead_number", pnumber) data;
    COMMIT;
END IF;


END