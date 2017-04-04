<?php namespace Sheba\Notification;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationViewComposer
{
    public $user;
    public $limit;

    /**
     * Create a new Notification composer.
     *
     */
    public function __construct()
    {
        $this->user = Auth::user();
        $this->limit = 20;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $limit = $this->limit;
        $notifiable = $this->user;
        $this->checkIfNotifiable($notifiable);
        $unseen_notifications = $notifiable->notifications()->unseen()->latest()->get();
        $unseen_notifications_count = $unseen_notifications->count();
        $limit = ($unseen_notifications_count <= $limit) ? ($limit - $unseen_notifications_count) : 0;
        $notifications = $notifiable->notifications()->seen()->latest()->take($limit)->get();
        $notifications = $unseen_notifications->merge($notifications);
        $view->with('UNSEEN_NOTIFICATIONS_COUNT', $unseen_notifications_count);
        $view->with('NOTIFICATIONS', $notifications);
        $view->with('NOTIFICATION_TYPES', getNotificationTypes());
    }

    /**
     * @param $notifiable
     * @throws \Exception
     */
    private function checkIfNotifiable($notifiable)
    {
        $notifiables = [
            'user' => 'App\Models\User',
            'customer' => 'App\Models\Customer',
            'partner' => 'App\Models\Partner',
            'resource' => 'App\Models\Resource'
        ];

        $class = (is_string($notifiable)) ? $notifiable: get_class($notifiable);
        if(!in_array($class, $notifiables)) {
            throw new \Exception("Invalid user provided for notification. " . get_class($notifiable) . " is not notifiable." );
        }
    }
}