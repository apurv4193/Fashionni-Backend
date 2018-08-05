<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications;
use Helpers;
use Config;
use DB;
use Carbon\Carbon;

class DeleteNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deleteNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Notifications';

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
        $objNotifications = new Notifications();
        
//      $currentTimeDate = Carbon::now();
//      $currentDate->toDateTimeString();
//      $currentTimeStamp = Carbon::now()->timestamp;
//      $currentTimeStamp = Carbon::now('Asia/Kolkata')->format('H:i');
//      $currentDate = $currentTimeDate->toDateString();
        
//      $dayAgo = Carbon::now('Asia/Kolkata')->subDays(2)->toDateTimeString();
        $dayAgo = Carbon::now()->subDays(2)->toDateString();
        $deleteNotification = Notifications::whereDate('created_at', '<=', $dayAgo)->delete();  
//      $getNotificationData = Notifications::where('created_at', '==', $dayAgo)->get();
    }
}
