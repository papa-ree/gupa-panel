<?php

namespace Bale\GupaPanel;

class GupaPanelPermissions
{
    public const VIEW_PANEL_OVERVIEW = 'gupa-panel.overview';
    public const VIEW_BLACKLIST = 'gupa-panel.blacklist.read';
    public const VIEW_WHITELIST = 'gupa-panel.whitelist.read';
    public const VIEW_BLOCKED_IPS = 'gupa-panel.blocked-ip.read';
    public const VIEW_FALSE_POSITIVE = 'gupa-panel.false-positive.review';
    public const VIEW_SYNC = 'gupa-panel.sync.manual';
    public const VIEW_KNOWN_CRAWLER = 'gupa-panel.known-crawler.read';
}
