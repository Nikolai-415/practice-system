<?php

namespace App\Models;

use App\Http\Functions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @method static User find($id)
 * @method static User findOrFail($id)
 * @method static Builder where(...$params)
 * @method static Builder orderBy
 * @property Carbon $created_at Дата и время создания записи в БД
 * @property Carbon $updated_at Дата и время последнего изменения записи в БД
 * @property Carbon $deleted_at Дата и время мягкого удаления записи в БД
 * @property string $login
 * @property string $password_sha512
 * @property string $first_name
 * @property string $second_name
 * @property string $third_name
 * @property string $email
 * @property int $show_email
 * @property string $phone
 * @property int $show_phone
 * @property int accept_chats_from
 * @property Role $role
 * @property Institution $institution
 * @property Carbon $last_activity_at
 */
class User extends Model
{
    use HasFactory;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function bans_from()
    {
        return $this->hasMany(Ban::class, 'user_from_id');
    }

    public function bans_to()
    {
        return $this->hasMany(Ban::class, 'user_to_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function practices()
    {
        return $this->belongsToMany(Practice::class, 'users_to_practices')->withTimestamps();
    }

    public function users_to_practices_statuses()
    {
        return $this->belongsToMany(UsersToPracticesStatus::class, 'users_to_practices')->withTimestamps();
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'users_to_chats')-> withTimestamps()->withPivot(['messages_not_read'])->as('user_to_chat');
    }

    public function tasks_from()
    {
        return $this->hasMany(Task::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'users_to_tasks')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function avatar_file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts_from()
    {
        return $this->hasMany(Contact::class, 'user_from_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function contacts_to()
    {
        return $this->hasMany(Contact::class, 'user_to_id');
    }

    public function getFullName()
    {
        $full_name = $this->second_name.' '.$this->first_name;
        if($this->third_name != null)
        {
            $full_name .= ' '.$this->third_name;
        }
        return $full_name;
    }

    public function isDirector()
    {
        $user_role_id = $this->role->id;
        return (
            ($user_role_id == Role::ROLE_ID_DIRECTOR) ||
            ($user_role_id == Role::ROLE_ID_ADMINISTRATOR) ||
            ($user_role_id == Role::ROLE_ID_SUPER_ADMINISTRATOR)
        );
    }

    public function isAdministrator()
    {
        $user_role_id = $this->role->id;
        return (
            ($user_role_id == Role::ROLE_ID_ADMINISTRATOR) ||
            ($user_role_id == Role::ROLE_ID_SUPER_ADMINISTRATOR)
        );
    }

    public function isSuperAdministrator()
    {
        $user_role_id = $this->role->id;
        return ($user_role_id == Role::ROLE_ID_SUPER_ADMINISTRATOR);
    }

    public function hasPermissionOnUser($user)
    {
        return (($this->role->weight > $user->role->weight) || ($this->id == $user->id));
    }

    public function canChangeRoleOfUser($user)
    {
        return (($this->id != $user->id) && ($this->hasPermissionOnUser($user)));
    }

    /** Может ли поменять роль на указанную */
    public function canChangeRoleTo($role)
    {
        return ($role->weight < $this->role->weight); // не может, если роль по весу выше или равна его текущей роли
    }

    public function isOnline()
    {
        return (now()->diffInMinutes($this->last_activity_at) <= 5);
    }

    public function echoActivityStatus()
    {
        if($this->getActiveBan() != null)
        {
            echo "<span class='ban_active'>Забанен</span>";
        }
        else
        {
            if ($this->isOnline())
            {
                echo "<span class='online_status'>Онлайн</span>";
            }
            else
            {
                echo "<span class='offline_status'>Оффлайн</span>";
            }
        }
    }

    public function canAllowActionFromSettingValueToUser($setting_value, $user)
    {
        if($user->id == $this->id) return true;
        else if ($setting_value == Functions::SETTING_VALUE_ALL) return true;
        else if ($setting_value == Functions::SETTING_VALUE_CONTACTS)
        {
            $contact_request = $this->getContactRequestWithUser($user);
            if(($contact_request != null) && ($contact_request->is_accepted)) return true;
        }
        return false;
    }

    public function canShowEmailTo($user)
    {
        return $this->canAllowActionFromSettingValueToUser($this->show_email, $user);
    }

    public function canShowPhoneTo($user)
    {
        return $this->canAllowActionFromSettingValueToUser($this->show_phone, $user);
    }

    public function getAvatarFileSrc()
    {
        if($this->avatar_file == null)
        {
            return '/img/avatar_default.jpg';
        }
        else
        {
            return $this->avatar_file->getFileUrl();
        }
    }

    public function getActiveBan()
    {
        $bans = $this->bans_to;
        foreach ($bans as $ban)
        {
            if($ban->isActive()) return $ban;
        }
        return null;
    }

    public function canBanUser(User $user)
    {
        return (($this->id != $user->id) && ($this->hasPermissionOnUser($user)) && ($user->getActiveBan() == null)); // можно забанить пользователя только если у него сейчас нет другого бана
    }

    public function canUnbanBan(Ban $ban)
    {
        return (($this->id != $ban->user_to->id) && ($this->hasPermissionOnUser($ban->user_to)) && $ban->isActive());
    }

    public function canDeleteBan(Ban $ban)
    {
        return (($this->id != $ban->user_to->id) && ($this->hasPermissionOnUser($ban->user_to)));
    }

    public function checkUserPermissionsToUser(&$user)
    {
        if($user->canShowEmailTo($this) == false)
        {
            $user['email'] = 'Скрыт';
        }
        if($user->canShowPhoneTo($this) == false)
        {
            $user['phone'] = 'Скрыт';
        }
    }

    public function checkUserPermissionsToUsers(&$users)
    {
        foreach ($users as $user)
        {
            $this->checkUserPermissionsToUser($user);
        }
    }

    public function getAllContacts()
    {
        $users_in_contacts = array();

        $contacts_from_accepted = $this->contacts_from()->where('is_accepted', '=', true)->get();
        foreach ($contacts_from_accepted as $contact_from)
        {
            $users_in_contacts[] = $contact_from->user_to;
        }

        $contacts_to_accepted = $this->contacts_to()->where('is_accepted', '=', true)->get();
        foreach ($contacts_to_accepted as $contact_to)
        {
            $users_in_contacts[] = $contact_to->user_from;
        }

        self::checkUserPermissionsToUsers($users_in_contacts);
        return $users_in_contacts;
    }

    public function getIncomingContacts()
    {
        $users_in_contacts = array();

        $contacts_to_accepted = $this->contacts_to()->where('is_accepted', '=', false)->get();
        foreach ($contacts_to_accepted as $contact_to)
        {
            $users_in_contacts[] = $contact_to->user_from;
        }

        self::checkUserPermissionsToUsers($users_in_contacts);
        return $users_in_contacts;
    }

    public function getOutcomingContacts()
    {
        $users_in_contacts = array();

        $contacts_from_accepted = $this->contacts_from()->where('is_accepted', '=', false)->get();
        foreach ($contacts_from_accepted as $contact_from)
        {
            $users_in_contacts[] = $contact_from->user_to;
        }

        self::checkUserPermissionsToUsers($users_in_contacts);
        return $users_in_contacts;
    }

    /**
     * @return Builder|Model|object|Contact
    */
    public function getContactRequestWithUser($user)
    {
        return Contact::where([
            ['user_from_id', '=', $this->id],
            ['user_to_id', '=', $user->id]
        ])->orWhere([
            ['user_to_id', '=', $this->id],
            ['user_from_id', '=', $user->id]
        ])->first();
    }

    public function getIncomingContactsCount()
    {
        return $this->contacts_to()->where('is_accepted', '=', 'false')->get()->count();
    }

    public function getOutcomingContactsCount()
    {
        return $this->contacts_from()->where('is_accepted', '=', 'false')->get()->count();
    }

    public function canCreateChatWith(User $user)
    {
        if($user->id == $this->id)
        {
            return false;
        }
        else
        {
            return $user->canAllowActionFromSettingValueToUser($user->accept_chats_from, $this);
        }
    }

    /**
     * @return Chat
    */
    public function getPersonalChatWith(User $user) {
        $chats = $this->getPersonalChats();
        foreach ($chats as $chat)
        {
            if($chat->getSecondUserIfChatIsPersonal($this)->id == $user->id)
            {
                return $chat;
            }
        }
        return null;
    }

    public function hasPersonalChatWith(User $user) {
        return ($this->getPersonalChatWith($user) != null);
    }

    public function getPersonalChats()
    {
        return $this->chats()->where('chat_type_id', '=', ChatType::CHAT_TYPE_ID_PERSONAL)->get();
    }

    // Находим массив пользователей, с которыми у данного пользователя имеется личный чат
    public function getPersonalChatsUsers()
    {
        $chats_users = array();
        $chats = $this->getPersonalChats();
        foreach ($chats as $chat)
        {
            $chats_users[] = $chat->getSecondUserIfChatIsPersonal($this);
        }
        return $chats_users;
    }

    public function isUserInChat(Chat $chat)
    {
        return ($chat->users->where('id', '=', $this->id)->first() != null);
    }

    public function canCreatePractices()
    {
        return $this->isDirector();
    }

    public function hasPermissionOnPractice(Practice $practice)
    {
        if($this->isDirector())
        {
            if($practice->user_id == $this->id)                                  // Если практика создана этим же пользователем
            {
                return true;
            }
            else if($this->hasPermissionOnUser($practice->user_from))               // если пользователь имеет права выше, чем пользователь, создавший практику - то имеет права над практикой
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function isUserInPractice(Practice $practice)
    {
        return ($practice->users->where('id', '=', $this->id)->first() != null);
    }

    public function getCountNewMessagesInChat(Chat $chat)
    {
        $user_to_chat = $this->chats()->find($chat->id)->user_to_chat;
        return $user_to_chat->messages_not_read;
    }

    public function getCountNewMessagesInPractice(Practice $practice)
    {
        $chat = $practice->getPracticeMainChatOrFail();
        $user_to_chat = $this->chats()->find($chat->id)->user_to_chat;
        return $user_to_chat->messages_not_read;
    }

    public function getCountNewMessagesInPersonalChats()
    {
        $chats = $this->chats;
        $count_new_messages = 0;
        /**
         * @var Chat $chat
         */
        foreach ($chats as $chat)
        {
            if($chat->chat_type->id == ChatType::CHAT_TYPE_ID_PERSONAL)
            {
                $count_new_messages += $this->getCountNewMessagesInChat($chat);
            }
        }
        return $count_new_messages;
    }

    public function getCountNewMessagesInPracticChats()
    {
        $chats = $this->chats;
        $count_new_messages = 0;
        /**
         * @var Chat $chat
         */
        foreach ($chats as $chat)
        {
            if($chat->chat_type->id == ChatType::CHAT_TYPE_ID_PRACTIC)
            {
                $count_new_messages += $this->getCountNewMessagesInChat($chat);
            }
        }
        return $count_new_messages;
    }
}
