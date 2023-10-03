BEGIN 

DECLARE ack INTEGER DEFAULT 21;

SELECT P_PurchaseDate purchase_date, 
P_PurchaseNumber purchase_number,
P_PurchaseSubTotal purchase_subtotal,
P_PurchaseTotal purchase_total,
P_PurchasePPN purchase_ppn,
P_PurchaseShipping purchase_shipping,
P_PurchaseDp purchase_dp,
P_PurchaseDisc purchase_disc,
P_PurchaseDiscRp purchase_discrp,
P_PurchaseGrandTotal purchase_grand_total,
P_PurchaseNote purchase_note,
M_VendorName vendor_name, M_VendorAddress vendor_address,
IFNULL(M_VendorPhone, "") vendor_phone,

    IFNULL(sa.S_StaffName, '.........................') admin_name,
    sa.S_StaffName admin_name,
    sb.S_StaffName sales_name,
    sb.S_StaffCode sales_code,
    sc.S_StaffName ack_name,

IFNULL(M_TermName, '') paymentplan_name,

    CONCAT("[", GROUP_CONCAT(
        JSON_OBJECT("item_id", M_ItemID, "item_name", M_ItemName, 
        "item_qty", P_PurchaseDetailQty, "item_unit", IFNULL(M_UnitName, '-'), 
        "item_price", P_PurchaseDetailPrice, "item_disc", P_PurchaseDetailDisc, 
        "item_discrp", P_PurchaseDetailDiscRp, "item_subtotal", P_PurchaseDetailSubTotal, 
        "item_ppn", P_PurchaseDetailPPNAmount, "item_total", P_PurchaseDetailTotal)), "]") items
FROM p_purchase
JOIN p_purchasedetail ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
    AND P_PurchaseDetailIsActive = "Y"

JOIN m_item ON P_PurchaseDetailA_ItemID = M_ItemID
LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID

JOIN m_vendor ON P_PurchaseM_VendorID = M_vendorID
LEFT JOIN s_staff sa ON sa.S_StaffS_UserID = `uid` AND sa.S_StaffIsActive = 'Y'
LEFT JOIN s_staff sb ON sb.S_StaffID = P_PurchaseS_StaffID AND sb.S_StaffIsActive = 'Y'
LEFT JOIN s_staff sc ON sc.S_StaffID = ack
LEFT JOIN m_paymentplan ON P_PurchaseM_PaymentPlanID = M_PaymentPlanID
LEFT JOIN m_term ON P_PurchaseM_TermID = M_TermID

WHERE P_PurchaseID = purchaseid
GROUP BY P_PurchaseID;

END