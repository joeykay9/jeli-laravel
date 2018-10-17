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
        return [HubtelChannel::class, 'database'];
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
                    ->greeting('Hello ' . $this->customer->first_name)
                    ->line('Your Jeli Account was created successfully.')
                    ->line('Create and collobrate on Moments with your friends, family or colleagues.')
                    ->line('You could also invite guests to your Moments and so much more.');
    }

    public function toSMS($notifiable)
    {
        return (new HubtelMessage)
                    ->from('Jeli')
                    ->content('Hello ' . $this->customer->first_name . ', Your Jeli Account was created successfully. ' . 'Create and collobrate on Moments with your friends, family or colleagues. ' . 'Jeli! Making Moments Possible!')
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
