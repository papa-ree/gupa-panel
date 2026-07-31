<?php

namespace Bale\GupaPanel\Livewire\Pages\KnownCrawler;

use Bale\GupaPanel\Models\KnownCrawler;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class Form extends Component
{
    public ?string $crawlerId = null;

    public string $name = '';

    public string $provider = '';

    public string $user_agent_pattern = '';

    public array $verified_ip_ranges = [];

    public bool $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'provider' => 'required|string|max:255',
        'user_agent_pattern' => 'required|string|max:255',
        'verified_ip_ranges' => 'array',
        'verified_ip_ranges.*' => 'string|max:255',
        'is_active' => 'boolean',
    ];

    public function mount(?string $id = null): void
    {
        if ($id) {
            $this->crawlerId = $id;
            $crawler = KnownCrawler::findOrFail($id);
            $this->name = $crawler->name;
            $this->provider = $crawler->provider;
            $this->user_agent_pattern = $crawler->user_agent_pattern;
            $this->verified_ip_ranges = $crawler->verified_ip_ranges ?? [];
            $this->is_active = $crawler->is_active;
        } else {
            $this->verified_ip_ranges = [''];
        }
    }

    public function addIpRange(): void
    {
        $this->verified_ip_ranges[] = '';
    }

    public function removeIpRange(int $index): void
    {
        unset($this->verified_ip_ranges[$index]);
        $this->verified_ip_ranges = array_values($this->verified_ip_ranges);
    }

    public function save(): void
    {
        $this->validate();

        $verifiedRanges = array_filter($this->verified_ip_ranges, fn($v) => $v !== '');

        if ($this->crawlerId) {
            $crawler = KnownCrawler::findOrFail($this->crawlerId);
            $crawler->update([
                'name' => $this->name,
                'provider' => $this->provider,
                'user_agent_pattern' => $this->user_agent_pattern,
                'verified_ip_ranges' => $verifiedRanges ?: null,
                'is_active' => $this->is_active,
            ]);
        } else {
            KnownCrawler::create([
                'name' => $this->name,
                'provider' => $this->provider,
                'user_agent_pattern' => $this->user_agent_pattern,
                'verified_ip_ranges' => $verifiedRanges ?: null,
                'is_active' => $this->is_active,
            ]);
        }

        $this->dispatch('toast', message: 'Crawler saved.', type: 'success');
        $this->redirectRoute('gupa-panel.known-crawler', navigate: true);
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.known-crawler.form');
    }
}