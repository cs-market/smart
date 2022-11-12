<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($mode == 'add') {
		foreach (Tygh::$app['session']['notifications'] as $k => $v) {
			if ($v['type'] == 'I') {
				unset(Tygh::$app['session']['notifications'][$k]);
			}
		}
	}

	return;
}
