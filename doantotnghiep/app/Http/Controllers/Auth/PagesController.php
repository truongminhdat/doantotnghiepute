<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestPassword;
use App\Models\Dangtin;
use App\Models\Loaiphong;
use App\Models\PasswordReset;
use App\Models\Phuong;
use App\Models\Quan;
use App\Models\slide;
use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\User\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PagesController extends Controller
{
    protected $userService;
    function __construct(UserService $userService){
        $this->userService = $userService;
        $data = [
            'slide' => slide::all(),
            'dangtin'=> Dangtin::all(),
            'phuong'=>Phuong::all(),
            'quan'=>Quan::all(),
        ];

        view()->share('data',$data);
        view()->share('slide',$data);
        view()->share('phuong',$data);
        view()->share('quan',$data);
        $dangtin = Dangtin::with('loaiphong')->get();
        $phuong = Dangtin::with('phuong')->get();
        $user = Dangtin::with('user')->get();
        $quan = Phuong::with('quan')->get();
        $loaiphong = Loaiphong::all();
        $loaiquan = Quan::all();
        $phuong = Phuong::all();
        view()->share('loaiquan',$loaiquan);
        view()->share('quan',$quan);
        view()->share('phuong',$phuong);
        view()->share('dangtin',$dangtin);
        view()->share('user',$user);
        view()->share('loaiphong',$loaiphong);
    }

   public function trangchu(){
        $dangtin = Dangtin::where('status','1')->get();
        return view('pages.home',compact('dangtin'));
       }


       public  function  showRegister(){
       return view('pages.dangki');
    }
    public function register(Request $request)
    {
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(),[
                'name'=>'required|min:6|max:30|',
                'email'=>'required|email',
                'Anhdaidien'=>'required|image|mimes:jpg,jpeg,png,gif|max:100000',
                'Anhbia'=>'required|image|mimes:jpg,jpeg,png,gif|max:100000',
                'sdt'=>'required',

                'password'=>'required|confirmed|min:6|max:20',
            ],[
                'password.confirmed'=>'M???t kh???u kh??ng tr??ng kh???p'
            ]);
            if ($validator->fails()){
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            if($request->hasFile('Anhdaidien')){
                $file = $request->file('Anhdaidien');
                $destination_path = public_path('upload/user');
                $file_Name = time().'_'.$file->getClientOriginalName();
                $file->move($destination_path,$file_Name);
            }
            else{
                $file_Name = 'noname.jpg';

            }
            if($request->hasFile('Anhbia')){
                $file = $request->file('Anhbia');
                $destination_path = public_path('upload/user');
                $file_Name_anhbia = time().'_'.$file->getClientOriginalName();
                $file->move($destination_path,$file_Name_anhbia);
            }
            else{
                $file_Name = 'noname.jpg';

            }
            try {
                $user = DB::table('users')->where('email',$request->email)->first();
                if(!$user){
                    $newUser = new User();
                    $newUser->name = $request->name;
                    $newUser->email = $request->email;
                    $newUser->password = $request->password;
                    $newUser->diachi = $request->diachi;
                    $newUser->sdt = $request->sdt;
                    $newUser->gioitinh = $request->gioitinh;
                    $newUser->ngaysinh = $request->ngaysinh;
                    $newUser->Anhdaidien = $file_Name;
                    $newUser->Anhbia = $file_Name_anhbia;
                    $newUser->level = $request->level;
                    $newUser->role_id = 2;
                    $newUser->save();
                    return redirect()->route('register')->with('thongbao','T???o t??i kho???n th??nh c??ng,m???i b???n ????ng nh???p');

                }
                else {
                    return redirect()->route('login')->with('thongbao','T??i kho???n ???? t???n t???i m???i ????ng nh???p,m???i b???n ????ng nh???p');
                }
            }catch (\Exception $exception){
                return redirect()->route('register')->with('thongbao','????ng k?? t??i kho???n kh??ng th??nh c??ng m???i ????ng k?? l???i');
            }


        }
    }
    public function getDangNhap(){
        return view('pages.dangnhap');
    }
    function postDangnhap(Request $request){
        $this->validate($request,[
            'email'=>'required',
            'password'=>'required|min:3|max:320'
        ],[
            'email.required'=>'B???n ch??a nh???p email',
            'password.required'=>'B???n ch??a nh???p password',
            'password.min'=>'Password kh??ng ???????c nh??? h??n 3 k?? t???',
            'password.max'=>'Password kh??ng ???????c nhi???u h??n 32 k?? t???'



        ]);
        $arr = [
            'email'=>$request->email,
            'password'=>$request->password,
        ];
        $dataUser = User::where('email',$request->email)->first();
        if(!$dataUser){
            return redirect('dangnhap')->with('thongbao','T??i kho???n kh??ng t???n t???i');
        }
        if ($dataUser->status == 1){
            return redirect('dangnhap')->with('thongbao','T??i kho???n ???? b??? kh??a');
        }
        if($dataUser->role_id == 2){
            if (Auth::attempt($arr))
            {
                return redirect('trangchu');
            }
            else{
                return redirect('dangnhap')->with('thongbao','T??i kho???n ???? b??? kh??a ho???c ch??a ????ng k?? vui l??ng ki???m tra l???i');
            }
        }
    }
    public function logout(){
       Auth::logout();
       return redirect('trangchu');
    }
    public function getnguoidung(){
        $user = Auth::user();
        return view('pages.nguoidung',['user'=>$user]);
    }
   public  function updatenguoidung(Request $request,$id)
   {
       $updateuser = User::find($id);
       $updateuser->name = $request->input('name');
       $updateuser->email = $request->input('email');
       $updateuser->password = $request->input('password');
       $updateuser->diachi = $request->input('diachi');
       $updateuser->sdt = $request->input('sdt');
       $updateuser->gioitinh = $request->input('gioitinh');
       $updateuser->ngaysinh = $request->input('ngaysinh');
       if($request->hasFile('Anhdaidien')){
           $destination = public_path('upload/user').$updateuser->Anhdaidien;
           if (\Illuminate\Support\Facades\File::exists($destination)){
               \Illuminate\Support\Facades\File::delete($destination);
           }
           $file = $request->file('Anhdaidien');
           $destination_path = public_path('upload/user');
           $file_Name_anhdaidien = time().'_'.$file->getClientOriginalName();
           $file->move($destination_path,$file_Name_anhdaidien);
           $updateuser->Anhdaidien = $file_Name_anhdaidien;
       }
       if($request->hasFile('Anhbia')){
           $destination = public_path('upload/user').$updateuser->Anhbia;
           if (\Illuminate\Support\Facades\File::exists($destination)){
               \Illuminate\Support\Facades\File::delete($destination);
           }
           $file = $request->file('Anhbia');
           $destination_path = public_path('upload/user');
           $file_Name_anhbia = time().'_'.$file->getClientOriginalName();
           $file->move($destination_path,$file_Name_anhbia);
           $updateuser->Anhbia = $file_Name_anhbia;
       }
       $updateuser->update();
       return redirect()->back()->with('success','B???n ???? c???p nh???t th??nh c??ng');

   }

   public function getupdatepassword(){
        return view('user.resetpassword');
   }

   public function updatepassword(Request $request){
           if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
               // The passwords matches
               return redirect()->back()->with("error","M???t kh???u hi???n t???i c???a b???n kh??ng ????ng");
           }

           if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
               // Current password and new password same
               return redirect()->back()->with("error","M???t kh???u m???i tr??ng v???i m???t kh???u c?? c???a b???n");
           }

            $request->validate([
               'current-password' => 'required',
               'new-password' => 'required|string|min:8|confirmed',
           ]);

           //Change Password
           $user = Auth::user();
           $user->password =($request->get('new-password'));
           $user->save();

           return redirect()->back()->with("success","Thay ?????i password th??nh c??ng!!");
       }
       public function forgetPass(){
        return view('user.forgetpass');
       }
       public function postforgetPass(Request $request)
       {
           $request->validate([
               'email'=>'required|exists:users'
           ],[
               'email.required'=>'Vui l??ng nh???p email h???p l???',
               'email.exists'=>'Email n??y kh??ng t???n t???i trong h??? th???ng'
           ]);
           dd($request->all());
//           $token = Str::random();
//           $user = User::where('email',$request->email)->first();
//           $user->update(['token'=>$token]);
//           Mail::send('mail.mail',compact('user'),function ($email) use ($user){
//            $email->subject('Web qu???n l?? tr???- L???y l???i m???t kh???u t??i kho???n');
//            $email->to($user->email,$user->name);
//            return redirect()->back('dangnhap')->with('success','Vui l??ng check mail ????? check m??');
//           });
       }

       public function  getPass(User $user,$token){
        if ($user->token === $token){
            return view('mail.mail');
        }
        return  abort(404);
       }






}

