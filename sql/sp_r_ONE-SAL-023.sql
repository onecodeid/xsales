DROP PROCEDURE `sp_r_ONE-SAL-023`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-SAL-023` (IN `salesid` int, IN `uid` int)
BEGIN 

DECLARE ppn CHAR(1) DEFAULT "N";
DECLARE ppnc INTEGER;
DECLARE banks VARCHAR(500);
DECLARE confs VARCHAR(2000);

SET confs = (SELECT fn_conf("invoice"));

SET ppnc = 0;
SET banks = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("bank_id", M_BankID, "bank_name", M_BankName, 
                "account_name", M_BankAccountName, "account_number", M_BankAccountNumber)), "]")
            FROM m_bankaccount
            JOIN m_bank ON M_BankAccountM_BankID = M_BankID
            WHERE M_BankAccountIsActive = "Y"
            AND M_BankAccountPPN = ppn);

SELECT L_SalesDate sales_date, L_SalesNumber sales_number,
DATE_ADD(L_SalesDate, INTERVAL M_TermDuration DAY) sales_due_date,
-- L_SalesDueDate sales_due_date,
L_SalesSubTotal sales_subtotal,
L_SalesTotal sales_total,
L_SalesDiscount sales_disc,
L_SalesDiscountRp sales_discrp,
L_SalesPPN sales_ppn,
L_SalesGrandTotal sales_grand_total,
L_SalesShipping sales_shipping,
L_SalesDP sales_dp,
L_SalesNote sales_note,

IF(M_CustomerCode = 'C.UMUM', L_SalesM_CustomerName, M_CustomerName) customer_name, IFNULL(M_CustomerAddress, '-') customer_address, IFNULL(M_CustomerPICName, '') customer_pic,
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
        JSON_OBJECT("item_id", M_ItemID, "item_code", M_ItemCode, "item_name", 
            IFNULL(L_SalesDetailA_ItemName, 
                IFNULL(M_ItemAliasName, (IF(M_ItemAlias IS NULL OR M_ItemAlias = "" OR M_ItemAlias = "null", M_ItemName, M_ItemAlias)))
            ), 
        "item_qty", L_SalesDetailQty, "item_unit", IFNULL(M_UnitName, '-'), 
        "item_price", L_SalesDetailPrice, "item_disc", L_SalesDetailDisc, 
        "item_discrp", L_SalesDetailDiscRp, "item_subtotal", L_SalesDetailSubTotal, 
        "item_ppn", L_SalesDetailPPN, "item_subtotal", L_SalesDetailSubTotal, "item_total", L_SalesDetailTotal)), "]") items,
    banks,
    fn_terbilang(L_SalesGrandTotal) terbilang,

    "" deliveries,
    "" sales_refs,
    IFNULL(M_TermName, '') term_name, IFNULL(M_TermDuration, '') term_duration
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
LEFT JOIN s_staff sb ON L_SalesS_StaffID = sb.S_StaffID
WHERE L_SalesID = salesid
GROUP BY L_SalesID;

END;;
DELIMITER ;