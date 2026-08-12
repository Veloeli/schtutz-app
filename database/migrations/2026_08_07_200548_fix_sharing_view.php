<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
CREATE OR REPLACE VIEW SHARING_VIEW AS
WITH

-- -----------------------------------------------------
-- Freeze date per team
-- -----------------------------------------------------
team_freeze AS (
    SELECT
        tu.team_id AS team_id,
        LAST_DAY(CURRENT_DATE - INTERVAL MIN(u.freeze_after) + 1 MONTH) AS freeze_date
    FROM users u
    JOIN team_user tu ON u.id = tu.user_id
    GROUP BY tu.team_id
),

-- -----------------------------------------------------
-- Current sharing (paid/shared amounts per category/user)
-- -----------------------------------------------------
current_sharing AS (
    SELECT
        c.team_id AS team_id,
        LAST_DAY(d.posting_date - INTERVAL 1 MONTH) + INTERVAL 1 DAY AS firstday,
        i.category_id AS category_id,
        d.user_id AS user_id,

        SUM(
            CASE
                WHEN COALESCE(d.wizard, '') IN ('T', 't')
                    THEN i.amount
                ELSE 0
            END
        ) AS shared_amount,

        SUM(
            CASE
                WHEN COALESCE(d.wizard, '') NOT IN ('T', 't')
                  AND d.posting_date BETWEEN COALESCE(t.valid_from, '1900-01-01')
                                         AND COALESCE(t.valid_until, '2999-12-31')
                  AND d.posting_date BETWEEN COALESCE(payer.member_from, '1900-01-01')
                                         AND COALESCE(payer.member_to, '2999-12-31')
                    THEN i.amount
                ELSE 0
            END
        ) AS paid_amount

    FROM items i
    JOIN documents d ON i.document_id = d.id
    JOIN categories c ON i.category_id = c.id
    JOIN teams t ON c.team_id = t.id
    JOIN team_user payer ON d.user_id = payer.user_id AND t.id = payer.team_id
    JOIN team_freeze tf ON t.id = tf.team_id

    WHERE d.posting_date > tf.freeze_date

    GROUP BY team_id, firstday, category_id, user_id
    HAVING paid_amount <> 0 OR shared_amount <> 0
),

-- -----------------------------------------------------
-- Valid payments (sum per team/category/month)
-- -----------------------------------------------------
valid_payments AS (
    SELECT
        current_sharing.team_id,
        current_sharing.firstday,
        current_sharing.category_id,
        SUM(current_sharing.paid_amount) AS valid_total
    FROM current_sharing
    GROUP BY team_id, firstday, category_id
),

-- -----------------------------------------------------
-- Distinct months
-- -----------------------------------------------------
months AS (
    SELECT DISTINCT
        valid_payments.firstday
    FROM valid_payments
),

-- -----------------------------------------------------
-- User share ratios per month
-- -----------------------------------------------------
usershare AS (
    SELECT
        months.firstday,
        tu.team_id,
        tu.user_id,
        tu.sharing_ratio,
        tu.clearing_account
    FROM team_user tu
    LEFT JOIN months ON 1 = 1
    WHERE months.firstday BETWEEN COALESCE(tu.member_from, '1900-01-01')
                              AND COALESCE(tu.member_to, '2999-12-31')
),

-- -----------------------------------------------------
-- Sum of sharing ratios per team/month
-- -----------------------------------------------------
denominator AS (
    SELECT
        usershare.team_id,
        usershare.firstday,
        SUM(usershare.sharing_ratio) AS sharing_total
    FROM usershare
    GROUP BY usershare.team_id, usershare.firstday
),

-- -----------------------------------------------------
-- Combine everything
-- -----------------------------------------------------
combined AS (
    SELECT
        us.team_id,
        us.firstday,
        vp.category_id,
        us.user_id,

        COALESCE(vp.valid_total, 0) AS valid_total,
        COALESCE(cs.paid_amount, 0) AS paid_amount,
        COALESCE(cs.shared_amount, 0) AS shared_amount,
        COALESCE(us.sharing_ratio, 0) AS sharing_ratio,
        dn.sharing_total,

        COALESCE(vp.valid_total * us.sharing_ratio / dn.sharing_total, 0) AS due_amount,
        COALESCE(vp.valid_total * us.sharing_ratio / dn.sharing_total, 0) - COALESCE(cs.paid_amount, 0) AS target_sharing,
        COALESCE(vp.valid_total * us.sharing_ratio / dn.sharing_total, 0)
            - COALESCE(cs.paid_amount, 0)
            - COALESCE(cs.shared_amount, 0) AS gap

    FROM usershare us
    LEFT JOIN valid_payments vp
        ON us.team_id = vp.team_id
       AND us.firstday = vp.firstday
    LEFT JOIN current_sharing cs
        ON vp.team_id = cs.team_id
       AND vp.firstday = cs.firstday
       AND vp.category_id = cs.category_id
       AND us.user_id = cs.user_id
    LEFT JOIN denominator dn
        ON dn.team_id = us.team_id
       AND dn.firstday = us.firstday
)

-- -----------------------------------------------------
-- Final output
-- -----------------------------------------------------
SELECT
    combined.team_id,
    combined.firstday,
    combined.category_id,
    combined.user_id,
    combined.valid_total,
    combined.paid_amount,
    combined.shared_amount,
    combined.sharing_ratio,
    combined.sharing_total,
    combined.due_amount,
    combined.target_sharing,
    combined.gap
FROM combined
ORDER BY team_id, firstday, category_id, user_id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // leave new version, only cosmetic changes
    }
};
