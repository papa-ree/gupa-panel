<?php

use Bale\GupaPanel\GupaPanelPermissions;

/**
 * Menu definisi untuk package bale/gupa-panel (Landlord Layout).
 *
 * Grup 'gupa-panel' mencakup fitur keamanan: blacklist, whitelist,
 * blocked IPs, false positive review, sync management, dan known crawlers.
 */
return [
    'type' => 'landlord',

    'groups' => [
        [
            'key' => 'gupa-panel',
            'label' => 'Gupa Panel',
            'icon' => 'shield',
            'items' => [
                [
                    'id' => 'overview',
                    'label' => 'Overview',
                    'url' => 'gupa-panel/overview',
                    'icon' => 'bar-chart-3',
                    'permission' => GupaPanelPermissions::VIEW_PANEL_OVERVIEW,
                ],
                [
                    'id' => 'blacklist',
                    'label' => 'Blacklist',
                    'url' => 'gupa-panel/blacklist',
                    'icon' => 'list-minus',
                    'permission' => GupaPanelPermissions::VIEW_BLACKLIST,
                ],
                [
                    'id' => 'whitelist',
                    'label' => 'Whitelist',
                    'url' => 'gupa-panel/whitelist',
                    'icon' => 'list-plus',
                    'permission' => GupaPanelPermissions::VIEW_WHITELIST,
                ],
                [
                    'id' => 'blocked-ips',
                    'label' => 'Blocked IPs',
                    'url' => 'gupa-panel/blocked-ips',
                    'icon' => 'shield-x',
                    'permission' => GupaPanelPermissions::VIEW_BLOCKED_IPS,
                ],
                [
                    'id' => 'false-positive',
                    'label' => 'False Positive Review',
                    'url' => 'gupa-panel/review-false-positive',
                    'icon' => 'check-circle',
                    'permission' => GupaPanelPermissions::VIEW_FALSE_POSITIVE,
                ],
                [
                    'id' => 'sync',
                    'label' => 'Sync Management',
                    'url' => 'gupa-panel/sync',
                    'icon' => 'refresh-cw',
                    'permission' => GupaPanelPermissions::VIEW_SYNC,
                ],
                [
                    'id' => 'known-crawler',
                    'label' => 'Known Crawlers',
                    'url' => 'gupa-panel/known-crawlers',
                    'icon' => 'bot',
                    'permission' => GupaPanelPermissions::VIEW_KNOWN_CRAWLER,
                ],
            ],
        ],
    ],
];
