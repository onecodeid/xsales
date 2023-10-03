SELECT 
    M_AccountID as account_id, M_AccountCode as account_code, M_AccountName as account_name, M_AccountType account_type,
    M_AccountGroupID as group_id, M_AccountGroupName as group_name, M_AccountPos account_pos,
    (balance_debit + trans_debit) balance_debit, (balance_credit + trans_credit) balance_credit, trans_debit, trans_credit,
    SUM(IFNULL(T_JournalDetailDebit, 0)) total_debit, SUM(IFNULL(T_JournalDetailCredit, 0)) total_credit,
    CONCAT('[', GROUP_CONCAT(
        JSON_OBJECT(
            'journal_id', T_JournalID,
            'journal_type', T_JournalType, 'journal_type_name', IFNULL(M_JournalTypeName, ''), 'journal_date', T_JournalDate, 'journal_number', T_journalNumber,
            'journal_receipt', T_JournalReceipt, 'journal_note', T_JournalNote, 'journal_ref_id', T_JournalRefID, 'journal_ref_note', IFNULL(T_JournalRefNote, ''),
            'detail_id', T_JournalDetailID, 'ledger_note', T_JournalDetailLedgerNote, 'journal_tags', IFNULL(T_JournalTags, '[]'),
            'journal_debit', T_JournalDetailDebit, 'journal_credit', T_JournalDetailCredit,
            'account_code', M_AccountCode, 'account_name', M_AccountName, 'account_type', M_AccountType, 
            'group_name', M_AccountGroupName, 'group_code',  M_AccountGroupCode,
            'journal_type_name', M_JournalTypeName
        )
        ORDER BY M_AccountCode, T_JournalDate, T_JournalID
    ), ']') as details

    FROM t_journaldetail
    JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate BETWEEN ? AND ? AND (T_JournalIsActive = 'Y' Or T_JournalIsActive = 'A') 
    AND T_JournalType <> 'J.93'
    JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID

    LEFT JOIN (
        SELECT IFNULL(SUM(IF(T_JournalType = 'J.30', T_JournalDetailDebit, 0)), 0) balance_debit, IFNULL(SUM(IF(T_JournalType = 'J.30', T_JournalDetailCredit, 0)), 0) balance_credit, 
            IFNULL(SUM(IF(T_JournalType <> 'J.30' AND T_JournalDate <> ?, T_JournalDetailDebit, 0)), 0) trans_debit, 
            IFNULL(SUM(IF(T_JournalType <> 'J.30' AND T_JournalDate <> ?, T_JournalDetailCredit, 0)), 0) trans_credit,
            M_AccountID account_id, M_AccountCode account_code, M_AccountName account_name, M_AccountType account_type
        FROM t_journaldetail
        JOIN t_journal ON T_JournalDetailT_JournalID = T_JournalID AND T_JournalDate >= CONCAT(?, '-01-01') AND T_JournalDate <= ? AND (T_JournalIsActive = 'Y' OR T_JournalIsActive = 'A')
        JOIN m_account ON T_JournalDetailM_AccountID = M_AccountID
        WHERE (T_JournalDetailIsActive = 'Y' OR T_JournalDetailIsActive = 'A')
        GROUP BY T_JournalDetailM_AccountID
    ) blc  ON account_id = M_AccountID

    LEFT JOIN m_accountgroup ON M_AccountM_AccountGroupID = M_AccountGroupID
    LEFT JOIN m_journaltype ON T_JournalType = M_JournalTypeCode AND M_JournalTypeIsActive = 'Y'
    WHERE (T_JournalDetailIsActive = 'Y' OR T_JournalDetailIsActive = 'A')
    AND ((T_JournalDetailM_AccountID = ? AND ? <> 0) OR ? = 0)
    AND ((FIND_IN_SET(T_JournalDetailM_AccountID, ?) AND ? <> '') OR ? = '')
    and (T_JournalDetailLedgerNote LIKE ? OR T_JournalNumber LIKE ?)
    GROUP BY M_AccountID
    ORDER BY M_AccountCode, T_JournalDate ASC, T_JournalNumber ASC