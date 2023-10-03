BEGIN 

DECLARE item_id INTEGER;
DECLARE code VARCHAR(25);
DECLARE ref_id INTEGER;
DECLARE n_qty DOUBLE;
DECLARE n_hpp DOUBLE;
DECLARE total_n_hpp DOUBLE;
DECLARE total_stock DOUBLE;
DECLARE o_hpp DOUBLE;
DECLARE total_o_hpp DOUBLE;
DECLARE x_hpp DOUBLE;

SELECT I_StockM_ItemID, I_StockLastTransCode, I_StockLastTransRefID
INTO item_id, code, ref_id
FROM i_stock 
WHERE I_StockID = stockid;

IF code = "PURCHASE.RECEIVE" THEN
    SELECT P_ReceiveDetailQty, 
        ((P_PurchaseDetailPrice * (100-P_PurchaseDetailDisc) / 100) - P_PurchaseDetailDiscRp)
            * IF(P_PurchaseIncludePPN="N" && P_PurchaseDetailPPN="Y", 1.1, 1)
    INTO n_qty, n_hpp
    FROM p_receivedetail
    JOIN p_purchasedetail ON P_ReceiveDetailP_PurchaseDetailID = P_PurchaseDetailID
    JOIN p_purchase ON P_PurchaseDetailP_PurchaseID = P_PurchaseID
    WHERE P_ReceiveDetailID = ref_id;
END IF;

SET total_stock = (SELECT SUM(I_StockQty)
    FROM i_stock
    WHERE I_StockM_ItemID = item_id
    AND I_StockIsActive = "Y"
    AND I_StockQty IS NOT NULL);
IF total_stock IS NULL tHEN SET total_stock = 0; END IF;

SET o_hpp = (SELECT M_ItemDefaultHPP FROM m_item WHERE M_ItemID = item_id);
IF o_hpp IS NULL THEN SET o_hpp = 0; END IF;

SET total_o_hpp = o_hpp * (total_stock - n_qty);
SET total_n_hpp = n_hpp * n_qty;

SET x_hpp = (total_o_hpp + total_n_hpp) / total_stock;


INSERT INTO one_account_aw_log.log_hpp(Log_HppDate,
Log_HppM_ItemID,
Log_HppCode,
Log_HppRefID,
Log_HppRefNumber,
Log_HppBeforeQty,
Log_HppQty,
Log_HppAfterQty,
Log_HppBeforeAmount,
Log_HppAfterAmount)
SELECT date(now()), item_id, code, ref_id, "", I_StockQty - I_StockLastTransQty, I_StockLastTransQty, I_StockQty, o_hpp, n_hpp
FROM i_stock WHERE I_StockID = stockid;

UPDATE m_item
SET M_ItemDefaultHPP = x_hpp
WHERE M_ItemID = item_id;





END