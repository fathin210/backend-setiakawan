<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Pelayanan;
use Exception;
use Illuminate\Http\Request;

class PelayananController extends Controller
{
    public function index()
    {
        try {
            $pelayanan = Pelayanan::all();
            return ResponseFormatter::success($pelayanan, "List Data Pelayanan");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(Request $request){
        $request->validate([
            'id_pelayanan' => 'required',
            'jenis_pelayanan' => 'required'
        ]);

        $update = $request->all();

        try {
            $pelayanan = Pelayanan::find($update['id_pelayanan']);
            $pelayanan->jenis_pelayanan = $update['jenis_pelayanan'];
            $pelayanan->save();
            return ResponseFormatter::success($pelayanan, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data pelayanan gagal");
        }
    }

    public function store(Request $request){
        $request->validate([
            'jenis_pelayanan' => 'required'
        ]);

        try {
            $pelayanan = Pelayanan::create($request->all());
            return ResponseFormatter::success($pelayanan, "Create Data pelayanan Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Create Data pelayanan gagal");
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_pelayanan' => 'required'
        ]);

        try {
            $pelayanan = Pelayanan::destroy($request['id_pelayanan']);
            return ResponseFormatter::success($pelayanan, "Delete Data pelayanan Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Delete Data pelayanan gagal");
        }
    }
}
