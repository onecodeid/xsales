DROP PROCEDURE `sp_master_item_last_purchase_0`;
DELIMITER ;;
CREATE PROCEDURE `sp_master_item_last_purchase_0` (IN `itemid` int)
BEGIN

DECLARE details TEXT;
DECLARE logid INTEGER;

SET details = (

SELECT CONCAT("[", GROUP_CONCAT(x SEPARATOR ","), "]")
FROM
(SELECT 
    
    JSON_OBJECT("purchase_id", P_PurchaseID, "purchase_date", P_PurchaseDate, "purchase_number", P_PurchaseNumber,
    "item_qty", P_PurchaseDetailQty, "item_price", P_PurchaseDetailPrice, "item_disc", P_PurchaseDetailDisc, "item_discrp", P_PurchaseDetailDiscRp,
    "item_subtotal", P_PurchaseDetailSubTotal, "item_ppn", P_PurchaseDetailPPNAmount,
    "item_total", P_PurchaseDetailTotal, "item_av", (P_PurchaseDetailTotal / P_PurchaseDetailQty), "vendor_name", M_VendorName) x

FROM p_purchasedetail
JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
JOIN m_vendor ON P_PurchaseM_VendorID = M_VendorID
JOIN m_item ON P_PurchaseDetailA_ItemID = M_ItemID
WHERE P_PurchaseDetailA_ItemID = itemid
AND P_PurchaseDetailIsActive = "Y"

ORDER BY P_PurchaseDate DESC
LIMIT 20) y
);

IF details IS NULL THEN SET details = "[]"; ENd IF;

SET logid = (SELECT Log_ItemPurchaseID FROM one_account_aw_log.log_itempurchase 
WHERE Log_ItemPurchaseIsActive = "Y" ANd Log_ItemPurchaseM_ItemID = itemid LIMIT 1);

IF logid IS NULL THEN
INSERT INTO one_account_aw_log.log_itempurchase(Log_ItemPurchaseM_ItemID, Log_ItemPurchaseDetails)
SELECT itemid, details;

ELSE
UPDATE one_account_aw_log.log_itempurchase
SET Log_ItemPurchaseDetails = details
WHERE Log_ItemPurchaseID = logid;

END IF;

END;;
DELIMITER ;