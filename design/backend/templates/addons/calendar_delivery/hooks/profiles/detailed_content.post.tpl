{include file="common/subheader.tpl" title=__("delivery_date")}
<div class="control-group">
    <label class="control-label" for="debt">{__("debt")}</label>
    <div class="controls">
        {$calendar_days = [
            '1' => {__("weekday_1")},
            '2' => {__("weekday_2")},
            '3' => {__("weekday_3")},
            '4' => {__("weekday_4")},
            '5' => {__("weekday_5")},
            '6' => {__("weekday_6")},
            '0' => {__("weekday_0")}
        ]}
        {html_checkboxes name='user_data[delivery_date]' options=$calendar_days columns=7 selected=$user_data.delivery_date}
    </div>
</div>
