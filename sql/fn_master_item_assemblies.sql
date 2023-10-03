BEGIN

DECLARE assemblies TEXT DEFAULT "[]";
DECLARE isassembly CHAR(1);

SET isassembly = (SELECT M_ItemIsAssembly FROM m_item WHERE M_ItemID = itemid);

IF isassembly = "Y"
    SET assemblies = (
        SELECT CONCAT("[", GROUP_CONCAT(
            JSON_OBJECT("item", JSON_OBJECT("item_id", M_ItemID, "item_name", M_ItemName), "hpp", M_ItemDefaultHPP, "qty", M_ItemAssemblyDetailQty)
        ), "]")
        FROM m_itemassembly
        JOIN m_item ON M_ItemAssemblyDetailM_ItemID = M_ItemID
        WHERE M_ItemAssemblyM_ItemID = itemid
        AND M_ItemAssemblyIsACtive = "Y"
        ORDER BY M_ItemName;
    );
END IF;

RETURN assemblies;

END