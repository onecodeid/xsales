DROP PROCEDURE `sp_master_account_save_new`;
DELIMITER ;;
CREATE PROCEDURE `sp_master_account_save_new` (IN `hdata` text, IN `accountid` int, IN `uid` int)
BEGIN

DECLARE maxclearcode INTEGER;
DECLARE parentcode VARCHAR(25);
DECLARE clearcode INTEGER;
DECLARE groupcode VARCHAR(25);
DECLARE mycode VARCHAR(25);

DECLARE groupid INTEGER;
DECLARE parentid INTEGER;
DECLARE account_name VARCHAR(100);
DECLARE account_type CHAR(1) DEFAULT "A";
DECLARE account_pos CHAR(1) DEFAULT "D";

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

SET account_name = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_name'));
SET account_pos = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_pos'));
IF accountid = 0 THEN

    SET groupid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_group'));
    SET parentid = JSON_UNQUOTE(JSON_EXTRACT(hdata, '$.account_parent'));

    SET groupcode = (SELECT M_AccountGroupCode FROM m_accountgroup WHERE M_AccountGroupID = groupid);
    SET maxclearcode = (SELECT M_AccountClearCode FROM m_account WHERE M_AccountM_AccountGroupID = groupid 
            AND M_AccountIsActive = "Y" AND LENGTH(M_AccountCode) = 7
            ORDER BY M_AccountCode DESC LIMIT 1);

    IF parentid <> 0 THEN
        SET parentcode = (SELECT M_AccountCode FROM m_account WHERE M_AccountID = parentid);
        SET maxclearcode = (SELECT max(M_AccountClearCode) FROM m_account WHERE M_AccountM_AccountGroupID = groupid 
            AND M_AccountIsActive = "Y" AND M_AccountCode LIKE CONCAT(parentcode, '%') AND M_AccountCode <> parentcode );

        IF maxclearcode IS NULL THEN 
            SET maxclearcode = CONCAT(REPLACE(parentcode, "-", ""), "01");
        END IF;
    END IF;

    SET clearcode = maxclearcode + 1;
    SET mycode = CONCAT(LEFT(clearcode, 1), "-", SUBSTR(clearcode, 2));

    -- INSERT INTO
    INSERT INTO m_account(M_AccountM_AccountGroupID, M_AccountCode, M_AccountName, M_AccountType, M_AccountClearCode, M_AccountPos)
    SELECT groupid, mycode, account_name, account_type, clearcode, account_pos;

    SET accountid = (SELECT LAST_INSERT_ID());

ELSE

    UPDATE m_account SET M_AccountName = account_name, M_ACcountPos = account_pos WHERE M_AccountID = accountid;

END IF;

SELECT "OK" status, JSON_OBJECT("account_id", accountid, "account_name", account_name) data;

COMMIT;

END;;
DELIMITER ;