<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publish articles whose scheduled time has passed';

    public function handle(ArticleService $articles): int
    {
        $count = $articles->publishDue();
        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
