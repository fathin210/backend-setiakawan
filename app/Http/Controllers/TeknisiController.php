<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Teknisi;
use Exception;
use Illuminate\Http\Request;

class TeknisiController extends Controller
{
    public function index()
    {
        try {
            $teknisi = Teknisi::all();
            return ResponseFormatter::success($teknisi, "List Data Teknisi");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required'
        ]);

        try {
            $teknisi = Teknisi::create($request->all());
            return ResponseFormatter::success($teknisi, "Berhasil Membuat Data Teknisi Baru");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(Request $request){
        $request->validate([
            'id_teknisi' => 'required',
            'nama' => 'required'
        ]);

        $update = $request->all();

        try {
            $teknisi = Teknisi::find($update['id_teknisi']);
            $teknisi->nama = $update['nama'];
            $teknisi->save();
            return ResponseFormatter::success($teknisi, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data teknisi gagal");
        }
    }

    public function destroy($id_teknisi)
    {
        try {
            $teknisi = Teknisi::find($id_teknisi)->delete();
            return ResponseFormatter::success(null, "Berhasil Menghapus Data Teknisi Dengan Id : ".$id_teknisi);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data Pasien dengan id = $id_teknisi Gagal");
        }
    }
}
