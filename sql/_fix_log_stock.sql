BEGIN

DECLARE done INT DEFAULT 0;
DECLARE vid, vqty, vitem INT;
DECLARE vtype VARCHAR(50);
DECLARE vdate DATE;

-- I_StockQty	I_StockMinQty	I_StockLastTransCode	I_StockLastTransDate	I_StockLastTransRefID

    DECLARE cursor_name CURSOR FOR
    select * from (
select "SALES.DELIVERY" as type, L_SalesDetailID as tid,
L_SalesDate as tdate, L_SalesDetailA_ItemID as titem, (0-L_SalesDetailQty) as tqty
from l_sales
join l_salesdetail on l_salesdetaill_salesid = l_salesid
and l_salesdetailisactive = "Y"
where l_salesisactive = "Y"

union

select "PURCHASE.RECEIVE" as type, P_PurchaseDetailID as tid,
P_PurchaseDate as tdate, P_PurchaseDetailA_ItemID as titem, P_PurchaseDetailQty as tqty
from p_purchase
join p_purchasedetail on p_purchasedetailp_purchaseid = p_purchaseid
and p_purchasedetailisactive = "Y"
where p_purchaseisactive = "Y"

union

select "INV.ADJUSTMENT" as type, I_AdjustDetailID as tid,
I_AdjustDate as tdate, I_AdjustDetailM_ItemID as titem, I_AdjustDetailQty as tqty
FROM i_adjust
JOIN i_adjustdetail ON I_AdjustDetailI_AdjustID = I_AdjustID AND I_AdjustDetailIsActive = "Y"
WHERE I_AdjustIsActive = "Y" AND I_AdjustDate > "2023-08-01"
) x order by tdate asc, type asc;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    
TRUNCATE xsales_log.log_stock;
UPDATE i_stock SET I_StockQty = 0, I_StockLastTransCode = "LOG.ADJUST", I_StockLastTransDate = null, I_StockLastTransRefID = 0;

    OPEN cursor_name;
    read_loop: LOOP
        FETCH NEXT FROM cursor_name INTO vtype, vid, vdate, vitem, vqty;
        IF done THEN
            LEAVE read_loop;
        END IF;
        -- Process the fetched data, e.g., display it
UPDATE i_stock SET I_StockQty = I_StockQty + vqty, I_StockLastTransQty = vqty, I_StockLastTransCode = vtype, I_StockLastTransDate = vdate,	I_StockLastTransRefID = vid 
WHERE I_StockM_ItemID = vitem;

        SELECT vtype, vid, vdate, vitem, vqty;
    END LOOP;
    CLOSE cursor_name;
END