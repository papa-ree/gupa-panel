<?php

namespace Bale\GupaPanel\Services;

use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\KnownCrawler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class FalsePositiveDetector
{
    protected Collection $knownCrawlers;

    protected int $threshold;

    public function __construct()
    {
        $this->knownCrawlers = KnownCrawler::active()->get();
        $this->threshold = (int) config('gupa-panel.false_positive.threshold', 50);
    }

    public function analyzeRequest(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';
        $ip = $request->ip();
        $score = 0;
        $reasons = [];

        $matchedCrawler = $this->matchKnownCrawler($userAgent);

        if ($matchedCrawler) {
            $reasons[] = 'Known crawler: '.$matchedCrawler->name.' ('.$matchedCrawler->provider.')';

            if ($this->ipMatchesVerifiedRange($ip, $matchedCrawler)) {
                $score += 100;
                $reasons[] = 'IP matches verified range for '.$matchedCrawler->name;
            } else {
                $score += 60;
                $reasons[] = $matchedCrawler->name.' user agent without verified IP range';
            }
        } else {
            $botLike = $this->checkBotLikeSignals($userAgent, $request);
            $score += $botLike['score'];
            $reasons = array_merge($reasons, $botLike['reasons']);
        }

        $isFalsePositive = $score >= $this->threshold;

        if ($matchedCrawler && ! $isFalsePositive) {
            $isFalsePositive = true;
            $reasons[] = 'Matched known crawler with low risk score';
        }

        return [
            'is_false_positive' => $isFalsePositive,
            'confidence_score' => min($score, 100),
            'reasons' => $reasons,
            'matched_crawler' => $matchedCrawler?->name,
            'provider' => $matchedCrawler?->provider,
        ];
    }

    public function analyzeBlockedIp(PanelBlockedIp $blockedIp): array
    {
        $log = PanelRequestLog::where('ip', $blockedIp->ip)
            ->latest()
            ->first();

        if (! $log) {
            return [
                'is_false_positive' => false,
                'confidence_score' => 0,
                'reasons' => ['No request logs found for this IP'],
                'matched_crawler' => null,
                'provider' => null,
            ];
        }

        $metadata = is_array($log->metadata) ? $log->metadata : [];
        $userAgent = $metadata['user_agent'] ?? '';
        $request = new Request;
        $request->headers->set('User-Agent', $userAgent);
        $request->server->set('REMOTE_ADDR', $blockedIp->ip);

        foreach ($metadata as $key => $value) {
            if (str_starts_with((string) $key, 'header_')) {
                $headerName = substr((string) $key, 7);
                $request->headers->set($headerName, $value);
            }
        }

        return $this->analyzeRequest($request);
    }

    public function getFalsePositives(int $limit = 50): array
    {
        $recentBlocks = PanelBlockedIp::latest()->limit($limit * 2)->get();
        $results = [];

        foreach ($recentBlocks as $blockedIp) {
            $analysis = $this->analyzeBlockedIp($blockedIp);

            if ($analysis['is_false_positive']) {
                $results[] = [
                    'blocked_ip' => $blockedIp,
                    'analysis' => $analysis,
                ];
            }

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    protected function matchKnownCrawler(string $userAgent): ?KnownCrawler
    {
        foreach ($this->knownCrawlers as $crawler) {
            if (stripos($userAgent, $crawler->user_agent_pattern) !== false) {
                return $crawler;
            }
        }

        return null;
    }

    protected function ipMatchesVerifiedRange(string $ip, KnownCrawler $crawler): bool
    {
        $ranges = $crawler->verified_ip_ranges;

        if (empty($ranges) || ! is_array($ranges)) {
            return false;
        }

        foreach ($ranges as $cidr) {
            if ($this->cidrMatch($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    protected function cidrMatch(string $ip, string $cidr): bool
    {
        $parts = explode('/', $cidr);

        if (count($parts) !== 2) {
            return $ip === $cidr;
        }

        $rangeIp = $parts[0];
        $prefix = (int) $parts[1];

        $ipLong = ip2long($ip);
        $rangeLong = ip2long($rangeIp);

        if ($ipLong === false || $rangeLong === false) {
            return false;
        }

        $mask = -1 << (32 - $prefix);

        return ($ipLong & $mask) === ($rangeLong & $mask);
    }

    protected function checkBotLikeSignals(string $userAgent, Request $request): array
    {
        $score = 0;
        $reasons = [];
        $method = $request->method();

        if (empty($userAgent)) {
            $score += 15;
            $reasons[] = 'Empty User-Agent';
        }

        if (! $request->headers->has('Accept')) {
            $score += 10;
            $reasons[] = 'Missing Accept header';
        }

        if (! $request->headers->has('Accept-Language') && $method === 'GET') {
            $score += 5;
            $reasons[] = 'Missing Accept-Language header';
        }

        $commonCrawlerSignals = ['bot', 'crawler', 'spider', 'scraper', 'scan', 'checker', 'monitor'];
        foreach ($commonCrawlerSignals as $signal) {
            if (stripos($userAgent, $signal) !== false) {
                $score += 20;
                $reasons[] = "User-Agent contains '{$signal}'";
                break;
            }
        }

        return compact('score', 'reasons');
    }
}
