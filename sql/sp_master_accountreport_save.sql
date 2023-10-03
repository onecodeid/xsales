DROP PROCEDURE `sp_master_accountreport_save`;
DELIMITER ;;
CREATE PROCEDURE `sp_master_accountreport_save` (IN `groups` varchar(255), IN `jdata` text)
BEGIN

DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER DEFAULT 0;
DECLARE tmp VARCHAR(2000);
DECLARE account_type VARCHAR(100);
DECLARE account_title VARCHAR(255);
DECLARE account_id INTEGER;
DECLARE is_group CHAR(1);
DECLARE detail_id INTEGER;
DECLARE account_sort INTEGER;

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

UPDATE m_accountreport
SET M_AccountReportIsActive = "O"
WHERE M_AccountReportIsActive = "Y" AND M_AccountReportType LIKE CONCAT(groups, '%');

SET l = JSON_LENGTH(jdata);
WHILE n < l DO
    SET tmp = JSON_EXTRACT(jdata, CONCAT('$[', n, ']'));
    SET account_type = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account_type'));
    SET account_title = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account_title'));
    SET account_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.account_id'));
    SET is_group = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.is_group'));
    SET account_sort = JSON_UNQUOTE(JSON_EXTRACT(tmp, '$.sort'));

    SET detail_id = (SELECT M_AccountReportID FROM m_accountreport WHERE M_AccountReportType = account_type 
                    AND M_AccountReportIsActive = "O"
                    AND ((M_AccountReportM_AccountID = account_id AND is_group = "N") 
                        OR (M_AccountReportM_AccountGroupID = account_id AND is_group = "Y") ));
    IF detail_id IS NULL THEN
        INSERT INTO m_accountreport(M_AccountReportType, M_AccountReportTitle, M_AccountReportM_AccountID, M_AccountReportM_AccountGroupID, M_AccountReportSort)
        SELECT account_type, account_title, IF(is_group = "N", account_id, 0), IF(is_group = "Y", account_id, 0), account_sort;
    ELSE
        UPDATE m_accountreport
            SET M_AccountReportTitle = account_title,  M_AccountReportM_AccountID = IF(is_group = "N", account_id, 0),
                M_AccountReportM_AccountGroupID = IF(is_group = "Y", account_id, 0), M_AccountReportIsActive = "Y", M_AccountReportSort = account_sort
        WHERE M_AccountReportID = detail_id;
    END IF;

    SET n = n + 1;
END WHILE;

UPDATE m_accountreport
SET M_AccountReportIsActive = "N"
WHERE M_AccountReportIsActive = "O" AND M_AccountReportType LIKE CONCAT(groups, '%');

SELECT "OK" as status, JSON_OBJECT("group", groups) data;

COMMIT;
END;;
DELIMITER ;