BEGIN

DECLARE finished INTEGER DEFAULT 0;
DECLARE logig varchar(100) DEFAULT "";

DECLARE laststock DOUBLE DEFAULT 0;
DECLARE logid INTEGER;
DECLARE beforeqty DOUBLE;
DECLARE qty DOUBLE;
DECLARE afterqty DOUBLE;

-- Log_StockCode	varchar(25) NULL	
-- Log_StockRefID	int(11) [0]	
-- Log_StockRefNumber	varchar(25) NULL	
-- Log_StockM_CustomerID	int(11) [0]	
-- Log_StockM_SupplierID	int(11) [0]	
-- Log_StockM_ItemID	int(11) [0]	
-- Log_StockM_WarehouseID	int(11) [0]	
-- Log_StockFromToM_WarehouseID	int(11) [0]	
-- Log_StockBeforeQty	double [0]	
-- Log_StockQty	double [0]	
-- Log_StockAfterQty

	-- declare cursor for employee email
DEClARE curLog
    CURSOR FOR 
        SELECT Log_StockID, Log_StockBeforeQty, Log_StockQty, Log_StockAfterQty
        FROM log_stock WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid AND Log_StockIsActive = "Y"
        AND Log_StockDate >= CONCAT(logdate, " 00:00:00")
        ORDER BY Log_StockDate ASC, Log_StockID ASC;

-- declare NOT FOUND handler
DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET finished = 1;

SET laststock = (SELECT Log_StockAfterQty
    FROM log_stock WHERE Log_StockM_ItemID = itemid AND Log_StockM_WarehouseID = warehouseid AND Log_StockIsActive = "Y"
    AND Log_StockDate < CONCAT(logdate, " 00:00:00")
    ORDER BY Log_StockDate DESC, Log_StockID DESC
    LIMIT 1);

IF laststock IS NULL THEN SET laststock = 0; END IF;

OPEN curLog;

getLog: LOOP
    FETCH curLog INTO logid, beforeqty, qty, afterqty;
    IF finished = 1 THEN 
        LEAVE getLog;
    END IF;
    
    -- ADJUST
    UPDATE log_stock SET Log_StockBeforeQty = laststock, Log_StockAfterQty = laststock + Log_StockQty WHERE Log_StockID = logid;
    SET laststock = laststock + qty;

END LOOP getLog;
CLOSE curLog;

END