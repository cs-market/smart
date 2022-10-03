<?php

function fn_import_user_extra_data($user_id, $extra_data) {
    if ($user_id && !empty($extra_data)) {
        $extra_data = json_decode($extra_data, true);
        fn_save_user_additional_data(USER_EXTRA_DATA, $extra_data, $user_id);
    }
}
