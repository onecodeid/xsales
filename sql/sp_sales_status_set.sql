Begin

DECLARE rcv_y INTEGER;
DECLARE rcv_x INTEGER;
DECLARE rcv INTEGER;
DECLARE status CHAR(1);
DECLARE rcv_z INTEGER;



    Set rcv = (select count(L_SalesDetailID) from l_salesdetail where L_SalesDetailL_SalesID = slid and L_SalesDetailIsActive = "Y" AND L_SalesDetailDone = "N");
    Set rcv_x = (select count(L_SalesDetailID) from l_salesdetail where L_SalesDetailL_SalesID = slid and L_SalesDetailIsActive = "Y" AND L_SalesDetailDone = "X");
    Set rcv_y = (select count(L_SalesDetailID) from l_salesdetail where L_SalesDetailL_SalesID = slid and L_SalesDetailIsActive = "Y" AND L_SalesDetailDone = "Y");
    Set rcv_z = (select count(L_SalesDetailID) from l_salesdetail where L_SalesDetailL_SalesID = slid and L_SalesDetailIsActive = "Y" AND L_SalesDetailDone = "Z");

    SET rcv_y = rcv_y + rcv_z;

if rcv_x > 0 or (rcv_y > 0 and rcv > 0) then set status = "X";
elseif rcv_y > 0 and rcv = 0 then set status = "Y";
else set status = "N"; end if;

    Update l_sales set L_SalesDone = status where L_SalesID = slid;


End