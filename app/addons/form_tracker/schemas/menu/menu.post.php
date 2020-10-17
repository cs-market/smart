<?php
    $schema['central']['marketing']['items']['new_sent_forms'] = array(
        'attrs' => array(
            'class'=>'is-addon'
        ),
        'href' => 'sent_forms.manage?status=N',
        'position' => 900
    );

    $schema['central']['marketing']['items']['sent_forms'] = array(
        'attrs' => array(
            'class'=>'is-addon'
        ),
        'href' => 'sent_forms.manage',
        'position' => 910
    );
    $schema['top']['administration']['items']['form_tracker_statuses'] = array(
        'attrs' => array(
            'class'=>'is-addon'
        ),
        'href' => 'statuses.manage?type=' . STATUSES_FORM_TRACKER,
        'position' => 408
    );
return $schema;