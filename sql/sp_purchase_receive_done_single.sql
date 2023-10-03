BEGIN

DECLARE undone INTEGER;
DECLARE done INTEGER;
DECLARE flag CHAR(1) DEFAULT "N";

-- FIXED DETAIL RECEIVED UNRECEIVED
UPDATE p_purchasedetail
SET P_PurchaseDetailUnReceived = P_PurchaseDetailQty - P_PurchaseDetailReceived
WHERE P_PurchaseDetailP_PurchaseID = pid;

SET undone = (SELECT COUNT(P_PurchaseDetailID) FROM p_purchasedetail 
            WHERE P_PurchaseDetailP_PurchaseID = pid AND P_PurchaseDetailIsActive = "Y"
            AND P_PurchaseDetailDone = "N");

SET done = (SELECT COUNT(P_PurchaseDetailID) FROM p_purchasedetail 
            WHERE P_PurchaseDetailP_PurchaseID = pid AND P_PurchaseDetailIsActive = "Y"
            AND P_PurchaseDetailDone = "Y");

IF undone > 0 AND (done > 0) THEN
    SET flag = "X";
ELSEIF done > 0 AND (undone IS NULL OR undone = 0) THEN
    SET flag = "Y";
END IF;

UPDATE p_purchase SET P_PurchaseDone = flag WHERE P_PurchaseID = pid;

END