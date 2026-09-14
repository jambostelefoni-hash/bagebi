<?php
namespace App\Notifications;
use App\Notifications\Channels\MailtrapEmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class ParentActionNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $subjectLine; private $messageLine; private $actionUrl; public $deliveryId;
    public function __construct(string $subject,string $message,?string $actionUrl=null,?int $deliveryId=null){$this->subjectLine=$subject;$this->messageLine=$message;$this->actionUrl=$actionUrl;$this->deliveryId=$deliveryId;}
    public function via($notifiable){return config('services.mailtrap.api_token') ? [MailtrapEmailChannel::class] : ['mail'];}
    public function toMail($notifiable){$mail=(new MailMessage)->subject($this->subjectLine)->greeting('გამარჯობა')->line($this->messageLine);if($this->actionUrl)$mail->action('გაგრძელება',$this->actionUrl);return $mail->line('ეს შეტყობინება გამოგზავნილია ბაგა-ბაღების მართვის სისტემიდან.');}
    public function toMailtrap($notifiable){return ['subject'=>$this->subjectLine,'message'=>$this->messageLine,'action_url'=>$this->actionUrl,'category'=>'Kindergarten platform'];}
}
