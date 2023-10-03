SELECT DATE(Log_ActivityCreated) log_date,
TIME(Log_ActivityCreated) log_time,
Log_ActivityWhat log_what,
Log_ActivityModule log_module,
Log_ActivityRefID log_ref_id,
Log_ActivityRefNumber log_ref_number,
Log_ActivityRefDate log_ref_date,
Log_ActivityUID log_uid,
Log_ActivityStaffID log_staff_id,
Log_ActivityStaffName log_staff_name
FROM one_account_aw_log.log_activity
WHERE Log_ActivityCreated BETWEEN CONCAT(?, ' 00:00:00') AND CONCAT(?, ' 23:59:59')
    AND (Log_ActivityRefNumber LIKE ? OR Log_ActivityModule LIKE ? OR Log_ActivityStaffName LIKE ?)
ORDER BY Log_ActivityCreated DESC
LIMIT ? OFFSET ?
###
SELECT COUNT(Log_ActivityID) n
FROM one_account_aw_log.log_activity
WHERE Log_ActivityCreated BETWEEN CONCAT(?, ' 00:00:00') AND CONCAT(?, ' 23:59:59')
    AND (Log_ActivityRefNumber LIKE ? OR Log_ActivityModule LIKE ? OR Log_ActivityStaffName LIKE ?)