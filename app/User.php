<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    // このユーザが所有する投稿
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    // このユーザに関係するモデルの件数をロードする。
    public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers','favorites']);
    }
    
    // このユーザがフォロー中のユーザ（Userモデルとの関係を定義）user >> follow
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    // このユーザをフォロー中のユーザ（Userモデルとの関係を定義）follow >> user
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    // $userIdで指定されたユーザをフォローする
    // @parma int $userId
    // @return bool
    public function follow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist || $its_me) {
            // すでにフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    /*
    * $userIdで指定されたユーザをアンフォローする。  
    * @param int $userId
    * @return bool  
    */
    public function unfollow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // すでにフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    // 指定された$userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中:true   以外:false
    public function is_following($userId)
    {
        // フォロー中ユーザの中に $userIdのものが存在するか
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    public function feed_microposts()
    {
        // このユーザがフォロー中のユーザのidを取得して配列にする
        $userIds = $this->followings()->pluck('users.id')->toArray();
        // このユーザのidもその配列に追加
        $userIds[] = $this->id;
        // それらのユーザが所有する投稿に絞り込む
        return Micropost::whereIn('user_id',$userIds);
    }

    // このユーザがお気に入りのユーザ
    // belongsToMany(関連づけるモデル名, 使用する中間テーブル名, 中間テーブルに保存されている自分のidのカラム名, 中間テーブルに保存されている関係先のidのカラム名);
    public function favorites()
    {
        // var_dump($this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->toSql());
        return $this->belongsToMany(Micropost::class,'favorites','user_id','micropost_id')->withTimestamps();
    }

    // $userIdで指定されたユーザをフォローする
    // @parma int $userId
    // @return bool
    // public function favorite($userId,$micropostId)
    public function favorite($micropostId)
    {
        //   dd($userId);

        // すでにお気に入り登録しているかの確認
        $exist = $this->is_favorite($micropostId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $micropostId;
        // ver_dump($exist);
        // print_r($exist);
        //  dd($exist, $its_me);
        if ($exist || $its_me) {
            // すでにお気に入り登録していれば何もしない
            return false;
        } else {
            // お気に入り未登録であればお気に入り登録する
            $this->favorites()->attach($micropostId);
            return true;
        }
    }
    
    /*
    * $userIdで指定されたユーザをアンフォローする。  
    * @param int $userId
    * @return bool  
    */
    public function unfavorite($micropostId)
    {
        // すでにお気に入り登録しているかの確認
        $exist = $this->is_favorite($micropostId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $micropostId;

    // dd($exist,$its_me);
        if ($exist && !$its_me) {
            // すでにお気に入り登録していればお気に入り登録を外す
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            // お気に入り未登録であれば何もしない
            return false;
        }
    }

    // 指定された$userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中:true   以外:false
    // public function is_favorite($userId, $micropostId)
    public function is_favorite($micropostId)
    {
        // フォロー中ユーザの中に $userIdのものが存在するか
        return $this->favorites()->where('micropost_id',  $micropostId)->exists();
        //  var_dump($this->favorites()->where('user_id', $userId)->where('micropost_id',$micropostId)->toSql());
        //   var_dump($this->favorites()->where('micropost_id', $micropostId , 13 )->toSql());
        // return $this->favorites()->where('user_id', $userId)->where('micropost_id',$micropostId)->exists();
    }



}

