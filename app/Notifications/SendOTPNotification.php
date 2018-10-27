<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Hubtel\HubtelChannel;
use NotificationChannels\Hubtel\HubtelMessage;
use App\Otp;

class SendOTPNotification extends Notification
{

    protected $otp;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Otp $otp)
    {
        $this->otp = $otp->otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [HubtelChannel::class];
    }

    public function toSMS($notifiable)
    {
        return (new HubtelMessage)
                    ->from('Jeli')
                    ->content('<#> Your Jeli verification code is ' . $this->otp . '
                     LuRTvMBAktx ')
                    ->registeredDelivery(true);
    }
}
