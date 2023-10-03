BEGIN 

DECLARE ppn CHAR(1) DEFAULT "N";
DECLARE ppnc INTEGER;
DECLARE banks VARCHAR(500);
DECLARE confs VARCHAR(2000);

SET confs = (SELECT fn_conf("invoice"));


SET ppnc = (SELECT COUNT(L_SalesdetailID) FROM l_salesdetail WHERE L_SalesDetailL_SalesID = salesid AND L_SalesDetailIsActive = "Y" ANd L_SalesDetailPPN = "Y");
IF ppnc IS NULL THEN SET ppnc = 0; END IF;
IF ppnc > 0 THEN SET ppn = "Y"; END IF;

SET banks = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("bank_id", M_BankID, "bank_name", M_BankName, 
                "account_name", M_BankAccountName, "account_number", M_BankAccountNumber)), "]")
            FROM m_bankaccount
            JOIN m_bank ON M_BankAccountM_BankID = M_BankID
            WHERE M_BankAccountIsActive = "Y"
            AND M_BankAccountPPN = ppn);

SELECT L_SalesDate invoice_date, L_SalesNumber invoice_number,
L_SalesProformaDueDate invoice_due_date,
L_SalesSubTotal invoice_subtotal,
L_SalesTotal invoice_total,
L_SalesDiscount invoice_disc,
L_SalesDiscountRp invoice_discrp,
L_SalesPPN invoice_ppn,
L_SalesGrandTotal invoice_grand_total,
L_SalesShipping invoice_shipping,
L_SalesDP invoice_dp,
L_SalesNote invoice_note,

M_CustomerName customer_name, M_CustomerAddress customer_address, IFNULL(M_CustomerPICName, '') customer_pic,
IFNULL(M_CustomerPhones, "[]") customer_phones,
    IFNULL(M_KelurahanName, '') village_name,
    IFNULL(M_DistrictName, '') district_name,
    IFNULL(M_CityName, '') city_name,
    IFNULL(M_ProvinceName, '') province_name,
    JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(confs,"$.admin"), "$.name")) admin_name,
    JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(confs,"$.ack"), "$.name")) ack_name,

    sb.S_StaffName sales_name,
    sb.S_StaffCode sales_code,
    IFNULL(sb.S_StaffPhones, "[]") sales_phones,
    IFNULL(sb.S_StaffEmail, "") sales_email,
    CONCAT("[", GROUP_CONCAT(
        JSON_OBJECT("item_id", M_ItemID, "item_name", 
        IFNULL(L_SalesDetailA_ItemName,
            IFNULL(M_ItemAliasName, (IF(M_ItemAlias IS NULL OR M_ItemAlias = "" OR M_ItemAlias = "null", M_ItemName, M_ItemAlias)))
        ), 
        "item_qty", L_SalesDetailQty, "item_unit", IFNULL(M_UnitName, '-'), 
        "item_price", L_SalesDetailPrice, "item_disc", L_SalesDetailDisc, 
        "item_discrp", L_SalesDetailDiscRp, "item_subtotal", L_SalesDetailSubTotal, 
        "item_ppn", L_SalesDetailPPN, "item_ppn_amount", L_SalesDetailPPNAmount, "item_total", L_SalesDetailPPNAmount)), "]") items,
    banks,
    fn_terbilang(L_SalesGrandTotal) terbilang,

    "" deliveries,
    IFNULL(GROUP_CONCAT(DISTINCT IF(L_SalesRef = "", NULL, L_SalesRef) SEPARATOR ", "), "") sales_refs,
    IFNULL(M_TermName, '') term_name
FROM l_sales
JOIN l_salesdetail ON L_SalesDetailL_SalesID = L_SalesID
    AND L_SalesDetailIsActive = "Y"

JOIN m_item ON L_SalesDetailA_ItemID = M_ItemID
LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
JOIN m_customer ON L_SalesM_CustomerID = M_customerID
LEFT JOIN m_itemalias ON M_ItemAliasM_ItemID = M_ItemID ANd M_ItemAliasM_customerID = M_CustomerID AND M_ItemAliasIsActive = "Y"
LEFT JOIN m_kelurahan ON M_CustomerM_KelurahanID = M_KelurahanID
LEFT JOIN m_district ON M_KelurahanM_districtID = M_DistrictID
LEFT JOIN m_city ON M_DistrictM_CityID = M_CityID
LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
LEFT JOIN s_staff sa ON sa.S_StaffS_UserID = `uid` AND S_StaffIsActive = 'Y'
LEFT JOIN m_term ON L_SalesM_TermID = M_TermID
JOIN s_staff sb ON L_SalesS_StaffID = sb.S_StaffID
WHERE L_SalesID = salesid
GROUP BY L_SalesID;

END