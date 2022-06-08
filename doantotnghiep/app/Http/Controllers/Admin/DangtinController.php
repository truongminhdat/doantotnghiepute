<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Dangtin;
use App\Models\Danhgia;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\Count;

class DangtinController extends Controller
{
    public function index()
    {
        $dangtin = Dangtin::paginate(10);
        return view('admin.dangtin.dangtin', [
            'title' => 'Quản lí Đăng tin'
        ], compact('dangtin'));
    }

    public function duyetbaidang(Request $request)
    {
        $data = $request->all();
        $dangtin = Dangtin::find($data['id']);
        $dangtin->status = $data['status'];
        $dangtin->save();
    }

    public function edit($id)
    {
        $dangtin = Dangtin::find($id);
        $comment = Comment::count();
        $danhgia = Danhgia::count();
        $grate = Danhgia::where('dangtin_id',$id)->avg('grate');
        return view('admin.dangtin.xemdangtin', [
                'title' => 'Xem bài đăng'
            ]
            , compact('dangtin', 'comment', 'danhgia','grate'));
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        $comment->delete();
        return redirect()->route('admin.dangtin')->with('thongbao','Xóa thành công');

    }
}

