MERGE mydb.dbo.idd_payroll AS target
USING (
    SELECT 
        c.cont_scheme_code AS scheme_code,
        c.cont_member_number AS member_number,
        m.m_name AS member_name,
        SUM(CASE WHEN c.cont_document = 'withdrawals' 
                 AND YEAR(c.cont_display_date) = 2024 
                 THEN c.cont_amount ELSE 0 END) AS annual_withdrawal,
        SUM(c.cont_amount) AS closing_balance
    FROM MYDB.DBO.CONTRIBUTIONS_TB c
    INNER JOIN mydb.dbo.members_tb m
        ON m.m_scheme_code = c.cont_scheme_code 
       AND m.m_number = c.cont_member_number
    WHERE c.cont_scheme_code = 'KE454'
      AND c.cont_display_date BETWEEN '2016-01-01' AND '2025-01-01'
    GROUP BY c.cont_scheme_code, c.cont_member_number, m.m_name
) AS src
ON target.scheme_code = src.scheme_code 
   AND target.member_number = src.member_number

WHEN MATCHED THEN
    UPDATE SET
        target.member_name = src.member_name,
        target.annual_withdrawal = ROUND(src.annual_withdrawal, 2),
        target.closing_balance = ROUND(src.closing_balance, 2),
        target.drawdown_percentage = ROUND(
            CASE WHEN src.closing_balance <> 0 
                 THEN (src.annual_withdrawal / src.closing_balance) * 100 
                 ELSE 0 END, 2),
        target.absolute_drawdown_percentage = ROUND(
            ABS(CASE WHEN src.closing_balance <> 0 
                     THEN (src.annual_withdrawal / src.closing_balance) * 100 
                     ELSE 0 END), 2),
        target.gross_after_deductions = ABS(ROUND((src.annual_withdrawal / 12), 2)),
        target.updated_at = GETDATE()

WHEN NOT MATCHED THEN
    INSERT (scheme_code, member_number, member_name, annual_withdrawal, closing_balance, drawdown_percentage, absolute_drawdown_percentage, gross_after_deductions, created_at)
    VALUES (
        src.scheme_code,
        src.member_number,
        src.member_name,
        ROUND(src.annual_withdrawal, 2),
        ROUND(src.closing_balance, 2),
        ROUND(CASE WHEN src.closing_balance <> 0 
                   THEN (src.annual_withdrawal / src.closing_balance) * 100 
                   ELSE 0 END, 2),
        ROUND(ABS(CASE WHEN src.closing_balance <> 0 
                       THEN (src.annual_withdrawal / src.closing_balance) * 100 
                       ELSE 0 END), 2),
        ROUND((src.annual_withdrawal / 12), 2),
        GETDATE()
    );