BEGIN 

DECLARE ppn CHAR(1) DEFAULT "N";
DECLARE ppnc INTEGER;
DECLARE banks VARCHAR(500);
DECLARE confs VARCHAR(2000);

SET confs = (SELECT fn_conf("invoice"));


SET ppnc = (SELECT COUNT(L_InvoicedetailID) FROM l_invoicedetail WHERE L_InvoiceDetailL_InvoiceID = invoiceid AND L_InvoiceDetailIsActive = "Y" ANd L_InvoiceDetailPPN = "Y");
IF ppnc IS NULL THEN SET ppnc = 0; END IF;
IF ppnc > 0 THEN SET ppn = "Y"; END IF;

SET banks = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("bank_id", M_BankID, "bank_name", M_BankName, 
                "account_name", M_BankAccountName, "account_number", M_BankAccountNumber)), "]")
            FROM m_bankaccount
            JOIN m_bank ON M_BankAccountM_BankID = M_BankID
            WHERE M_BankAccountIsActive = "Y"
            AND M_BankAccountPPN = ppn);

SELECT L_InvoiceDate invoice_date, L_InvoiceNumber invoice_number,
L_InvoiceDueDate invoice_due_date,
L_InvoiceSubTotal invoice_subtotal,
L_InvoiceTotal invoice_total,
L_InvoiceDiscount invoice_disc,
L_InvoiceDiscountRp invoice_discrp,
L_InvoicePPN invoice_ppn,
L_InvoiceGrandTotal invoice_grand_total,
L_InvoiceShipping invoice_shipping,
L_InvoiceDP invoice_dp,
L_InvoiceNote invoice_note,

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
        "item_qty", L_InvoiceDetailQty, "item_unit", IFNULL(M_UnitName, '-'), 
        "item_price", L_InvoiceDetailPrice, "item_disc", L_InvoiceDetailDisc, 
        "item_discrp", L_InvoiceDetailDiscRp, "item_subtotal", L_InvoiceDetailSubTotal, 
        "item_ppn", L_InvoiceDetailPPN, "item_subtotal", L_InvoiceDetailSubTotal, "item_total", L_InvoiceDetailTotal)), "]") items,
    banks,
    fn_terbilang(L_InvoiceGrandTotal) terbilang,

    GROUP_CONCAT(DISTINCT L_DeliveryNumber SEPARATOR ", ") deliveries,
    IFNULL(GROUP_CONCAT(DISTINCT IF(L_SalesRef = "", NULL, L_SalesRef) SEPARATOR ", "), "") sales_refs,
    IFNULL(M_TermName, '') term_name
FROM l_invoice
JOIN l_invoicedetail ON L_InvoiceDetailL_InvoiceID = L_InvoiceID
    AND L_InvoiceDetailIsActive = "Y"

JOIN l_deliverydetail ON L_InvoiceDetailL_DeliveryID = L_DeliveryDetailL_DeliveryID
    AND L_DeliveryDetailIsActive = "Y" AND L_DeliveryDetailA_ItemID = L_InvoiceDetailA_ItemID
JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID

JOIN l_salesdetail ON L_DeliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID

JOIN m_item ON L_InvoiceDetailA_ItemID = M_ItemID
LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
JOIN m_customer ON L_InvoiceM_CustomerID = M_customerID
LEFT JOIN m_itemalias ON M_ItemAliasM_ItemID = M_ItemID ANd M_ItemAliasM_customerID = M_CustomerID AND M_ItemAliasIsActive = "Y"
LEFT JOIN m_kelurahan ON M_CustomerM_KelurahanID = M_KelurahanID
LEFT JOIN m_district ON M_KelurahanM_districtID = M_DistrictID
LEFT JOIN m_city ON M_DistrictM_CityID = M_CityID
LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
LEFT JOIN s_staff sa ON sa.S_StaffS_UserID = `uid` AND S_StaffIsActive = 'Y'
LEFT JOIN m_term ON L_InvoiceM_TermID = M_TermID
JOIN s_staff sb ON L_SalesS_StaffID = sb.S_StaffID
WHERE L_InvoiceID = invoiceid
GROUP BY L_InvoiceID;

END