<?php namespace Sheba\Notification;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Session;
use Carbon\Carbon;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Partner;
use App\Models\Resource;
use App\Models\User;

class NotificationHandler
{
    /** @var */
    protected $notifiable_type;
    /** @var */
    protected $notifiable_id;
    /** @var array */
    protected $notifiable_types = [];
    /** @var array */
    protected $notifiable_ids = [];

    protected $senderId;
    protected $senderType;

    private $userSettingsIgnored = false;
    private $forceSending = false;

    /** @var array */
    public $notifiables = [
        'user' => 'App\Models\User',
        'customer' => 'App\Models\Customer',
        'partner' => 'App\Models\Partner',
        'resource' => 'App\Models\Resource'
    ];

    /**
     * @param $notifiable
     * @param int $limit
     * @throws \Exception
     */
    public function setViewData($notifiable, $limit = 20)
    {
        $this->checkIfNotifiable($notifiable);
        $unseen_notifications = $notifiable->notifications()->unseen()->get();
        $unseen_notifications_count = $unseen_notifications->count();
        $limit = ($unseen_notifications_count <= $limit) ? ($limit - $unseen_notifications_count) : 0;
        $notifications = $notifiable->notifications()->seen()->latest()->take($limit)->get();
        $notifications = $unseen_notifications->merge($notifications);
        \View::share('UNSEEN_NOTIFICATIONS_COUNT', $unseen_notifications_count);
        \View::share('NOTIFICATIONS', $notifications);
        \View::share('NOTIFICATION_TYPES', getNotificationTypes());
    }

    /**
     * Define who is generating this notification
     *
     * @param $id
     * @param $type
     * @return $this|NotificationHandler
     */
    public function sender($id, $type = 'user')
    {
        $this->senderId = $id;
        $this->senderType = $this->notifiables[$type];
        return $this;
    }

    public function ignoreUserSettings()
    {
        $this->userSettingsIgnored = true;
        return $this;
    }

    public function forceSend($data)
    {
        $this->forceSending = true;
        return $this->send($data);
    }

    /**
     * @param $data
     * @throws \Exception
     * @return $this|NotificationHandler
     */
    public function send($data)
    {
        $data = $this->prepare($data);

        if($this->notifiable_type && $this->notifiable_id) {
            array_push($this->notifiable_types, $this->notifiable_type);
            array_push($this->notifiable_ids, $this->notifiable_id);
        }

        return $this->sendToAll($data);
    }

    /**
     * @param $data
     * @throws \Exception
     * @return $this|NotificationHandler
     */
    public function sendToAll($data)
    {
        if($this->validateNotifiable()) {
            foreach($this->notifiable_ids as $key => $id) {
                if($this->isSent($key)) continue;
                if(!$this->forceSending) {
                    if($this->isAuthUser($key) || $this->hasUserTurnedOffNotification($key, $data['link'])) continue;
                }
                unset($data['id']);
                $data['notifiable_id'] = $id;
                $data['notifiable_type'] = $this->notifiable_types[$key];
                $data['id'] = Notification::insertGetId($data);
                if(config('sheba.socket_on')) event(new NotificationCreated($data, $this->senderId, $this->senderType));
            }
        }
        return $this;
    }

    /**
     * @param $notifiable
     * @return $this|NotificationHandler
     */
    public function setNotifiable($notifiable)
    {
        if(is_array($notifiable) || $notifiable instanceof Collection) {
            return $this->setNotifiables($notifiable);
        }
        if($notifiable instanceof Department) {
            return $this->department($notifiable);
        }
        $this->checkIfNotifiable($notifiable);
        $this->notifiable_type = get_class($notifiable);
        $this->notifiable_id = $notifiable->id;
        return $this;
    }

    /**
     * @param $notifiables
     * @return $this
     */
    public function setNotifiables($notifiables)
    {
        foreach($notifiables as $notifiable) {
            if($notifiable instanceof Department) {
                $this->department($notifiable); continue;
            }

            $this->checkIfNotifiable($notifiable);
            array_push($this->notifiable_types, get_class($notifiable));
            array_push($this->notifiable_ids, $notifiable->id);
        }
        return $this;
    }

    /**
     * @param $notifiable_type
     * @param $notifiable_id
     * @return $this
     * @throws \Exception
     */
    public function setNotifiableDirectly($notifiable_type, $notifiable_id)
    {
        $this->checkIfNotifiable($notifiable_type, $notifiable_id);
        $this->notifiable_type = $notifiable_type;
        $this->notifiable_id = $notifiable_id;
        return $this;
    }

    /**
     * @param $user
     * @return NotificationHandler
     * @throws \Exception
     */
    public function user($user)
    {
        return $this->setNotifiableDirectly('App\Models\User', ($user instanceof User) ? $user->id : $user);
    }

    /**
     * @param $users
     * @return $this
     */
    public function users($users)
    {
        foreach($users as $user) {
            array_push($this->notifiable_types, 'App\Models\User');
            array_push($this->notifiable_ids, ($user instanceof User) ? $user->id : $user);
        }
        return $this;
    }

    /**
     * @param $department
     * @return NotificationHandler
     */
    public function department($department)
    {
        $department = ($department instanceof Department) ? $department : Department::find($department);
        return $this->users($department->users);
    }

    /**
     * @param $departments
     * @return NotificationHandler
     */
    public function departments($departments)
    {
        foreach($departments as $department) {
            $this->department($department);
        }
        return $this;
    }

    /**
     * @param $resource
     * @return NotificationHandler
     * @throws \Exception
     */
    public function resource($resource)
    {
        return $this->setNotifiableDirectly('App\Models\Resource', ($resource instanceof Resource) ? $resource->id : $resource);
    }

    /**
     * @param $resources
     * @return $this
     */
    public function resources($resources)
    {
        foreach($resources as $resource) {
            array_push($this->notifiable_types, 'App\Models\Resource');
            array_push($this->notifiable_ids, ($resource instanceof Resource) ? $resource->id : $resource);
        }
        return $this;
    }

    /**
     * @param $partner
     * @return NotificationHandler
     * @throws \Exception
     */
    public function partner($partner)
    {
        return $this->setNotifiableDirectly('App\Models\Partner', ($partner instanceof Partner) ? $partner->id : $partner);
    }

    /**
     * @param $partners
     * @return $this
     */
    public function partners($partners)
    {
        foreach($partners as $partner) {
            array_push($this->notifiable_types, 'App\Models\Partner');
            array_push($this->notifiable_ids, ($partner instanceof Partner) ? $partner->id : $partner);
        }
        return $this;
    }

    /**
     * Customer
     *
     * @param $customer
     * @return NotificationHandler
     * @throws \Exception
     */
    public function customer($customer)
    {
        return $this->setNotifiableDirectly('App\Models\Customer', ($customer instanceof Customer) ? $customer->id : $customer);
    }

    /**
     * @param $customers
     * @return $this
     */

    public function customers($customers)
    {
        foreach($customers as $customer) {
            array_push($this->notifiable_types, 'App\Models\Customer');
            array_push($this->notifiable_ids, ($customer instanceof Customer) ? $customer->id : $customer);
        }
        return $this;
    }

    /**
     * @param $notifiable
     * @param null $id
     * @throws \Exception
     */
    private function checkIfNotifiable($notifiable, $id = null)
    {
        $class = (is_string($notifiable)) ? $notifiable: get_class($notifiable);
        if(!in_array($class, $this->notifiables)) {
            throw new \Exception("Invalid user provided for notification. " . get_class($notifiable) . " is not notifiable." );
        }

        if(!empty($id) && !is_int($id)) {
            throw new \Exception("Integer expected as notifiable id, " . gettype($id) . " given.");
        }
    }

    /**
     * @throws \Exception
     */
    private function validateNotifiable()
    {
        $caller = debug_backtrace()[1]['function'];
        if( ($caller == "send" && !$this->notifiable_type && !$this->notifiable_id) ||
            ($caller == "sendToAll" && empty($this->notifiable_types) && empty($this->notifiable_ids)) ) {
            return false;
            //throw new \Exception("I'm not sure about whom I should send this notification. Make sure to set that correctly.");
        }
        return true;
    }

    /**
     * @param $data
     * @return array
     * @throws \Exception
     */
    private function prepare($data)
    {
        if(!isset($data) || (is_array($data) && !isset($data['title']))) {
            throw new \Exception("Notifications must have a title.");
        }

        return ((is_array($data)) ? $data : ['title' => $data ]) + ['created_at' => Carbon::now()];
    }

    /**
     *
     */
    private function removeDuplication()
    {
        for($i = 0; $i < count($this->notifiable_ids); $i++) {
            for($j = 0; $j < $i; $j++) {
                if($this->notifiable_ids[$i] == $this->notifiable_ids[$j] &&
                    $this->notifiable_types[$i] == $this->notifiable_types[$j]) {
                    array_splice($this->notifiable_ids, $i, 1);
                    array_splice($this->notifiable_types, $i, 1);
                    $i--;
                }
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    private function isSent($key)
    {
        for($i=0; $i <= $key - 1; $i++) {
            if( $this->notifiable_ids[$i] == $this->notifiable_ids[$key] &&
                $this->notifiable_types[$i] == $this->notifiable_types[$key] )
                return true;
        }
        return false;
    }

    /**
     * @param $key
     * @return bool
     */
    private function isAuthUser($key)
    {
        if(empty($this->senderId) && empty($this->senderType)) {
            if(!Auth::user()) return false;
            return $this->notifiable_ids[$key] == Auth::user()->id && $this->notifiable_types[$key] == get_class(Auth::user());
        }
        return $this->notifiable_ids[$key] == $this->senderId && $this->notifiable_types[$key] == $this->senderType;
    }

    /**
     * @param $key
     * @param $link
     * @return bool
     */
    private function hasUserTurnedOffNotification($key, $link)
    {
        if($this->userSettingsIgnored) return false;
        if($this->notifiable_types[$key] != "App\\Models\\User") return false;
        if(!$link) return false;
        $user = User::find($this->notifiable_ids[$key]);
        $link = explode('/', $link);
        if(count($link) < 4) return false;
        $event_type = 'App\Models\\' . studly_case($link[3]);
        if(count($link) == 4) return $this->hasUserTurnedOffThisEventType($user, $event_type);
        $event = ['event_type' => $event_type, 'event_id' => $link[4]];
        return $this->hasUserUnfollowedThisEvent($user, $event) || $this->hasUserTurnedOffThisEventType($user, $event_type);
    }

    /**
     * @param $user
     * @param $event
     * @return bool
     */
    private function hasUserUnfollowedThisEvent($user, $event)
    {
        return $user->unfollowedNotifications()->where($event)->count();
    }

    /**
     * @param $user
     * @param $event
     * @return bool
     */
    private function hasUserTurnedOffThisEventType($user, $event)
    {
        $rules = $user->notificationSetting->rules;
        $event = snake_case(class_basename($event)); // App\Models\Job => Job => job; App\Models\InfoCall => InfoCall => info_call;
        if(!property_exists($rules, $event)) return true;
        return !$rules->$event;
    }
}