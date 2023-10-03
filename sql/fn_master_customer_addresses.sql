BEGIN

DECLARE rst TEXT;

SET rst = (SELECT CONCAT("[", 
            GROUP_CONCAT(
                JSON_OBJECT("address_id", M_DeliveryAddressID,
                            "address_name", M_DeliveryAddressName,
                            "address_desc", M_DeliveryAddressDesc,
                            "address_village", IF(M_KelurahanID is null, null, JSON_OBJECT("id", M_KelurahanID, "name", M_KelurahanName)),
                            "address_district", IF(M_DistrictID is null, null, JSON_OBJECT("id", M_DistrictID, "name", M_DistrictName)),
                            "address_city", JSON_OBJECT("id", M_CityID, "name", M_CityName),
                            "address_province", JSON_OBJECT("id", M_ProvinceID, "name", M_ProvinceName),
                            "address_postcode", M_DeliveryAddressPostCode,
                            "address_pic_name", M_DeliveryAddressPIC,
                            "address_phones", M_DeliveryAddressPhones )),"]")
FROM m_deliveryaddress
JOIN m_city ON M_DeliveryAddressM_CityID = M_CityID
LEFT JOIN m_district ON M_DeliveryAddressM_DistrictID = M_DistrictID
LEFT JOIN m_kelurahan ON M_DeliveryAddressM_KelurahanID = M_KelurahanID
JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
WHERE M_DeliveryAddressM_CustomerID = customerid
AND M_DeliveryAddressIsActive = "Y"
AND M_DeliveryAddressIsMain = "N");

IF rst IS NULL THEN SET rst = "[]"; END IF;

RETURN rst;

END