<?php

namespace App\Http\Controllers;

use App\Model\Role;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function list(Request $request) {

        $users = User::paginate(10)->toArray();
        $data = $users['data'];
        foreach ($data as $key => &$value) {
            $role = Role::where(array('uid' => $value['id']))->first();
            $value['role'] = $role;
        }
        $users['data'] = $data;

        return $this->pjson(0, $users);
    }


    public function test(Request $request) {
//        dd($request->session()->all());
    }

    public function info() {
        $user = $this->guard()->user();
        $type = $user->role->type;
        return $this->json([
            'code' => 0,
            'data' => array_merge($user->toArray(), ['type' => $type])
        ]);
    }

    public function createUser(Request $request) {
        $data = $request->post();

        $notValid = $this->validateData($data);
        if ($notValid) {
            return $notValid;
        }
        $res = $this->create($data);
        if ($res) {
            return $this->json([
                'code' => 0,
                'data' => [
                    'user' => $res
                ]
            ]);
        } else {
            return $this->json([
                'code' => 400,
                'msg' => '创建用户失败'
            ]);
        }
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    protected function validateData($data) {
        $validator = validator(
            $data, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ], []);
        if ($validator->fails()) {
            $msgs = $validator->errors()->getMessages();
            return [
                'code' => 400,
                'msg' => $msgs
            ];
        }
        return false;
    }

    protected function pjson($code, $data = '', $msg = null) {

        return $this->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    public function activeAccount(Request $request) {
        $data = $request->post();
        $id = $data['id'];
        $active = $data['active'];
        $active = intval($active);
//        dd($data);
        if (!$id) {
            return $this->pjson(400, '', '更新失败');
        }
        $user = User::find($id);
        $user->isActived = [2,1,2,3][$active];
        $res = $user->save();
        if ($res) {
            return $this->pjson(0, ['msg' => '更新成功']);
        } else {
            return $this->pjson(400, '', '更新失败');
        }
    }


    public function resetSelfPwd(Request $request)
    {
        $data = $request->post();
        $validator = validator(
            $data, [
            'password' => 'required|string|min:6',
        ], []);
        if ($validator->fails()) {
            $msgs = $validator->errors()->getMessages();
            return $this->json([
                'code' => 400,
                'msg' => $msgs
            ]);
        }
        $user = $this->guard()->user();
        $password = $data['password'];
        $res = $this->resetPassword($user, $password);

        if ($res) {
            return $this->pjson(0, ['msg' => '更新密码成功']);
        } else {
            return $this->pjson(400, '', '更新失败');
        }
    }
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        return $user->save();
    }
    public function resetPwd(Request $request)
    {
        $data = $request->post();
        $validator = validator(
            $data, [
            'password' => 'required|string|min:6',
        ], []);
        if ($validator->fails()) {
            $msgs = $validator->errors()->getMessages();
            return $this->json([
                'code' => 400,
                'msg' => $msgs
            ]);
        }
        $id = $data['id'];
        if (!$id) {
            return $this->pjson(400, '', '更新失败');
        }
        $user = User::find($id);
        $password = $data['password'];
//        dd($user);
        $res = $this->resetPassword($user, $password);

        if ($res) {
            return $this->pjson(0, ['msg' => '更新密码成功']);
        } else {
            return $this->pjson(400, '', '更新失败');
        }
    }

    public function setAdmin(Request $request) {
        $data = $request->post();
        if (!array_key_exists('id', $data) || !array_key_exists('type', $data) || !in_array(intval($data['type']), [1, 2])) {
            return $this->pjson(400, '', '参数错误');
        }
        $id = $data['id'];
        $type = $data['type'] == 1 ? 1 : 2;
        $role = Role::where(['uid' => $id])->first();
        $user = $this->user();
        $cid = $user->id;

        $currentRole = Role::where(['uid' => $cid])->first();
        if ($id == 1 && $cid === 1) {

        } else {
            if ($currentRole) {
                $currentUserType = $currentRole->type;
                if ($currentUserType !== 1) {
                    return $this->wjson(400, '', '您没有权限操作!');
                }
            } else {
                return $this->wjson(400, '', '您没有权限操作!');
            }
        }

        if ($role) {
            $role->type = $type;
            $role->cid = $cid;
            $res = $role->save();
            if ($res) {
                return $this->pjson(0, ['msg' => '设置成功']);
            } else {
                return $this->pjson(400,'', '设置失败!');
            }
        } else {
            $res = Role::create([
                'uid' => $id,
                'type' => $type,
                'cid' => $cid
            ]);

            if ($res) {
                return $this->pjson(0, ['msg' => '设置成功']);
            } else {
                return $this->pjson(400,'', '设置失败!');
            }
        }

    }
}
