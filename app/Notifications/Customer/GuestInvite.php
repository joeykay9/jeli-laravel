<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class GuestInvite extends Notification
{
    use Queueable;

    protected $customer;
    protected $moment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, Moment $moment)
    {
        $this->customer = $customer;
        $this->moment = $moment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [OneSignalChannel::class, 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->subject("Moment Guest Invite")
            ->body("Hi " . $notifiable->first_name . "! " . $this->customer->first_name . " has invited you to thier moment " . $this->moment . ". Follow the link below to respond to the invite.");
    }

    public function toSMS($notifiable)
    {
        return (new HubtelMessage)
                    ->from('Jeli')
                    ->content('Hi' . $notifiable->first_name . '! ' . $this->customer->first_name . ' has invited you to thier moment ' . $this->moment . '. Follow the link below to respond to the invite.')
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
