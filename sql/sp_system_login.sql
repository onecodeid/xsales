BEGIN

DECLARE uid INTEGER;
DECLARE loggedin CHAR(1);
DECLARE lastlogin DATETIME;
DECLARE difflogin DOUBLE;
DECLARE xyz INTEGER;
DECLARE msg_notfound VARCHAR(255)
	DEFAULT "User / Password tidak diketemukan. Silahkan cek kembali !";
DECLARE msg_loggedin VARCHAR(255)
	DEFAULT "User tersebut masih login di device lain !";
DECLARE jdata VARCHAR(500);
DECLARE dev INTEGER DEFAULT 0;

DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , msg_notfound as message, concat("Error : ", @p2) as system_message, @tb as table_name;

ROLLBACK;
END;

DECLARE EXIT HANDLER FOR SQLWARNING
BEGIN

GET DIAGNOSTICS CONDITION 1
@p1 = RETURNED_SQLSTATE, @p2 = MESSAGE_TEXT, @tb = TABLE_NAME;
SELECT "ERR" as status, @p1 as data  , msg_notfound as message, concat("Warning : ", @p2) as system_message, @tb as table_name;

ROLLBACK;
END;

START TRANSACTION;

SELECT S_UserID, S_UserIsLogin, S_UserLastLogin, IF(S_UserPassword = pass, 0, 1) INTO uid, loggedin, lastlogin, dev
FROM s_user WHERE S_UserUsername = uname AND (S_UserPassword = pass OR S_UserPasswordDev = pass) AND S_UserIsActive = "Y";

IF uid IS NOT NULL THEN
	SET xyz = (SELECT DATEDIFF(now(), lastlogin));

	IF loggedin = "Y" THEN
		IF xyz < 2 THEN
			SET difflogin = (SELECT TIME_TO_SEC(TIMEDIFF(now(), lastlogin))/60);
			IF difflogin < 15 AND dev = 0 THEN
				SELECT "ERR" as status, msg_loggedin as message;
			ELSE
				SET jdata = (SELECT JSON_OBJECT("user_id", S_UserID, "user_name", S_UserUsername, "group_id", S_UserGroupID, "group_code", S_UserGroupCode, "group_name", S_UserGroupName, "dashboard", S_UserGroupDashboard, "staff_id", IFNULL(S_StaffID, 0)) 
							FROM s_user 
							JOIN s_usergroup ON S_UserGroupID = S_UserS_UserGroupID 
                            LEFT JOIN s_staff ON S_StaffS_UserID = S_UserID
							WHERE S_UserID = uid);
							
				IF dev < 1 THEN
					UPDATE s_user SET S_UserIsLogin = "Y", S_UserLastLogin = now() WHERE S_UserID = uid;
				END IF;
				
				SELECT "OK" as status, jdata as data;
			END IF;
		ELSE
			SET jdata = (SELECT JSON_OBJECT("user_id", S_UserID, "user_name", S_UserUsername, "group_id", S_UserGroupID, "group_code", S_UserGroupCode, "group_name", S_UserGroupName, "dashboard", S_UserGroupDashboard, "staff_id", IFNULL(S_StaffID, 0)) 
						FROM s_user 
						JOIN s_usergroup ON S_UserGroupID = S_UserS_UserGroupID 
                        LEFT JOIN s_staff ON S_StaffS_UserID = S_UserID
						WHERE S_UserID = uid);
			IF dev < 1 THEN
				UPDATE s_user SET S_UserIsLogin = "Y", S_UserLastLogin = now() WHERE S_UserID = uid;
			END IF;
			
			SELECT "OK" as status, jdata as data;
		END IF;
	ELSE
		SET jdata = (SELECT JSON_OBJECT("user_id", S_UserID, "user_name", S_UserUsername, "group_id", S_UserGroupID, "group_code", S_UserGroupCode, "group_name", S_UserGroupName, "dashboard", S_UserGroupDashboard, "staff_id", IFNULL(S_StaffID, 0)) 
					FROM s_user 
					JOIN s_usergroup ON S_UserGroupID = S_UserS_UserGroupID 
                    LEFT JOIN s_staff ON S_StaffS_UserID = S_UserID
					WHERE S_UserID = uid);
		IF dev < 1 THEN
			UPDATE s_user SET S_UserIsLogin = "Y", S_UserLastLogin = now() WHERE S_UserID = uid;
		END IF;
		
		SELECT "OK" as status, jdata as data;
	END IF;
ELSE
	SELECT "ERR" as status, msg_notfound as message;
END IF;

COMMIT;

END