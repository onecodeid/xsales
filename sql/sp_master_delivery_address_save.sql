BEGIN

-- DECLARE address_id INTEGER;
DECLARE address_name VARCHAR(50);
DECLARE address_desc VARCHAR(255);
DECLARE address_province INTEGER;
DECLARE address_city INTEGER;
DECLARE address_district INTEGER;
DECLARE address_village INTEGER;
DECLARE address_postcode VARCHAR(5);
DECLARE address_pic_name VARCHAR(100);
DECLARE address_phones VARCHAR(255);

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , @p2 as message, @tb as table_name;

ROLLBACK;
END;

START TRANSACTION;

-- SET address_id = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.id"));
SET address_name = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.name"));
SET address_desc = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.desc"));
SET address_city = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.city"));
SET address_district = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.district"));
SET address_village = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.village"));
SET address_postcode = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.postcode"));
SET address_pic_name = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.pic_name"));
SET address_phones = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.phones"));

IF address_phones IS NULL tHEN SET address_phones = "[]"; END IF;

IF address_id = 0 THEN
    INSERT INTO m_deliveryaddress(
        M_DeliveryAddressM_CustomerID,
        M_DeliveryAddressName,	
        M_DeliveryAddressDesc,
        M_DeliveryAddressM_KelurahanID,
        M_DeliveryAddressM_DistrictID,
        M_DeliveryAddressM_CityID,
        M_DeliveryAddressPostCode,
        M_DeliveryAddressPhones,
        M_DeliveryAddressPIC
    )
    SELECT customer_id, address_name, address_desc, IFNULL(address_village, 0), IFNULL(address_district, 0), address_city, address_postcode, address_phones, address_pic_name;

    SET address_id = (SELECT LAST_INSERT_ID());
ELSE
    UPDATE m_deliveryaddress
    SET M_DeliveryAddressName = address_name,
        M_DeliveryAddressDesc = address_desc,
        M_DeliveryAddressM_KelurahanID = IFNULL(address_village, 0),
        M_DeliveryAddressM_DistrictID = IFNULL(address_district, 0),
        M_DeliveryAddressM_CityID = address_city,
        M_DeliveryAddressPostCode = address_postcode,
        M_DeliveryAddressPhones	= address_phones,
        M_DeliveryAddressPIC = address_pic_name,
        M_DeliveryAddressIsActive = "Y"
    WHERE M_DeliveryAddressID = address_id;
END IF;

SELECT "OK" status, JSON_OBJECT("customer_id", customer_id, "address_id", address_id) data;
COMMIT;

END