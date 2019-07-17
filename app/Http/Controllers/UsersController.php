<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Handlers\ImageUploadHandler;

class UsersController extends Controller
{
    /**
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 中间件过滤
     */
    public function __construct()
    {
        $this->middleware('auth',['except' => ['show']]);
    }

    //个人中心
    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }

    //编辑用户资料
    public function edit(User $user)
    {
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }

    //更新用户资料
    public function update(UserRequest $request, ImageUploadHandler $uploader,User $user)
    {
        $this->authorize('update',$user);
        $data = $request->all();
        if ($request->avatar) {
            $result = $uploader->save($request->avatar,'avatar',$user->id,416);
            if ($result){
                $data['avatar'] = $result['path'];
            }
        }
        $user->update($data);
        return redirect()->route('users.show',$user->id)->with('success','资料更新成功！');
    }
}
