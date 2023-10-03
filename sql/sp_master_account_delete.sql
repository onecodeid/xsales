BEGIN

DECLARE used CHAR(1);
DECLARE parent CHAR(1);

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

SELECT M_AccountUsed, M_AccountParent
INTO used, parent
FROM m_account WHERE M_AccountID = accountid;

IF used = "Y" THEN
    SELECT "ERR" status, "Akun sudah digunakan di dalam transaksi / jurnal. Tidak bisa dihapus :(" message;
    ROLLBACK;

ELSEIF parent = "Y" THEN
    SELECT "ERR" status, "Akun tersebut merupakan parent dari akun lain, silahkan hapus anaknya terlebih dahulu !" message;
    ROLLBACK;

ELSE
    UPDATE m_account SET M_AccountIsActive = "N" WHERE M_AccountID = accountid;
    SELECT "OK" status, JSON_OBJECT("account_id", accountid) data;

    COMMIT;

END IF;

END