{if $user_data.user_type == "UserTypes::CUSTOMER"|enum}
    <div class="control-group">
        <label class="control-label" for="approve_return_action">{__('returns.approve_returns')}:</label>
        <div class="controls">
            <input type="hidden" name="user_data[approve_returns]" value="{"YesNo::NO"|enum}">
            <input type="checkbox" name="user_data[approve_returns]" value="{"YesNo::YES"|enum}" {if $user_data.approve_returns == "YesNo::YES"|enum}checked="_checked"{/if}>
        </div>
    </div>
{/if}
