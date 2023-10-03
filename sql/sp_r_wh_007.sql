BEGIN

SELECT M_ItemID item_id, M_ItemCode item_code, M_ItemName item_name, M_ItemMinStock stock_min, IFNULL(I_StockQty, 0) stock_qty, M_ItemHPP item_hpp, (M_ItemHPP * IFNULL(I_StockQty, 0)) stock_nominal, M_CategoryID category_id, M_CategoryName category_name, M_RackCode rack_code
FROM m_item
JOIN m_category ON M_ItemM_CategoryID = m_categoryID AND ((M_CategoryID = categoryid AND categoryid <> 0) OR categoryid = 0)
LEFT JOIN i_stock ON I_StockM_ItemID= M_ItemID AND I_StockIsActive = "Y"
LEFT JOIN m_rack ON M_ItemM_RackID = M_RackID

WHERE M_ItemIsActive="Y"
ORDER BY M_ItemName ASC;

END