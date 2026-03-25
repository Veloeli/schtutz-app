<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
create or replace view sharing_view as
with
team_freeze as (
select
    tu.team_id, last_day(current_date - interval min(u.freeze_after)+1 month) freeze_date
from
    users u
    join team_user tu on u.id = tu.user_id
group by
    tu.team_id
),

current_sharing as (
select
	c.team_id, last_day(d.posting_date - interval 1 month) + interval 1 day firstday, i.category_id, d.owner_id user_id, 
    sum(case when coalesce(d.wizard,'') in ('T','t') then i.amount else 0 end) shared_amount,
    sum(case when coalesce(d.wizard,'') not in ('T','t')
            and d.posting_date between coalesce(t.valid_from, '1900-01-01') and coalesce(t.valid_until, '2999-12-31')
            and d.posting_date between coalesce(payer.member_from,'1900-01-01') and coalesce(payer.member_to, '2999-12-31') 
        then i.amount else 0 end) paid_amount
from
	items i
    join documents d on i.document_id = d.id
    join categories c on i.category_id = c.id
    join teams t on c.team_id = t.id
    join team_user payer on d.owner_id = payer.user_id and t.id = payer.team_id
    join team_freeze tf on t.id = tf.team_id
where
    d.posting_date > tf.freeze_date
group by
	1,2,3,4
having 
    paid_amount != 0 or shared_amount != 0
),

valid_payments as (
select
    team_id, firstday, category_id,
    sum(paid_amount) valid_total
from
    current_sharing
group by
    1,2,3
),

months as (
    select distinct firstday from valid_payments
),

usershare as (
select
    months.firstday, tu.team_id, tu.user_id, tu.sharing_ratio, tu.clearing_account
from
    team_user tu
    left join months on 1 = 1
where
	months.firstday between coalesce(tu.member_from,'1900-01-01') and coalesce(tu.member_to,'2999-12-31')
),
    
numerator as (
select
    team_id, firstday, sum(sharing_ratio) sharing_total
from
    usershare
group by
    team_id, firstday
),

combined as (
select
    us.team_id, us.firstday, vp.category_id, us.user_id, 
    coalesce(vp.valid_total, 0) valid_total, 
    coalesce(cs.paid_amount, 0) paid_amount,
    coalesce(cs.shared_amount, 0) shared_amount, 
    coalesce(us.sharing_ratio, 0) sharing_ratio, 
    sharing_total,
    coalesce(vp.valid_total / nu.sharing_total * us.sharing_ratio, 0) due_amount,
    coalesce(vp.valid_total / nu.sharing_total * us.sharing_ratio, 0) - coalesce(cs.paid_amount, 0) target_sharing,
    coalesce(vp.valid_total / nu.sharing_total * us.sharing_ratio, 0) - coalesce(cs.paid_amount, 0) - coalesce(cs.shared_amount, 0) gap
from
    usershare us
    left join valid_payments vp  on us.team_id = vp.team_id and us.firstday = vp.firstday
    left join current_sharing cs on vp.team_id = cs.team_id and vp.firstday = cs.firstday and vp.category_id = cs.category_id and us.user_id = cs.user_id
    left join numerator nu       on nu.team_id = us.team_id and nu.firstday = us.firstday
)

select * from combined
order by 1,2,3,4
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS sharing_view");
    }
};
