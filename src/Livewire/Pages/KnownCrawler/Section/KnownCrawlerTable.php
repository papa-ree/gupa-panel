<?php

namespace Bale\GupaPanel\Livewire\Pages\KnownCrawler\Section;

use Bale\Core\Traits\HasDeleteOption;
use Bale\GupaPanel\Models\KnownCrawler;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Attributes\On;
use Livewire\Component;

class KnownCrawlerTable extends Component
{
    use HasDeleteOption;

    protected string $modelClass = KnownCrawler::class;

    #[On('addToWhitelist')]
    public function addToWhitelist(string $id): void
    {
        $crawler = KnownCrawler::findOrFail($id);
        $ranges = $crawler->verified_ip_ranges;

        if (empty($ranges)) {
            $this->dispatch('toast', message: __('No IP ranges available for :name.', ['name' => $crawler->name]), type: 'warning');
            return;
        }

        $added = 0;
        $skipped = 0;

        foreach ($ranges as $ip) {
            PanelWhitelist::firstOrCreate(
                ['ip' => $ip],
                ['reason' => __('Added from Known Crawler: :name', ['name' => $crawler->name])]
            )->wasRecentlyCreated ? $added++ : $skipped++;
        }

        $this->dispatch('toast', message: __(':added IP(s) added to whitelist from :name (:skipped duplicate(s)).', ['added' => $added, 'name' => $crawler->name, 'skipped' => $skipped]), type: 'success');
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.known-crawler.section.known-crawler-table');
    }
}
