DROP PROCEDURE `sp_log_activity`;
DELIMITER ;;
CREATE PROCEDURE `sp_log_activity` (IN `what` varchar(25), IN `module` varchar(25), IN `refid` int, IN `uid` int)
BEGIN

DECLARE refnumber VARCHAR(25) DEFAULT "";
DECLARE refdate DATE;

IF module = "SALES.OFFER" THEN
    SET refnumber = (SELECT L_OfferNumber FROM l_offer WHERE L_OfferID = refid);
    SET refdate = (SELECT DATE(L_OfferDate) FROM l_offer WHERE L_OfferID = refid);
ELSEIF module = "SALES.ORDER" THEN
    SET refnumber = (SELECT L_SalesNumber FROM l_sales WHERE L_SalesID = refid);
    SET refdate = (SELECT L_SalesDate FROM l_sales WHERE L_SalesID = refid);
ELSEIF module = "SALES.DELIVERY" THEN
    SET refnumber = (SELECT L_DeliveryNumber FROM l_delivery WHERE L_DeliveryID = refid);
    SET refdate = (SELECT L_DeliveryDate FROM l_delivery WHERE L_DeliveryID = refid);
ELSEIF module = "SALES.INVOICE" THEN
    SET refnumber = (SELECT L_InvoiceNumber FROM l_invoice WHERE L_InvoiceID = refid);
    SET refdate = (SELECT L_InvoiceDate FROM l_invoice WHERE L_InvoiceID = refid);
END IF;

IF refnumber IS NULL THEN SET refnumber = ""; END IF;

INSERT INTO one_account_aw_log.log_activity(
    Log_ActivityWhat,
    Log_ActivityModule,
    Log_ActivityRefID,
    Log_ActivityRefNumber,
    Log_ActivityRefDate,
    Log_ActivityUID,
    Log_ActivityStaffID,
    Log_ActivityStaffName)
SELECT what, module, refid, refnumber, refdate, uid, IFNULL(S_StaffID, 0), IFNULL(S_StaffName, '')
FROM s_user 
LEFT JOIN s_staff ON S_StaffS_UserID = S_UserID AND S_StaffIsActive = "Y"
WHERE S_UserID = uid
GROUP BY S_UserID;

END;;
DELIMITER ;