<?php

function get_ix_from_switch_name(string $switch_name): string
{
    return match (preg_replace('/pe\./', '', preg_replace('/\d/', '', $switch_name))) {
        'syd' => 'NSW-IX',
        'per' => 'WA-IX',
        'bne' => 'QLD-IX',
        'cbr' => 'ACT-IX',
        'adl' => 'SA-IX',
        'mel' => 'VIC-IX',


        'akl' => 'AKL-IX',
        'chc' => 'CHC-IX',
        'wlg' => 'WLG-IX',
    };
}
