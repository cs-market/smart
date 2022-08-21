<script type="text/javascript">
    (function(_, $) {
        $(document).ready(function () {
            $(_.doc).on('change', '.cm-switcher-button', function (e) {
                state = $(this).prop('checked');
                id = $(this).attr('id');
                qty_id = id.replace('switch_button_', '#qty_count_');
                new_step = state ? $(this).data('caStep') : $(this).data('caItemsInPackage');
                $(qty_id).attr('data-ca-step', new_step).data('caMinQty', new_step);
                input_val = $(qty_id).val();
                new_val = Math.ceil(input_val/new_step)*new_step;
                $(qty_id).val(new_val).trigger('change');
                $.ceEvent('trigger', 'ce.valuechangerincrease', [$(qty_id), new_step, new_step, new_val]);
            });
        });
    }(Tygh, Tygh.$));
</script>
