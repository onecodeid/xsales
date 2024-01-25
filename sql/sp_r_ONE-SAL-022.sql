DROP PROCEDURE `sp_r_ONE-SAL-022`;
DELIMITER ;;
CREATE PROCEDURE `sp_r_ONE-SAL-022` (IN `sdate` date, IN `edate` date, IN `customerid` INT)
BEGIN

SELECT invoice_id, invoice_date, invoice_number,

staff_id, staff_name, customer_id, customer_name,
item_id, item_name, item_qty, item_price, 
sum(item_qty * item_disc) item_disc,
sum(item_qty * item_price) item_bruto,
sum(item_qty * item_disctotal) item_disctotal, 
sum(item_subtotal) item_subtotal, 
item_incppn, 
item_ppn,

sum(item_ppnamount) item_ppnamount,
sum(item_total) item_total, unit_name

FROM (
SELECT invoice_id, invoice_date, invoice_number,

staff_id, staff_name, customer_id, customer_name,
item_id, item_name, item_qty, item_price, 
item_disc,
(item_subtotal * item_disctotal / 100) as  item_disctotal, 
item_subtotal * (100-item_disctotal)/100 as item_subtotal, 
item_incppn, 
item_ppn,

IF(item_incppn = "N",
item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (100), 
item_subtotal * (100-item_disctotal) * invoice_ppnvalue / (1 + invoice_ppnvalue) ) 
as item_ppnamount,

item_total, unit_name
FROM (
    SELECT L_SalesID invoice_id, L_SalesDate invoice_date, L_SalesNumber invoice_number,
        
        0 invoice_ppnvalue,
        0 staff_id, '' staff_name, 
        M_CustomerID customer_id, IF(M_CustomerCode = 'C.UMUM', CONCAT(M_customerName, " - ", L_SalesM_CustomerName), M_customerName) customer_name,
        M_ItemID item_id, M_ItemName item_name, L_SalesDetailQty item_qty,
        L_SalesDetailPrice item_price,


        (L_SalesDetailPrice * L_SalesDetailDisc / 100) + L_SalesDetailDiscRp item_disc,
        0 item_disctotal,
        -- ((L_SalesDetailPrice * (100-L_SalesDetailDisc) / 100) - L_SalesDetailDiscRp) * L_SalesDetailQty item_subtotal,
        L_SalesDetailSubTotal item_subtotal,

    "N" item_incppn,
    "N" item_ppn,
    0 item_ppnamount,
    L_SalesDetailTotal item_total,
    IFNULL(M_UnitName, '') unit_name
    FROM l_salesdetail
    JOIN l_sales ON L_SalesDetailL_SalesID = L_SalesID
    AND L_SalesDate BETWEEN sdate AND edate
    JOIN m_item ON L_SalesDetailA_ItemID = M_ItemID
    LEFT JOIN m_unit ON M_ItemM_UnitID = M_UnitID


    JOIN m_customer ON L_SalesM_CustomerID = M_CustomerID
    WHERE L_SalesDetailIsActive = "Y" 
    AND ((M_CustomerID = customerid AND customerid <> 0) OR customerid = 0)
    ORDER BY L_SalesDate, M_CustomerName, M_ItemName) xxx 
    ORDER BY invoice_date, customer_name, invoice_number
) yyy GROUP BY invoice_id ORDER BY invoice_date, invoice_number;
























END;;
DELIMITER ;