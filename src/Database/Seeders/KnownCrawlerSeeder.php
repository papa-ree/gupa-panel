<?php

namespace Bale\GupaPanel\Database\Seeders;

use Bale\GupaPanel\Models\KnownCrawler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class KnownCrawlerSeeder extends Seeder
{
    public function run(): void
    {
        $basePath = __DIR__.'/../../../resources/ips';

        $jsonCrawlers = [
            [
                'file' => 'goog.json',
                'name' => 'Google (General)',
                'provider' => 'google',
                'user_agent_pattern' => 'Googlebot',
            ],
            [
                'file' => 'google-common-crawlers.json',
                'name' => 'Google Common Crawlers',
                'provider' => 'google',
                'user_agent_pattern' => 'Googlebot',
            ],
            [
                'file' => 'google-special-crawlers.json',
                'name' => 'Google Special Crawlers',
                'provider' => 'google',
                'user_agent_pattern' => 'Googlebot',
            ],
            [
                'file' => 'google-user-triggered-agents.json',
                'name' => 'Google User-Triggered Agents',
                'provider' => 'google',
                'user_agent_pattern' => 'Googlebot',
            ],
            [
                'file' => 'google-user-triggered-fetchers.json',
                'name' => 'Google User-Triggered Fetchers',
                'provider' => 'google',
                'user_agent_pattern' => 'Googlebot',
            ],
        ];

        foreach ($jsonCrawlers as $crawlerConfig) {
            $filePath = $basePath.'/'.$crawlerConfig['file'];

            if (! File::exists($filePath)) {
                $this->command?->warn("File not found: {$filePath}");

                continue;
            }

            $json = File::get($filePath);
            $data = json_decode($json, true);

            if (! isset($data['prefixes'])) {
                $this->command?->warn("No prefixes found in: {$crawlerConfig['file']}");

                continue;
            }

            $ipv4Ranges = [];
            $ipv6Ranges = [];

            foreach ($data['prefixes'] as $prefix) {
                if (isset($prefix['ipv4Prefix'])) {
                    $ipv4Ranges[] = $prefix['ipv4Prefix'];
                } elseif (isset($prefix['ipv6Prefix'])) {
                    $ipv6Ranges[] = $prefix['ipv6Prefix'];
                }
            }

            $verifiedIpRanges = array_merge($ipv4Ranges, $ipv6Ranges);

            KnownCrawler::updateOrCreate(
                ['name' => $crawlerConfig['name']],
                [
                    'provider' => $crawlerConfig['provider'],
                    'user_agent_pattern' => $crawlerConfig['user_agent_pattern'],
                    'verified_ip_ranges' => $verifiedIpRanges,
                    'is_active' => true,
                ]
            );

            $this->command?->info("Seeded: {$crawlerConfig['name']} - IPv4: ".count($ipv4Ranges).', IPv6: '.count($ipv6Ranges));
        }

        $this->seedOtherCrawlers();
    }

    protected function seedOtherCrawlers(): void
    {
        $crawlers = [
            ['name' => 'Googlebot', 'provider' => 'google', 'user_agent_pattern' => 'Googlebot', 'verified_ip_ranges' => null],
            ['name' => 'Googlebot Image', 'provider' => 'google', 'user_agent_pattern' => 'Googlebot-Image', 'verified_ip_ranges' => null],
            ['name' => 'Googlebot News', 'provider' => 'google', 'user_agent_pattern' => 'Googlebot-News', 'verified_ip_ranges' => null],
            ['name' => 'Googlebot Video', 'provider' => 'google', 'user_agent_pattern' => 'Googlebot-Video', 'verified_ip_ranges' => null],
            ['name' => 'Google Mobile Ads', 'provider' => 'google', 'user_agent_pattern' => 'AdsBot-Google', 'verified_ip_ranges' => null],
            ['name' => 'Google Mobile Ads Mobile', 'provider' => 'google', 'user_agent_pattern' => 'AdsBot-Google-Mobile', 'verified_ip_ranges' => null],
            ['name' => 'Google PageSpeed', 'provider' => 'google', 'user_agent_pattern' => 'Google Page Speed', 'verified_ip_ranges' => null],
            ['name' => 'Google Web Light', 'provider' => 'google', 'user_agent_pattern' => 'googleweblight', 'verified_ip_ranges' => null],
            ['name' => 'Google Favicon', 'provider' => 'google', 'user_agent_pattern' => 'Google-Favicon', 'verified_ip_ranges' => null],
            ['name' => 'Google Site Verifier', 'provider' => 'google', 'user_agent_pattern' => 'Google-Site-Verification', 'verified_ip_ranges' => null],
            ['name' => 'Google Structured Data', 'provider' => 'google', 'user_agent_pattern' => 'Google-Structured-Data-Testing-Tool', 'verified_ip_ranges' => null],
            ['name' => 'Facebook External Hit', 'provider' => 'meta', 'user_agent_pattern' => 'facebookexternalhit', 'verified_ip_ranges' => ['69.171.224.0/19', '69.171.248.0/21', '69.63.176.0/20', '31.13.24.0/21', '31.13.64.0/18', '157.240.0.0/16', '204.15.20.0/22', '179.60.192.0/22']],
            ['name' => 'Facebook Platform', 'provider' => 'meta', 'user_agent_pattern' => 'facebookplatform', 'verified_ip_ranges' => null],
            ['name' => 'Meta External Hit', 'provider' => 'meta', 'user_agent_pattern' => 'meta-externalagent', 'verified_ip_ranges' => null],
            ['name' => 'Twitterbot', 'provider' => 'twitter', 'user_agent_pattern' => 'Twitterbot', 'verified_ip_ranges' => ['199.16.156.0/22', '199.59.148.0/22', '199.96.56.0/21', '202.160.128.0/22']],
            ['name' => 'Bingbot', 'provider' => 'bing', 'user_agent_pattern' => 'bingbot', 'verified_ip_ranges' => ['40.77.0.0/16', '65.55.0.0/16', '131.253.0.0/16', '157.55.0.0/16', '207.46.0.0/16']],
            ['name' => 'Bing Preview', 'provider' => 'bing', 'user_agent_pattern' => 'BingPreview', 'verified_ip_ranges' => null],
            ['name' => 'MSN Bot', 'provider' => 'bing', 'user_agent_pattern' => 'msnbot', 'verified_ip_ranges' => null],
            ['name' => 'Microsoft Ad Preview', 'provider' => 'bing', 'user_agent_pattern' => 'Mozilla/5.0 (Windows NT) AppleWebKit.* (KHTML, like Gecko) Chrome.* Safari.* (?:AdPreview|adpreview)', 'verified_ip_ranges' => null],
            ['name' => 'Yahoo Slurp', 'provider' => 'yahoo', 'user_agent_pattern' => 'Slurp', 'verified_ip_ranges' => null],
            ['name' => 'Yandex Bot', 'provider' => 'yandex', 'user_agent_pattern' => 'YandexBot', 'verified_ip_ranges' => null],
            ['name' => 'Yandex Images', 'provider' => 'yandex', 'user_agent_pattern' => 'YandexImages', 'verified_ip_ranges' => null],
            ['name' => 'Baidu Spider', 'provider' => 'baidu', 'user_agent_pattern' => 'Baiduspider', 'verified_ip_ranges' => null],
            ['name' => 'DuckDuckBot', 'provider' => 'duckduckgo', 'user_agent_pattern' => 'DuckDuckBot', 'verified_ip_ranges' => null],
            ['name' => 'Sogou Spider', 'provider' => 'sogou', 'user_agent_pattern' => 'Sogou', 'verified_ip_ranges' => null],
            ['name' => 'Naver Bot', 'provider' => 'naver', 'user_agent_pattern' => 'NaverBot', 'verified_ip_ranges' => null],
            ['name' => 'LinkedIn Bot', 'provider' => 'linkedin', 'user_agent_pattern' => 'LinkedInBot', 'verified_ip_ranges' => null],
            ['name' => 'Slack Link Preview', 'provider' => 'slack', 'user_agent_pattern' => 'Slack-ImgProxy', 'verified_ip_ranges' => null],
            ['name' => 'Slack Bot', 'provider' => 'slack', 'user_agent_pattern' => 'Slackbot-LinkExpanding', 'verified_ip_ranges' => null],
            ['name' => 'Telegram Bot', 'provider' => 'telegram', 'user_agent_pattern' => 'TelegramBot', 'verified_ip_ranges' => null],
            ['name' => 'Discord Bot', 'provider' => 'discord', 'user_agent_pattern' => 'Discordbot', 'verified_ip_ranges' => null],
            ['name' => 'WhatsApp Preview', 'provider' => 'whatsapp', 'user_agent_pattern' => 'WhatsApp', 'verified_ip_ranges' => null],
            ['name' => 'Pinterest Bot', 'provider' => 'pinterest', 'user_agent_pattern' => 'Pinterest', 'verified_ip_ranges' => null],
            ['name' => 'Apple Bot', 'provider' => 'apple', 'user_agent_pattern' => 'Applebot', 'verified_ip_ranges' => null],
            ['name' => 'Ahrefs Bot', 'provider' => 'other', 'user_agent_pattern' => 'AhrefsBot', 'verified_ip_ranges' => null],
            ['name' => 'Semrush Bot', 'provider' => 'other', 'user_agent_pattern' => 'SemrushBot', 'verified_ip_ranges' => null],
            ['name' => 'Moz Bot', 'provider' => 'other', 'user_agent_pattern' => 'rogerbot', 'verified_ip_ranges' => null],
            ['name' => 'Majestic Bot', 'provider' => 'other', 'user_agent_pattern' => 'MJ12bot', 'verified_ip_ranges' => null],
            ['name' => 'Screaming Frog', 'provider' => 'other', 'user_agent_pattern' => 'Screaming Frog SEO Spider', 'verified_ip_ranges' => null],
        ];

        foreach ($crawlers as $crawler) {
            KnownCrawler::updateOrCreate(
                ['user_agent_pattern' => $crawler['user_agent_pattern']],
                [
                    'name' => $crawler['name'],
                    'provider' => $crawler['provider'],
                    'verified_ip_ranges' => $crawler['verified_ip_ranges'],
                    'is_active' => true,
                ]
            );
            $this->command?->info("Seeded: {$crawler['name']}");
        }
    }
}
