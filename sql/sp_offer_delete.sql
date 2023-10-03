BEGIN

DECLARE used CHAR(1);

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

SET used = (SELECT L_OfferUsed FROM l_offer WHERE L_OfferID = offerid);
IF used = "Y" THEN
    SELECT "ERR" status, "Penawaran tersebut telah digunakan, tidak bisa dihapus :(" message;
    ROLLBACK;
ELSE
    UPDATE l_offer SET L_OfferIsActive = "N" WHERE L_OfferID = offerid;
    SELECT "OK" status, JSON_OBJECT("offer_id", offerid) data;
    COMMIT;
END IF;

END