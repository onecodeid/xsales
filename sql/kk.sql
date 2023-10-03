alter table p_purchiveorder
add column P_PurchiveOrderPaid double not null default 0 after P_PurchiveOrderGrandTotal,
add column P_PurchiveOrderUnpaid double not null default 0 after P_PurchiveOrderPaid,
add column P_PurchiveOrderLunas char(1) not null default "N" after P_PurchiveOrderUnpaid,
add index(P_PurchiveOrderPaid), add index(P_PurchiveOrderUnpaid), add index(P_PurchiveOrderLunas);