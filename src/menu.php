<?php

use Bale\GupaPanel\GupaPanelPermissions;

return [
    [
        'id' => 'overview',
        'group' => 'gupa-panel',
        'label' => 'Overview',
        'url' => 'gupa-panel/overview',
        'icon' => 'bar-chart-3',
        'permission' => GupaPanelPermissions::VIEW_PANEL_OVERVIEW,
        'table' => null,
    ],
    [
        'id' => 'blacklist',
        'group' => 'gupa-panel',
        'label' => 'Blacklist',
        'url' => 'gupa-panel/blacklist',
        'icon' => 'list-minus',
        'permission' => GupaPanelPermissions::VIEW_BLACKLIST,
        'table' => 'gupa_panel_blacklists',
    ],
    [
        'id' => 'whitelist',
        'group' => 'gupa-panel',
        'label' => 'Whitelist',
        'url' => 'gupa-panel/whitelist',
        'icon' => 'list-plus',
        'permission' => GupaPanelPermissions::VIEW_WHITELIST,
        'table' => 'gupa_panel_whitelists',
    ],
    [
        'id' => 'blocked-ips',
        'group' => 'gupa-panel',
        'label' => 'Blocked IPs',
        'url' => 'gupa-panel/blocked-ips',
        'icon' => 'block',
        'permission' => GupaPanelPermissions::VIEW_BLOCKED_IPS,
        'table' => 'gupa_panel_blocked_ips',
    ],
    [
        'id' => 'false-positive',
        'group' => 'gupa-panel',
        'label' => 'False Positive Review',
        'url' => 'gupa-panel/review-false-positive',
        'icon' => 'check-circle',
        'permission' => GupaPanelPermissions::VIEW_FALSE_POSITIVE,
        'table' => null,
    ],
    [
        'id' => 'sync',
        'group' => 'gupa-panel',
        'label' => 'Sync Management',
        'url' => 'gupa-panel/sync',
        'icon' => 'refresh-cw',
        'permission' => GupaPanelPermissions::VIEW_SYNC,
        'table' => null,
    ],
];
