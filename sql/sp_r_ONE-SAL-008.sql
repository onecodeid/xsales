BEGIN

DECLARE h_prospect VARCHAR(500);
DECLARE h_category VARCHAR(1000);

SET h_prospect = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("h_type","P","h_id", M_ProspectID,"h_code",
    M_ProspectCode,"h_name",M_ProspectName)), "]")
    FROM m_prospect WHERE M_ProspectIsActive="Y" ORDER BY M_ProspectCode);
SET h_category = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("h_type","P","h_id", M_CategoryID,"h_code",
    M_CategoryCode,"h_name",M_CategoryName)), "]")
    FROM m_category WHERE M_CategoryIsActive="Y" ORDER BY M_CategoryCode);

SELECT h_prospect prospect, h_category category, S_StaffNAme staff_name
FROM s_staff wHERE S_StaffID = staffid;

SELECT L_LeadID lead_id, L_LeadNumber lead_number, L_LeadDate lead_date,
L_LeadNote lead_note, S_StaffID staff_id, S_StaffName staff_name,
CONCAT('[', GROUP_CONCAT(JSON_OBJECT('d_id', l_leaddetailid, 'd_type', l_leaddetailtype,
    'd_cid', l_leaddetailm_categoryid, 'd_pid', l_leaddetailm_prospectid,
    'd_b2b', l_leaddetailb2b, 'd_b2c', l_leaddetailb2c)),']') details
FROM `l_lead`
JOIN s_staff ON L_LeadS_StaffID = S_StaffID
JOIN l_leaddetail ON L_LeadDetailL_LeadID = L_LeadID AND L_LeadDetailIsactive = 'Y'
WHERE `L_LeadIsActive` = 'Y'
AND ((L_LeadS_StaffID = staffid AND staffid > 0) OR staffid = 0)
AND L_LeadDate BETWEEN sdate AND edate
GROUP BY L_LeadID
ORDER BY S_StaffName ASC, L_LeadDate ASC, L_LeadNumber ASC;

END