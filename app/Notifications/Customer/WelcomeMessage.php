<?php

namespace App\Notifications\Customer;

use App\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Hubtel\HubtelChannel;
use NotificationChannels\Hubtel\HubtelMessage;

class WelcomeMessage extends Notification
{
    use Queueable;

    protected $customer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', HubtelChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Welcome')
                    ->greeting('Welcome to Jeli')
                    ->line('Your Jeli Account was created successfully');
    }

    public function toSMS($notifiable)
    {
        return (new HubtelMessage)
                    ->from('Jeli')
                    ->content('Your Jeli Account was created successfully. Jeli! Making Moments Possible!')
                    ->registeredDelivery(true);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
