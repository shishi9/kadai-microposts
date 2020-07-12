<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    //ユーザをお気に入りにするアクション
    // @param   $id  相手のユーザID
    // @return  \Illuminate\Heep\Response
    public function store($id)
    {
        // DD($id);
        // 認証済みユーザ（閲覧者）が、 idのユーザをお気に入り登録する
        \Auth::user()->favorite($id);
        // 前のURLへリダイレクトさせる
        return back();
    }
    
    // ユーザをお気に入りから除外するアクション
    // @param  $id 相手のユーザID
    // @return \Illuminate\Http\Response
    public function destroy($id)
    {
        // 認証済みユーザ（閲覧者）が、 idのユーザをお気に入りから除外する
        \Auth::user()->unfavorite($id);
        // 前のURLへリダイレクトさせる
        return back();
    }



}
