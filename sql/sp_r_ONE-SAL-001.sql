BEGIN 

DECLARE confs VARCHAR(2000);
DECLARE so_id INTEGER;
DECLARE delivery_address_id INTEGER;

SET confs = (SELECT fn_conf("delivery"));
SET so_id = (SELECT MAX(L_SalesID)
                FROM l_delivery
                JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
                    AND L_DeliveryDetailIsActive = "Y"
                JOIN l_salesdetail ON l_deliveryDetailL_SalesDetailID = L_SalesDetailID
                JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
                WHERE L_DeliveryID = deliveryid
            );
SET delivery_address_id = (SELECT M_DeliveryAddressID
                FROM l_sales
                JOIN m_deliveryaddress ON L_SalesM_DeliveryAddressID = M_DeliveryAddressID
                WHERE L_SalesID = so_id);        

SELECT L_DeliveryDate delivery_date, L_DeliveryNumber delivery_number,
MAX(L_SalesDate) sales_date, 
M_CustomerName customer_name, 

M_DeliveryAddressDesc customer_address, 
IFNULL(M_DeliveryAddressPIC, IFNULL(M_CustomerPICName, '')) customer_pic,
IF(M_DeliveryAddressPhones <> "[]", M_DeliveryAddressPhones, IFNULL(M_CustomerPhones, "[]")) customer_phones,
    IFNULL(M_KelurahanName, '') village_name,
    IFNULL(M_DistrictName, '') district_name,
    IFNULL(M_CityName, '') city_name,
    IFNULL(M_ProvinceName, '') province_name,
    sa.S_StaffName transporter_name,
    sb.S_StaffName sales_name,
    sb.S_StaffCode sales_code,
    IFNULL(L_DeliverySendNote, '') delivery_send_note,
    M_DeliveryTypeCode delivery_type_code,
    M_DeliveryTypeName delivery_type_name,
    JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(confs,"$.admin"), "$.name")) admin_name,
    JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(confs,"$.ack"), "$.name")) ack_name,
    IFNULL(sb.S_StaffPhones, "[]") sales_phones,
    IFNULL(sb.S_StaffEmail, "") sales_email,
    CONCAT("[", GROUP_CONCAT(JSON_OBJECT("item_id", M_ItemID, "item_name", 
    IFNULL(l_deliverydetaila_itemname,
    IFNULL(M_ItemAliasName, (IF(M_ItemAlias IS NULL OR M_ItemAlias = "" OR M_ItemAlias = "null", M_ItemName, M_ItemAlias))) 
    ), 
        "item_qty", IF(pb.M_PackConversion IS NOT NULL, L_DeliveryDetailQty / pb.M_PackConversion, 
        IF(M_ItemViewInPack="Y",L_DeliveryDetailQty/pa.M_PackConversion, L_DeliveryDetailQty)), 
        
        "item_unit", IFNULL(IF(M_ItemViewInPack="Y", IFNULL(IFNULL(pb.M_PackName, pa.M_PackName), M_UnitName), M_UnitName), ''))), "]") items,
    CONCAT("[", GROUP_CONCAT(DISTINCT CONCAT('"', L_SalesNumber, '"') SEPARATOR ","), "]") sales_numbers,
    IFNULL( CONCAT("[", GROUP_CONCAT(DISTINCT CONCAT('"', L_SalesRef, '"') SEPARATOR ","), "]"), "[]") sales_refs
FROM l_delivery
JOIN l_deliverydetail ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
    AND L_DeliveryDetailIsActive = "Y"
JOIN l_salesdetail ON l_deliveryDetailL_SalesDetailID = L_SalesDetailID
JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
JOIN m_item ON L_DeliveryDetailA_ItemID = M_ItemID
LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID
LEFT JOIN m_itempack ON M_ItemPackM_CustomerID = L_DeliveryM_CustomerID
    AND M_ItemPackIsActive = "Y"
    AND M_ItemPackM_ItemID = M_ItemID
LEFT JOIN m_pack pa ON M_ItemM_PackID = pa.M_PackID
LEFT JOIN m_pack pb ON M_ItemPackM_PackID = pb.M_PackID
JOIN m_customer ON L_DeliveryM_CustomerID = M_customerID
JOIN m_deliverytype ON L_DeliveryM_DeliveryTypeID = M_DeliveryTypeID
LEFT JOIN m_itemalias ON M_ItemAliasM_ItemID = M_ItemID ANd M_ItemAliasM_customerID = M_CustomerID AND M_ItemAliasIsActive = "Y"

LEFT JOIN m_deliveryaddress ON M_DeliveryAddressID = delivery_address_id
LEFT JOIN m_kelurahan ON M_DeliveryAddressM_KelurahanID = M_KelurahanID
LEFT JOIN m_district ON M_DeliveryAddressM_DistrictID = M_DistrictID
LEFT JOIN m_city ON M_DeliveryAddressM_CityID = M_CityID
LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID

LEFT JOIN s_staff sa ON L_DeliveryS_StaffID = sa.S_StaffID

JOIN s_staff sb ON L_SalesS_StaffID = sb.S_StaffID
WHERE L_DeliveryID = deliveryid
GROUP BY L_DeliveryID;

END