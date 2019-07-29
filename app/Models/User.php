<?php

namespace App\Models;

use Auth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;

class User extends Authenticatable implements MustVerifyEmailContract, JWTSubject
{
    use  MustVerifyEmailTrait, HasRoles;
    use Traits\LastActivedAtHelper;
    use Traits\ActiveUserHelper;
    use Notifiable{
        notify as protected laravelNotify;
    }

    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
    }

    public function notify($instance)
    {
        //如果要通知的人是当前用户，就不必通知了
        if ($this->id === Auth::id()){
            return;
        }
        //只有数据库类型通知才需要提醒,直接发送Email或其他通知
        if (method_exists($instance,'toDatabase')){
            $this->increment('notification_count');
        }
        $this->laravelNotify($instance);
    }
    
    //允许更新的字段
    protected $fillable = [
        'name', 'email', 'password','avatar',
        'introduction','phone',
        'weixin_openid', 'weixin_unionid',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 用户与话题中间的关系是 一对多 的关系，一个用户拥有多个主题，在 Eloquent 中使用 hasMany() 方法进行关联。
     * 关联设置成功后，我们即可使用 $user->topics 来获取到用户发布的所有话题数据。
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function setPasswordAttribute($value)
    {
        //如果值的长度等于 60 ，即认为是已做过加密的情况
        if (strlen($value) != 60){
            //不等于60 则做加密处理
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    public function setAvatarAttribute($path)
    {
        //如果不是 http 开头，那就是从后台上传的，需要补全 URL
        if (! starts_with($path,'http')){
            //拼接完整的URL
            $path = config('app.url') . "/uploads/images/avatar/$path";

        }

        $this->attributes['avatar'] = $path;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
