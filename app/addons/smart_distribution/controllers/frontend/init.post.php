<?php

//if (AREA == 'C' && empty($auth['user_id']) && !in_array($controller, array('auth', 'profiles', 'sd_exim_1c', 'exim_1c'))) {
if (AREA == 'C' && empty($auth['user_id']) && in_array($controller, array('index'))) {
	fn_redirect('auth.login_form');
}

if (in_array($auth['company_id'], [2186])) Tygh::$app['session']['notifications'] = [];
