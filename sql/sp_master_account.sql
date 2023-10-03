BEGIN

DECLARE existingid INTEGER;
DECLARE acode VARCHAR(50);
DECLARE aprefix VARCHAR(50);
DECLARE aname VARCHAR(50);
DECLARE agroup INTEGER;

DECLARE afrom INTEGER;
DECLARE aparent INTEGER;
DECLARE fromcode VARCHAR(25);
DECLARE maxcode VARCHAR(25);
DECLARE nextcode INTEGER;
DECLARE likecode INTEGER;

START TRANSACTION;

-- SET acode = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.code'));
SET aname = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.name'));
-- SET aprefix = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.prefix'));
SET agroup = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.group'));
SET aparent = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.parent'));
SET afrom = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.from'));

IF aparent IS NULL THEN SET aparent = 0; END IF;
IF afrom IS NULL THEN SET afrom = 0; END IF;

IF aparent <> 0 THEN
    SET fromcode = (SELECT M_AccountCode FROM m_account WHERE M_AccountID = aparent);
    SET maxcode = (SELECT MAX(M_AccountClearCode) FROM m_account WHERE M_AccountCode LIKE CONCAT(fromcode,'%') AND M_AccountIsActive = "Y");
    SET nextcode = maxcode + 1;
ELSEIF afrom <> 0 THEN
    SET fromcode = (SELECT M_AccountClearCode FROM m_account WHERE M_AccountID = afrom);
    SET nextcode = fromcode + 1;
    SET likecode = floor(fromcode / 100);

    UPDATE m_account SET M_AccountClearCode = M_AccountClearCode + 1 
    WHERE M_AccountClearCode LIKE CONCAT(likecode, '%') AND M_AccountIsActive = "Y" AND M_AccountClearCode > fromcode;

    UPDATE m_account SET M_AccountCode = CONCAT(LEFT(M_AccountClearCode, 1), "-", SUBSTR(M_AccountClearCode, 2))
    WHERE M_AccountClearCode LIKE CONCAT(likecode, '%') AND M_AccountIsActive = "Y";
ELSE
    SET fromcode = (SELECT MAX(M_AccountClearCode) FROM m_account WHERE M_AccountM_AccountGroupID = agroup AND M_AccountIsActive = "Y");
    SET nextcode = fromcode + 1;
END IF;

SET acode = CONCAT(LEFT(nextcode, 1), "-", SUBSTR(nextcode, 2));

INSERT INTO m_account(
            M_AccountCode,
            M_AccountName,
            M_AccountM_AccountGroupID
        )
        SELECT acode, aname, agroup;

SELECT "OK" as status, JSON_OBJECT("account_id", accountid) as data;
COMMIT;


-- IF accountid = 0 THEN
--     SET existingid = (SELECT M_AccountID FROM m_account WHERE M_AccountCode = CONCAT(aprefix, acode) AND M_AccountIsActive = "Y");
--     IF existingid IS NULL THEN
--         INSERT INTO m_account(
--             M_AccountCode,
--             M_AccountName,
--             M_AccountM_AccountGroupID
--         )
--         SELECT CONCAT(aprefix, acode), aname, agroup;

--         SET accountid = (SELECT LAST_INSERT_ID());
--         SELECT "OK" as status, JSON_OBJECT("account_id", accountid) as data;
--         COMMIT;
--     ELSE
--         SELECT "ERR" status, "Kode perkiraan sudah digunakan oleh akun lain, coba lagi" message;
--         ROLLBACK;
--     END IF;
-- ELSE
--     SET existingid = (SELECT M_AccountID 
--         FROM m_account 
--         WHERE M_AccountCode = CONCAT(aprefix, acode) AND M_AccountIsActive = "Y" AND M_AccountID <> accountid);

--     IF existingid IS NULL THEN
--         UPDATE m_account
--         SET 
--             M_AccountCode = CONCAT(aprefix, acode),
--             M_AccountName = aname,
--             M_AccountM_AccountGroupID = agroup
--         WHERE M_AccountID = accountid;

--         SELECT "OK" as status, JSON_OBJECT("account_id", accountid) as data;
--         COMMIT;
--     ELSE
--         SELECT "ERR" status, "Kode perkiraan sudah digunakan oleh akun lain, coba lagi" message;
--         ROLLBACK;
--     END IF;
-- END IF;

END