<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Status;
use Exception;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index()
    {
        try {
            $status = Status::all();
            return ResponseFormatter::success($status, "List Data Status");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(Request $request){
        $request->validate([
            'id_status' => 'required',
            'jenis_status' => 'required'
        ]);

        $update = $request->all();

        try {
            $status = Status::find($update['id_status']);
            $status->jenis_status = $update['jenis_status'];
            $status->save();
            return ResponseFormatter::success($status, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data status gagal");
        }
    }

    public function store(Request $request){
        $request->validate([
            'jenis_status' => 'required'
        ]);

        try {
            $status = Status::create($request->all());
            return ResponseFormatter::success($status, "Create Data Status Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Create Data status gagal");
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_status' => 'required'
        ]);

        try {
            $status = Status::destroy($request['id_status']);
            return ResponseFormatter::success($status, "Delete Data Status Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Delete Data status gagal");
        }
    }
}
