BEGIN

DECLARE vendor_code VARCHAR(25);
DECLARE vendor_name VARCHAR(100);
DECLARE vendor_address VARCHAR(255);
DECLARE vendor_city INTEGER;
DECLARE vendor_district INTEGER;
DECLARE vendor_kelurahan INTEGER;
DECLARE vendor_phone VARCHAR(50);
DECLARE vendor_phones VARCHAR(255);
DECLARE vendor_email VARCHAR(100);
DECLARE vendor_postcode VARCHAR(5);
DECLARE vendor_is_company CHAR(1) DEFAULT "N";
DECLARE vendor_pic_name VARCHAR(100);
DECLARE vendor_pic_phone VARCHAR(50);
DECLARE vendor_npwp VARCHAR(100);
DECLARE vendor_note VARCHAR(255);
DECLARE vendor_join DATE;
DECLARE vendor_staff INTEGER;
DECLARE vendor_prospect CHAR(1) DEFAULT "N";

DECLARE address_id INTEGER;
DECLARE address_name VARCHAR(50);
DECLARE address_desc VARCHAR(255);
DECLARE address_province INTEGER;
DECLARE address_city INTEGER;
DECLARE address_district INTEGER;
DECLARE address_village INTEGER;
DECLARE address_postcode VARCHAR(5);
DECLARE address_pic_name VARCHAR(100);
DECLARE address_phones VARCHAR(255);

DECLARE tmp VARCHAR(255);
DECLARE n INTEGER DEFAULT 0;
DECLARE l INTEGER;
DECLARE b_id INTEGEr;
DECLARE b_bank INTEGER;
DECLARE b_name VARCHAR(50);
DECLARE b_number VARCHAR(50);

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

SET vendor_name = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.name"));
SET vendor_address = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.address"));
SET vendor_city = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.city"));
SET vendor_district = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.district"));
SET vendor_kelurahan = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.kelurahan"));
SET vendor_phone = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.phone"));
SET vendor_phones = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.phones"));
SET vendor_email = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.email"));
SET vendor_postcode = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.postcode"));
SET vendor_pic_name = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.pic_name"));
SET vendor_pic_phone = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.pic_phone"));
SET vendor_npwp = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.npwp"));
SET vendor_note = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.note"));
SET vendor_join = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.join"));
SET vendor_prospect = JSON_UNQUOTE(JSON_EXTRACT(jdata, "$.prospect"));

IF vendor_district IS NULL tHEN SET vendor_district = 0; END IF;
IF vendor_kelurahan IS NULL tHEN SET vendor_kelurahan = 0; END IF;
IF vendor_phones IS NULL tHEN SET vendor_phones = "[]"; END IF;

IF vendorid = 0 THEN
    SET vendor_code = (SELECT fn_numbering('VENDOR'));

    INSERT INTO m_vendor(M_VendorCode,
        M_VendorName,
        M_VendorAddress,
        M_VendorM_CityID,
        M_VendorM_DistrictID,
        M_VendorM_KelurahanID,
        M_VendorPhone,
        M_VendorPhones,
        M_VendorEmail,
        M_VendorPostCode,
        M_VendorPICName,
        M_VendorPICPhone,
        M_VendorNPWP,
        M_VendorNote,
        M_VendorUserID)
    SELECT vendor_code, vendor_name, vendor_address, vendor_city, vendor_district, vendor_kelurahan,
        vendor_phone, vendor_phones, vendor_email, vendor_postcode, vendor_pic_name,
        vendor_pic_phone, vendor_npwp, vendor_note, uid;
    
    SET vendorid = (SELECT LAST_INSERT_ID());
ELSE
    UPDATE m_vendor
    SET M_VendorName = vendor_name,
        M_VendorAddress = vendor_address,
        M_VendorM_CityID = vendor_city,
        M_VendorM_DistrictID = vendor_district,
        M_VendorM_KelurahanID = vendor_kelurahan,
        M_VendorPhone = vendor_phone,
        M_VendorPhones = vendor_phones,
        M_VendorEmail = vendor_email,
        M_VendorPostCode = vendor_postcode,
        M_VendorPICName = vendor_pic_name,
        M_VendorPICPhone = vendor_pic_phone,
        M_VendorNPWP = vendor_npwp,
        M_VendorNote = vendor_note,
        M_VendorUserID = uid
    WHERE M_VendorID = vendorid;
END IF;


UPDATE m_vendorbank
SET M_VendorBankIsActive = "O"
WHERE M_VendorBankM_VendorID = vendorid
AND M_VendorBankIsActive = "Y";

SET l = JSON_LENGTH(bdata);
WHILE n < l DO

    SET tmp = JSON_EXTRACT(bdata, CONCAT("$[", n, "]"));
    SET b_bank = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.bank"));
    SET b_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.name"));
    SET b_number = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.number"));
    
    SET b_id = (SELECT M_VendorBankID FROM m_vendorbank WHERE M_VendorBankM_VendorID = vendorid
                AND M_VendorBankIsActive = "O"
                AND M_VendorBankM_BankID = b_bank
                AND M_VendorBankNumber = b_number);
    IF b_id IS NULL THEN
        INSERT INTO m_vendorbank(M_VendorBankM_VendorID, M_VendorBankM_BankID, M_VendorBankName, M_VendorBankNumber)
        SELECT vendorid, b_bank, b_name, b_number;
    ELSE
        UPDATE m_vendorbank
        SET M_VendorBankName = b_name, M_VendorBankIsActive = "Y"
        WHERE M_VendorBankID = b_id;
    END IF;

    SET n = n + 1;
END WHILE;

UPDATE m_vendorbank
SET M_VendorBankIsActive = "N"
WHERE M_VendorBankM_VendorID = vendorid
AND M_VendorBankIsActive = "O";




-- SET address_id = (SELECT M_DeliveryAddressID FROM m_deliveryaddress WHERE M_DeliveryAddressM_VendorID = vendorid AND M_DeliveryADdressIsActive = "Y"
--                 AND M_DeliveryAddressIsMain = "Y");
-- IF address_id IS NULL THEN
--     INSERT INTO m_deliveryaddress(M_DeliveryAddressM_VendorID,
--                     M_DeliveryAddressName,
--                     M_DeliveryAddressDesc,
--                     M_DeliveryAddressM_KelurahanID,
--                     M_DeliveryAddressM_DistrictID,
--                     M_DeliveryAddressM_CityID,
--                     M_DeliveryAddressPostCode,
--                     M_DeliveryAddressPhones,
--                     M_DeliveryAddressPIC,
--                     M_DeliveryAddressIsMain)
--     SELECT vendorid, "Alamat Utama", vendor_address, vendor_kelurahan, vendor_district, 
--         vendor_city, vendor_postcode, vendor_phones, vendor_pic_name, "Y";

--     SET address_id = (SELECT LAST_INSERT_ID());
-- ELSE
--     UPDATE m_deliveryaddress
--     SET M_DeliveryAddressDesc = vendor_address,
--                     M_DeliveryAddressM_KelurahanID = vendor_kelurahan,
--                     M_DeliveryAddressM_DistrictID = vendor_district,
--                     M_DeliveryAddressM_CityID = vendor_city,
--                     M_DeliveryAddressPostCode = vendor_postcode,
--                     M_DeliveryAddressPhones = vendor_phones,
--                     M_DeliveryAddressPIC = vendor_pic_name
--     WHERE M_DeliveryAddressID = address_id;
-- END IF;



-- UPDATE m_deliveryaddress
-- SET M_DeliveryADdressIsActive = "O"
-- WHERE M_DeliveryAddressM_VendorID = vendorid AND M_DeliveryADdressIsActive = "Y"
-- AND M_DeliveryAddressIsMain = "N";

-- SET n = 0;
-- SET l = JSON_LENGTH(addresses);
-- WHILE n < l DO

--     SET tmp = JSON_EXTRACT(addresses, CONCAT("$[", n, "]"));
--     SET address_id = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.id"));
--     SET address_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.name"));
--     SET address_desc = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.desc"));
--     SET address_city = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.city"));
--     SET address_district = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.district"));
--     SET address_village = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.village"));
--     SET address_postcode = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.postcode"));
--     SET address_pic_name = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.pic_name"));
--     SET address_phones = JSON_UNQUOTE(JSON_EXTRACT(tmp, "$.phones"));

--     IF address_phones IS NULL tHEN SET address_phones = "[]"; END IF;

--     IF address_id = 0 THEN
--         INSERT INTO m_deliveryaddress(
--             M_DeliveryAddressM_VendorID,
--             M_DeliveryAddressName,	
--             M_DeliveryAddressDesc,
--             M_DeliveryAddressM_KelurahanID,
--             M_DeliveryAddressM_DistrictID,
--             M_DeliveryAddressM_CityID,
--             M_DeliveryAddressPostCode,
--             M_DeliveryAddressPhones,
--             M_DeliveryAddressPIC
--         )
--         SELECT vendorid, address_name, address_desc, address_village, address_district, address_city, address_postcode, address_phones, address_pic_name;
        
--     ELSE
--         UPDATE m_deliveryaddress
--         SET M_DeliveryAddressName = address_name,
--             M_DeliveryAddressDesc = address_desc,
--             M_DeliveryAddressM_KelurahanID = address_village,
--             M_DeliveryAddressM_DistrictID = address_district,
--             M_DeliveryAddressM_CityID = address_city,
--             M_DeliveryAddressPostCode = address_postcode,
--             M_DeliveryAddressPhones	= address_phones,
--             M_DeliveryAddressPIC = address_pic_name,
--             M_DeliveryAddressIsActive = "Y"
--         WHERE M_DeliveryAddressID = address_id;
--     END IF;

--     SET n = n + 1;
-- END WHILE;

-- UPDATE m_deliveryaddress
-- SET M_DeliveryADdressIsActive = "N"
-- WHERE M_DeliveryAddressM_VendorID = vendorid AND M_DeliveryADdressIsActive = "O";


SELECT "OK" status, JSON_OBJECT("vendor_id", vendorid) data;
COMMIT;


END