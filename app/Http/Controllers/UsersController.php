<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;   //追加

class UsersController extends Controller
{
    public function index()
    {
        // ユーザ一覧をIDの降順で取得
        $users = User::orderBy('id','desc')->paginate(10);
        
        return view('user.index',[
            'users' => $users,
            ]);
    }
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.show',[
            'user' => $user,
            ]);
    }
    
}
