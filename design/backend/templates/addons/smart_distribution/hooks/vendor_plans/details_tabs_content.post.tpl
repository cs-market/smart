<div id="content_usergroups_{$id}" class="hidden">

    {include file="common/select_usergroups.tpl" id="ug_id" name="plan_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$plan.usergroup_ids input_extra="" list_mode=false}
</div>