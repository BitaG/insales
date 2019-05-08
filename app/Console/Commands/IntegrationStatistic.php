<?php

namespace App\Console\Commands;

use App\Shop;
use Carbon\Carbon;
use Illuminate\Console\Command;

class IntegrationStatistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Statistic from connection.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $connect  = Shop::all()->count();
        $last_installation = Shop::orderBy('created_at','desc')->first();
        $last_day = Shop::where('created_at', '>=', Carbon::now()->subDays(1)->toDateTimeString())->count();
        $last_month = Shop::where('created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString())->count();

        if(!empty($last_day)) {
            $this->line('Last day:'. $last_day);
        }

        if(!empty($last_month)) {
            $this->line('Last month:'. $last_month);
        }

        $this->line('All connect:'.$connect);

        if(!empty($last_installation)) {
            $this->line('Last installation: '.
                $last_installation->shop_id.'|'.$last_installation->created_at);
        }
    }
}
