MERGE mydb.dbo.idd_payroll AS target
USING (
    SELECT 
        t.scheme_code,
        t.member_number,
        t.member_name,
        t.annual_withdrawal_of_prev_yr,
        t.closing_balance,

        -- Drawdown percentage (without ABS)
        ROUND(
            CASE 
                WHEN t.closing_balance <> 0 
                THEN (t.annual_withdrawal_of_prev_yr / t.closing_balance) * 100
                ELSE 0
            END, 
        2) AS drawdown_percentage,

        -- Absolute drawdown percentage (always positive)
        ROUND(
            CASE 
                WHEN t.closing_balance <> 0 
                THEN ABS(t.annual_withdrawal_of_prev_yr / t.closing_balance) * 100
                ELSE 0
            END, 
        2) AS absolute_drawdown_percentage,

        -- Amended annual payroll (capped at 15% of balance)
        CASE 
            WHEN 
                (CASE 
                    WHEN t.closing_balance <> 0 
                    THEN ABS(t.annual_withdrawal_of_prev_yr / t.closing_balance) * 100
                    ELSE 0
                END) < 15 
            THEN ABS(t.annual_withdrawal_of_prev_yr)
            ELSE t.closing_balance * 0.15
        END AS annual_payroll_of_prev_yr,

        -- Monthly payroll (amended / 12)
        ROUND(
            CASE 
                WHEN 
                    (CASE 
                        WHEN t.closing_balance <> 0 
                        THEN ABS(t.annual_withdrawal_of_prev_yr / t.closing_balance) * 100
                        ELSE 0
                    END) < 15 
                THEN ABS(t.annual_withdrawal_of_prev_yr) / 12
                ELSE (t.closing_balance * 0.15) / 12
            END, 
        2) AS gross_after_deductions
    FROM (
        SELECT 
            c.cont_scheme_code AS scheme_code,
            c.cont_member_number AS member_number,
            m.m_name AS member_name,
            SUM(CASE 
                    WHEN c.cont_document = 'withdrawals' 
                         AND YEAR(c.cont_display_date) = 2024 
                    THEN c.cont_amount 
                    ELSE 0 
                END) AS annual_withdrawal_of_prev_yr,
            SUM(c.cont_amount) AS closing_balance
        FROM MYDB.DBO.CONTRIBUTIONS_TB c
        INNER JOIN MYDB.DBO.MEMBERS_TB m
            ON m.m_scheme_code = c.cont_scheme_code 
           AND m.m_number = c.cont_member_number
        WHERE c.cont_scheme_code = 'KE454'
          AND c.cont_display_date BETWEEN '2016-01-01' AND '2025-01-01'
        GROUP BY 
            c.cont_scheme_code,
            c.cont_member_number,
            m.m_name
    ) AS t
) AS src
ON target.scheme_code = src.scheme_code AND target.member_number = src.member_number

-- If record exists, update it
WHEN MATCHED THEN
    UPDATE SET
        target.member_name = src.member_name,
        target.annual_withdrawal = src.annual_withdrawal_of_prev_yr,
        target.closing_balance = src.closing_balance,
        target.drawdown_percentage = src.drawdown_percentage,
        target.absolute_drawdown_percentage = src.absolute_drawdown_percentage,
        target.annual_payroll_of_prev_yr = src.annual_payroll_of_prev_yr,
        target.gross_after_deductions = src.gross_after_deductions,
        target.updated_at = GETDATE()

-- If not exists, insert a new record
WHEN NOT MATCHED THEN
    INSERT (
        scheme_code,
        member_number,
        member_name,
        annual_withdrawal,
        closing_balance,
        drawdown_percentage,
        absolute_drawdown_percentage,
        annual_payroll_of_prev_yr,
        gross_after_deductions,
        created_at,
        payroll_year
    )
    VALUES (
        src.scheme_code,
        src.member_number,
        src.member_name,
        src.annual_withdrawal_of_prev_yr,
        src.closing_balance,
        src.drawdown_percentage,
        src.absolute_drawdown_percentage,
        src.annual_payroll_of_prev_yr,
        src.gross_after_deductions,
        GETDATE(),
        2024
    );
