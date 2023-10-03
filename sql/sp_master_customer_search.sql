BEGIN

SELECT * 
FROM (
(SELECT M_CustomerID customer_id, M_CustomerCode customer_code, M_CustomerName customer_name,
    M_CustomerAddress customer_address, IFNULL(M_KelurahanName, "") customer_kelurahan,
    IFNULL(M_DistrictName, "") customer_district, IFNULL(M_CityName, "") customer_city,
    IFNULL(M_ProvinceName, "") customer_province, 10 score, M_CustomerIndex customer_index
FROM m_customer
LEFT JOIN m_kelurahan ON M_CustomerM_KelurahanID = M_KelurahanID
LEFT JOIN m_district ON M_CustomerM_DistrictID = M_DistrictID
LEFT JOIN m_city ON M_CustomerM_CityID = M_CityID
LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
WHERE M_CustomerIndex = phrase_full AND M_CustomerIsActive = "Y")

UNION

(SELECT M_CustomerID customer_id, M_CustomerCode customer_code, M_CustomerName customer_name,
    M_CustomerAddress customer_address, IFNULL(M_KelurahanName, "") customer_kelurahan,
    IFNULL(M_DistrictName, "") customer_district, IFNULL(M_CityName, "") customer_city,
    IFNULL(M_ProvinceName, "") customer_province, 9 score, M_CustomerIndex customer_index
FROM m_customer
LEFT JOIN m_kelurahan ON M_CustomerM_KelurahanID = M_KelurahanID
LEFT JOIN m_district ON M_CustomerM_DistrictID = M_DistrictID
LEFT JOIN m_city ON M_CustomerM_CityID = M_CityID
LEFT JOIN m_province ON M_CityM_ProvinceID = M_ProvinceID
WHERE 
    (M_CustomerIndex LIKE CONCAT("%[",phrase1,"]%") AND
    ((M_CustomerIndex LIKE CONCAT("%[",phrase2,"]%") AND phrase2 != "") OR phrase2 = "") ) 
    AND M_CustomerIsActive = "Y") ) x GROUP BY customer_id ORDER BY score DESC;


END