BEGIN

-- DECLARE sdate DATE;
-- DECLARE edate DATE;

DECLARE sdate1 DATE;
DECLARE edate1 DATE;
DECLARE sdate2 DATE;
DECLARE edate2 DATE;
DECLARE sdate3 DATE;
DECLARE edate3 DATE;
DECLARE sdate4 DATE;
DECLARE edate4 DATE;
DECLARE sdate5 DATE;
DECLARE edate5 DATE;
DECLARE sdate6 DATE;
DECLARE edate6 DATE;
DECLARE sdate7 DATE;
DECLARE edate7 DATE;
DECLARE sdate8 DATE;
DECLARE edate8 DATE;
DECLARE sdate9 DATE;
DECLARE edate9 DATE;
DECLARE sdate10 DATE;
DECLARE edate10 DATE;
DECLARE sdate11 DATE;
DECLARE edate11 DATE;
DECLARE sdate12 DATE;
DECLARE edate12 DATE;

DECLARE n INTEGER DEFAULT 1;

-- SET sdate = DATE_SUB(date(now()), INTERVAL 3 MONTH);
-- SET edate = DATE_SUB(date(now()), INTERVAL 1 MONTH);

-- SET sdate = DATE_SUB(LAST_DAY(sdate),INTERVAL DAY(LAST_DAY(sdate))-1 DAY);
-- SET edate = LAST_DAY(edate);

SET sdate12 = DATE_SUB(date(now()), INTERVAL 1 MONTH);
SET edate12 = LAST_DAY(sdate12);
SET sdate12 = DATE_SUB(edate12,INTERVAL DAY(edate12)-1 DAY);

SET sdate11 = DATE_SUB(date(now()), INTERVAL 2 MONTH);
SET edate11 = LAST_DAY(sdate11);
SET sdate11 = DATE_SUB(edate11,INTERVAL DAY(edate11)-1 DAY);

SET sdate10 = DATE_SUB(date(now()), INTERVAL 3 MONTH);
SET edate10 = LAST_DAY(sdate10);
SET sdate10 = DATE_SUB(edate10,INTERVAL DAY(edate10)-1 DAY);

SET sdate9 = DATE_SUB(date(now()), INTERVAL 4 MONTH);
SET edate9 = LAST_DAY(sdate9);
SET sdate9 = DATE_SUB(edate9,INTERVAL DAY(edate9)-1 DAY);

SET sdate8 = DATE_SUB(date(now()), INTERVAL 5 MONTH);
SET edate8 = LAST_DAY(sdate8);
SET sdate8 = DATE_SUB(edate8,INTERVAL DAY(edate8)-1 DAY);

SET sdate7 = DATE_SUB(date(now()), INTERVAL 6 MONTH);
SET edate7 = LAST_DAY(sdate7);
SET sdate7 = DATE_SUB(edate7,INTERVAL DAY(edate7)-1 DAY);

SET sdate6 = DATE_SUB(date(now()), INTERVAL 7 MONTH);
SET edate6 = LAST_DAY(sdate6);
SET sdate6 = DATE_SUB(edate6,INTERVAL DAY(edate6)-1 DAY);

SET sdate5 = DATE_SUB(date(now()), INTERVAL 8 MONTH);
SET edate5 = LAST_DAY(sdate5);
SET sdate5 = DATE_SUB(edate5,INTERVAL DAY(edate5)-1 DAY);

SET sdate4 = DATE_SUB(date(now()), INTERVAL 9 MONTH);
SET edate4 = LAST_DAY(sdate4);
SET sdate4 = DATE_SUB(edate4,INTERVAL DAY(edate4)-1 DAY);

SET sdate3 = DATE_SUB(date(now()), INTERVAL 10 MONTH);
SET edate3 = LAST_DAY(sdate3);
SET sdate3 = DATE_SUB(edate3,INTERVAL DAY(edate3)-1 DAY);

SET sdate2 = DATE_SUB(date(now()), INTERVAL 11 MONTH);
SET edate2 = LAST_DAY(sdate2);
SET sdate2 = DATE_SUB(edate2,INTERVAL DAY(edate2)-1 DAY);

SET sdate1 = DATE_SUB(date(now()), INTERVAL 12 MONTH);
SET edate1 = LAST_DAY(sdate1);
SET sdate1 = DATE_SUB(edate1,INTERVAL DAY(edate1)-1 DAY);

-- TRUNCATE TABLE an_recapt_monthly_w;
-- TRUNCATE TABLE an_recapt_monthly;

TRUNCATE TABLE an_recapt_month;

INSERT INTO an_recapt_month(An_RecaptMonthM_ItemID,
    An_RecaptMonthM_WarehouseID,
    An_RecaptMonthMonth1Date,
    An_RecaptMonthMonth1Qty,
    An_RecaptMonthMonth2Date,	
    An_RecaptMonthMonth2Qty,
    An_RecaptMonthMonth3Date,
    An_RecaptMonthMonth3Qty,
    An_RecaptMonthMonth4Date,
    An_RecaptMonthMonth4Qty,
    An_RecaptMonthMonth5Date,
    An_RecaptMonthMonth5Qty,
    An_RecaptMonthMonth6Date,
    An_RecaptMonthMonth6Qty,
    An_RecaptMonthMonth7Date,
    An_RecaptMonthMonth7Qty,
    An_RecaptMonthMonth8Date,
    An_RecaptMonthMonth8Qty,
    An_RecaptMonthMonth9Date,
    An_RecaptMonthMonth9Qty,
    An_RecaptMonthMonth10Date,
    An_RecaptMonthMonth10Qty,
    An_RecaptMonthMonth11Date,
    An_RecaptMonthMonth11Qty,
    An_RecaptMonthMonth12Date,
    An_RecaptMonthMonth12Qty,
    An_RecaptMonth3MonthAv)
SELECT 
    L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID,
    sdate1,
    SUM(IF(L_DeliveryDate BETWEEN sdate1 AND edate1, L_DeliveryDetailQty, 0)),
    sdate2,
    SUM(IF(L_DeliveryDate BETWEEN sdate2 AND edate2, L_DeliveryDetailQty, 0)),
    sdate3,
    SUM(IF(L_DeliveryDate BETWEEN sdate3 AND edate3, L_DeliveryDetailQty, 0)),
    sdate4,
    SUM(IF(L_DeliveryDate BETWEEN sdate4 AND edate4, L_DeliveryDetailQty, 0)),
    sdate5,
    SUM(IF(L_DeliveryDate BETWEEN sdate5 AND edate5, L_DeliveryDetailQty, 0)),
    sdate6,
    SUM(IF(L_DeliveryDate BETWEEN sdate6 AND edate6, L_DeliveryDetailQty, 0)),
    sdate7,
    SUM(IF(L_DeliveryDate BETWEEN sdate7 AND edate7, L_DeliveryDetailQty, 0)),
    sdate8,
    SUM(IF(L_DeliveryDate BETWEEN sdate8 AND edate8, L_DeliveryDetailQty, 0)),
    sdate9,
    SUM(IF(L_DeliveryDate BETWEEN sdate9 AND edate9, L_DeliveryDetailQty, 0)),
    sdate10,
    SUM(IF(L_DeliveryDate BETWEEN sdate10 AND edate10, L_DeliveryDetailQty, 0)),
    sdate11,
    SUM(IF(L_DeliveryDate BETWEEN sdate11 AND edate11, L_DeliveryDetailQty, 0)),
    sdate12,
    SUM(IF(L_DeliveryDate BETWEEN sdate12 AND edate12, L_DeliveryDetailQty, 0)),
    0
FROM l_deliverydetail
JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
    AND L_DeliveryIsActive = "Y"
    AND L_DeliveryDate BETWEEN sdate1 AND edate12
WHERE L_DeliveryDetailIsActive = "Y"
GROUP BY L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID;

-- INSERT ALL WAREHOUSE
INSERT INTO an_recapt_month(An_RecaptMonthM_ItemID,
    An_RecaptMonthM_WarehouseID,
    An_RecaptMonthMonth1Date,
    An_RecaptMonthMonth1Qty,
    An_RecaptMonthMonth2Date,	
    An_RecaptMonthMonth2Qty,
    An_RecaptMonthMonth3Date,
    An_RecaptMonthMonth3Qty,
    An_RecaptMonthMonth4Date,
    An_RecaptMonthMonth4Qty,
    An_RecaptMonthMonth5Date,
    An_RecaptMonthMonth5Qty,
    An_RecaptMonthMonth6Date,
    An_RecaptMonthMonth6Qty,
    An_RecaptMonthMonth7Date,
    An_RecaptMonthMonth7Qty,
    An_RecaptMonthMonth8Date,
    An_RecaptMonthMonth8Qty,
    An_RecaptMonthMonth9Date,
    An_RecaptMonthMonth9Qty,
    An_RecaptMonthMonth10Date,
    An_RecaptMonthMonth10Qty,
    An_RecaptMonthMonth11Date,
    An_RecaptMonthMonth11Qty,
    An_RecaptMonthMonth12Date,
    An_RecaptMonthMonth12Qty)
SELECT * FROM (
    SELECT An_RecaptMonthM_ItemID,
    0,
    An_RecaptMonthMonth1Date,
    SUM(An_RecaptMonthMonth1Qty),
    An_RecaptMonthMonth2Date,	
    SUM(An_RecaptMonthMonth2Qty),
    An_RecaptMonthMonth3Date,
    SUM(An_RecaptMonthMonth3Qty),
    An_RecaptMonthMonth4Date,
    SUM(An_RecaptMonthMonth4Qty),
    An_RecaptMonthMonth5Date,
    SUM(An_RecaptMonthMonth5Qty),
    An_RecaptMonthMonth6Date,
    SUM(An_RecaptMonthMonth6Qty),
    An_RecaptMonthMonth7Date,
    SUM(An_RecaptMonthMonth7Qty),
    An_RecaptMonthMonth8Date,
    SUM(An_RecaptMonthMonth8Qty),
    An_RecaptMonthMonth9Date,
    SUM(An_RecaptMonthMonth9Qty),
    An_RecaptMonthMonth10Date,
    SUM(An_RecaptMonthMonth10Qty),
    An_RecaptMonthMonth11Date,
    SUM(An_RecaptMonthMonth11Qty),
    An_RecaptMonthMonth12Date,
    SUM(An_RecaptMonthMonth12Qty)
    FROM an_recapt_month
    WHERE An_RecaptMonthM_WarehouseID <> 0
    GROUP BY An_RecaptMonthM_ItemID
) x;
-- END OF INSERT ALL WAREHOUSE

UPDATE an_recapt_month
SET An_RecaptMonth3MonthAv = (An_RecaptMonthMonth12Qty + An_RecaptMonthMonth11Qty + An_RecaptMonthMonth10Qty) / 3;

-- FREQUENT DELIVERY
TRUNCATE TABLE an_recapt_freq;

INSERT INTO an_recapt_freq(
    An_RecaptFreqM_ItemID,
    An_RecaptFreqM_WarehouseID,
    An_RecaptFreqWeek1,
    An_RecaptFreqWeek2,
    An_RecaptFreqWeek3,
    An_RecaptFreqWeek4,
    An_RecaptFreqMonth,
    An_RecaptFreq2Month,
    An_RecaptFreqStatus
)
SELECT 
    L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID,
    0, 0, 0, 0,
    SUM(IF(L_DeliveryDate BETWEEN sdate12 AND edate12, 1, 0)),
    SUM(IF(L_DeliveryDate BETWEEN sdate11 AND edate12, 1, 0)),
    ''
FROM l_deliverydetail
JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
    AND L_DeliveryIsActive = "Y"
    AND L_DeliveryDate BETWEEN sdate1 AND edate12
WHERE L_DeliveryDetailIsActive = "Y"
GROUP BY L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID;

INSERT INTO an_recapt_freq(
    An_RecaptFreqM_ItemID,
    An_RecaptFreqM_WarehouseID,
    An_RecaptFreqWeek1,
    An_RecaptFreqWeek2,
    An_RecaptFreqWeek3,
    An_RecaptFreqWeek4,
    An_RecaptFreqMonth,
    An_RecaptFreq2Month,
    An_RecaptFreqStatus
)
SELECT 
    L_DeliveryDetailA_ItemID, 0,
    0, 0, 0, 0,
    SUM(IF(L_DeliveryDate BETWEEN sdate12 AND edate12, 1, 0)),
    SUM(IF(L_DeliveryDate BETWEEN sdate11 AND edate12, 1, 0)),
    ''
FROM l_deliverydetail
JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
    AND L_DeliveryIsActive = "Y"
    AND L_DeliveryDate BETWEEN sdate1 AND edate12
WHERE L_DeliveryDetailIsActive = "Y"
GROUP BY L_DeliveryDetailA_ItemID;

UPDATE an_recapt_freq
SET an_recaptfreqstatus = 
    IF (An_RecaptFreqMonth >= 8, 'A',
        IF(An_RecaptFreqMonth >= 4, 'B',
        IF(An_RecaptFreqMonth >= 1, 'C',
        'D')));
-- END OF FREQUENT DELIVERY

-- INSERT INTO an_recapt_monthly_w(
--     An_RecaptMonthlyWMonth,
--     An_RecaptMonthlyWYear,
--     An_RecaptMonthlyWM_ItemID,
--     An_RecaptMonthlyWM_WarehouseID,
--     An_RecaptMonthlyWQty
-- )
-- SELECT month(L_DeliveryDate), year(L_DeliveryDate), L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID, sum(L_DeliveryDetailQty)
-- FROM l_deliverydetail
-- JOIN l_delivery ON L_DeliveryDetailL_DeliveryID = L_DeliveryID
--     AND L_DeliveryIsActive = "Y"
--     AND L_DeliveryDate BETWEEN sdate AND edate
-- WHERE L_DeliveryDetailIsActive = "Y"
-- GROUP BY LEFT(L_DeliveryDate, 7), L_DeliveryDetailA_ItemID, L_DeliveryM_WarehouseID;

END