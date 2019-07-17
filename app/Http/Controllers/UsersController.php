<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Handlers\ImageUploadHandler;

class UsersController extends Controller
{
    //个人中心
    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }

    //编辑用户资料
    public function edit(User $user)
    {
        return view('users.edit',compact('user'));
    }

    //更新用户资料
    public function update(UserRequest $request, ImageUploadHandler $uploader,User $user)
    {
//        dd($request->avatar);
        $data = $request->all();
        if ($request->avatar) {
            $result = $uploader->save($request->avatar,'avatar',$user->id);
            if ($result){
                $data['avatar'] = $result['path'];
            }
        }
        $user->update($data);
        return redirect()->route('users.show',$user->id)->with('success','资料更新成功！');
    }
}
