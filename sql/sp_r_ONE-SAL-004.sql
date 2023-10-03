BEGIN

SELECT L_OfferDate offer_date, L_OfferNumber offer_number, M_CustomerID customer_id, M_CustomerName customer_name,
    M_CustomerIsCompany customer_is_company, M_CustomerPICName customer_pic_name, IFNULL(M_CustomerEmail, '-') customer_email,
    IFNULL(M_CustomerPhones, "[]") customer_phones, IFNULL(S_StaffPhones, "[]") staff_phones, IFNULL(S_StaffEmail, "-") staff_email,
    L_OfferTotal offer_total, L_OfferIncludePPN offer_ppn, L_OfferNote offer_note, S_StaffID staff_id, S_StaffName staff_name,
    IFNULL(L_OfferFranco, '') offer_franco, IFNULL(L_OfferDelivery, '') offer_delivery,
    IFNULL(L_OfferValidity, '') offer_validity, IFNULL(L_OfferStockNote, '') offer_stocknote,
    IFNULL(M_PaymentPlanID, 0) paymentplan_id, IFNULL(M_PaymentPlanName, '') paymentplan_name,
    IFNULL(M_TermID, 0) term_id, IFNULL(M_TermName, '') term_name,
    CONCAT("[", GROUP_CONCAT(
    JSON_OBJECT("item_id", M_ItemID, "item_code", M_ItemCode, 
        "item_name", IF(L_OfferDetailOther<>'Y', IFNULL(M_ItemAliasName, (IF(M_ItemAlias IS NULL OR M_ItemAlias = "" OR M_ItemAlias = "null", M_ItemName, M_ItemAlias))), L_OfferDetailOtherName), 
        "item_qty", L_OfferDetailQty,
        "item_price", L_OfferDetailPrice, "item_disc", L_OfferDetailDisc, "item_discrp", L_OfferDetailDiscRp,
        "item_subtotal", L_OfferDetailSubTotal, "item_ppn", L_OfferDetailPPN, "item_ppn_amount", L_OfferDetailPPNAmount,
        "item_total", L_OfferDetailTotal, 
        "unit_name", IFNULL(ua.M_UnitName, ub.M_UnitName), "pack_name", IFNULL(pa.M_PackName, pb.M_PackName))), "]") items,
    fn_conf('ppn') ppn
FROM l_offer
JOIN l_offerdetail ON L_OfferID = L_OfferDetailL_OfferID AND L_OfferDetailIsActive = "Y"
JOIN m_customer ON L_OfferM_CustomerID = M_CustomerID
LEFT JOIN m_item ON L_OfferDetailA_ItemID=M_ItemID
LEFT JOIN m_itemalias ON M_ItemAliasM_ItemID = M_ItemID ANd M_ItemAliasM_customerID = M_CustomerID AND M_ItemAliasIsActive = "Y"
LEFT JOIN m_pack pa ON M_ItemM_PackID = pa.M_PackID
LEFT JOIN m_unit ua ON M_ItemM_UnitID = ua.M_UnitID
LEFT JOIN m_pack pb ON L_OfferDetailM_PackID = pb.M_PackID
LEFT JOIN m_unit ub ON L_OfferDetailM_UnitID = ub.M_UnitID

LEFT JOIN m_term ON L_OfferM_TermID = M_TermID

JOIN s_staff ON L_OfferS_StaffID = S_StaffID
LEFT JOIN m_paymentplan ON L_OfferM_PaymentPlanID = M_PaymentPlanID
WHERE L_OfferID = offerid;

END