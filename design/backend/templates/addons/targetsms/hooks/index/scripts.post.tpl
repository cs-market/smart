<script type="text/javascript">
(function(_, $) {
    _.tr({
        'targetsms.success': '{__("successful")}',
        'targetsms.successSmsSent': '{__("success_sms_sent")}',
        'targetsms.error': '{__("error")}',
        'targetsms.smsWasntSent': '{__("sms_wasnt_sent")}',
    });
    $(document).on("click","#send_sms_custom",function(){       
        var phones = $('#addon_option_targetsms_phone_numbers').val();
        var text = $('#addon_option_targetsms_custom_sms_content').val();
        var senders = $('#addon_option_targetsms_custom_sms_sender').val();
        var adminIndex = $('#alt_host_address').val();
        var url = adminIndex + '?dispatch=addons.send_sms';
        var params = {
            data:{
                "phones": phones,
                "text": text,
                "senders": senders
            },
            callback: function(response){
                if (response.notifications.length == 0){
                    $.ceNotification('show', {
                        type: 'N',
                        title: _.tr('targetsms.success'),
                        message: _.tr('targetsms.successSmsSent')
                    });
                    $('#addon_option_targetsms_phone_numbers').val("");
                    $('#addon_option_targetsms_custom_sms_content').val("");
                    $('#addon_option_targetsms_custom_sms_sender').val(""); 
                }           
            }
        };
        $.ceAjax('request', url, params);
    }); 
}(Tygh, Tygh.$));
</script>