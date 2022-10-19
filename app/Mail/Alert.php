<?php

namespace App\Mail;

use WMGCore\Services\ConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Email alert
 *
 * Class Alert
 * @category WMG
 * @package  App\Mail
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Alert extends Mailable
{
    use Queueable, SerializesModels;
    public $alertEvents;
    /**
     * Create a new message instance.
     * @param $alertEvents
     *
     * @return void
     */
    public function __construct($alertEvents)
    {
        $this->alertEvents = $alertEvents;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Event Alerts')->view('alert');
    }
}
