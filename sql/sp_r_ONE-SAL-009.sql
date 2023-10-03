BEGIN

DECLARE h_prospect TEXT;
DECLARE h_category TEXT;

SET h_prospect = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("h_type","P","h_id", M_ProspectID,"h_code",
    M_ProspectCode,"h_name",M_ProspectName)), "]")
    FROM m_prospect WHERE M_ProspectIsActive="Y" ORDER BY M_ProspectCode);

SET h_category = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("h_type","P","h_id", M_LeadCategoryID,"h_code",
    M_LeadCategoryCode,"h_name",M_LeadCategoryacronym)), "]")
    FROM m_leadcategory WHERE M_LeadCategoryIsActive="Y" ORDER BY M_LeadCategoryAcronym);
-- SET h_category = (SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT("h_type","P","h_id", M_CategoryID,"h_code",
--     M_CategoryCode,"h_name",M_CategoryName)), "]")
--     FROM m_category WHERE M_CategoryIsActive="Y" ORDER BY M_CategoryCode);

SELECT h_prospect prospect, h_category category;

SELECT staff_id, staff_name,
CONCAT('[', GROUP_CONCAT(JSON_OBJECT('d_type', lead_type,
    'd_cid', lead_category, 'd_pid', lead_prospect,
    'd_b2b', lead_b2b, 'd_b2c', lead_b2c)),']') details

FROM (
    SELECT S_StaffID staff_id, S_StaffName staff_name,
    L_LeadDetailType lead_type, l_leaddetailm_leadcategoryid lead_category, l_leaddetailm_prospectid lead_prospect,
    IFNULL(M_ProspectName, '') category_name, IFNULL(M_LeadCategoryName, '') prospect_name,
    SUM(l_leaddetailb2b) lead_b2b, SUM(l_leaddetailb2c) lead_b2c
    FROM `l_lead`
    JOIN s_staff ON L_LeadS_StaffID = S_StaffID
    JOIN l_leaddetail ON L_LeadDetailL_LeadID = L_LeadID AND L_LeadDetailIsactive = 'Y'
    LEFT JOIN m_prospect ON L_LeadDetailM_ProspectID = M_ProspectID
    LEFT JOIN m_leadcategory ON L_LeadDetailM_LeadCategoryID = M_LeadCategoryID
    WHERE `L_LeadIsActive` = 'Y'
    AND L_LeadDate BETWEEN sdate AND edate
    GROUP BY S_StaffID, L_LeadDetailType, L_LeadDetailM_LeadCategoryID, L_LeadDetailM_ProspectID
    ORDER BY S_StaffName ASC, L_LeadDetailType DESC, L_LeadDetailM_ProspectID, L_LeadDetailM_LeadCategoryID
) x GROUP BY staff_id;

-- SELECT staff_id, staff_name,
-- CONCAT('[', GROUP_CONCAT(JSON_OBJECT('d_type', lead_type,
--     'd_cid', lead_category, 'd_pid', lead_prospect,
--     'd_b2b', lead_b2b, 'd_b2c', lead_b2c)),']') details

-- FROM (
-- SELECT S_StaffID staff_id, S_StaffName staff_name,
-- L_LeadDetailType lead_type, l_leaddetailm_categoryid lead_category, l_leaddetailm_prospectid lead_prospect,
-- IFNULL(M_ProspectName, '') category_name, IFNULL(M_CategoryName, '') prospect_name,
-- SUM(l_leaddetailb2b) lead_b2b, SUM(l_leaddetailb2c) lead_b2c
-- FROM `l_lead`
-- JOIN s_staff ON L_LeadS_StaffID = S_StaffID
-- JOIN l_leaddetail ON L_LeadDetailL_LeadID = L_LeadID AND L_LeadDetailIsactive = 'Y'
-- LEFT JOIN m_prospect ON L_LeadDetailM_ProspectID = M_ProspectID
-- LEFT JOIN m_category ON L_LeadDetailM_CategoryID = M_CategoryID
-- WHERE `L_LeadIsActive` = 'Y'
-- AND L_LeadDate BETWEEN sdate AND edate
-- GROUP BY S_StaffID, L_LeadDetailType, L_LeadDetailM_CategoryID, L_LeadDetailM_ProspectID
-- ORDER BY S_StaffName ASC, L_LeadDetailType DESC, L_LeadDetailM_ProspectID, L_LeadDetailM_CategoryID
-- ) x GROUP BY staff_id;

END