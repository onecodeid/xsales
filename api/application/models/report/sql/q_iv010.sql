SELECT m_itemid item_id, m_itemcode item_code, m_itemname item_name,
    sum(l_deliverydetailqty) item_qty, IFNULL(m_unitname, '') unit_name
FROM l_deliverydetail
JOIN l_delivery on l_deliverydetaill_deliveryid = l_deliveryid and l_deliverydate between ? and ? and l_deliveryisactive = "Y"
    and l_deliverym_warehouseid = ?
JOIN m_item on l_deliverydetaila_itemid = m_itemid
JOIN m_unit on m_itemm_unitid = m_unitid
where l_deliverydetailisactive = "Y"
group by m_itemid
order by item_qty desc
limit 25

