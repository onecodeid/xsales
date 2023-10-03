BEGIN

DECLARE d_id INTEGER;
DECLARE d_bill INTEGER;
DECLARE d_dp_id INTEGER;
DECLARE d_retur_id INTEGER;
DECLARE d_amount DOUBLE;
DECLARE d_change DOUBLE;
DECLARE d_type_id INTEGER;
DECLARE d_type_code VARCHAR(15);
DECLARE journal_id INTEGER;
DECLARE journal_post CHAR(1);
DECLARE finished INTEGER DEFAULT 0;

DECLARE j_cursor
    CURSOR FOR
        SELECT T_JournalID
        FROM t_journal
        WHERE T_JournalIsActive = 'Y' AND T_JournalDate BETWEEN sdate AND edate;

-- DECLARE EXIT HANDLER FOR SQLEXCEPTION
-- BEGIN
-- GET DIAGNOSTICS CONDITION 1
-- @p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
-- SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;
-- ROLLBACK;
-- END;

-- DECLARE EXIT HANDLER FOR SQLWARNING
-- BEGIN
-- GET DIAGNOSTICS CONDITION 1
-- @p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
-- SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;
-- ROLLBACK;
-- END;

-- START TRANSACTION;
    OPEN j_cursor;

    getPayment: LOOP
        FETCH j_cursor INTO d_id;
        IF finished = 1 THEN
            LEAVE getPayment;
        END IF;

        CALL sp_journal_cashflow(d_id);

    END LOOP getPayment;
    CLOSE j_cursor;
-- COMMIT;

END