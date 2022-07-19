<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\TarifGigi;
use Exception;
use Illuminate\Http\Request;

class TarifGigiController extends Controller
{
    public function index()
    {
        try {
            $tarif_gigi = TarifGigi::all();
            return ResponseFormatter::success($tarif_gigi, "List Data Tarif Gigi");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(Request $request){
        $request->validate([
            'id_tarif' => 'required',
            'tarif_gigi' => 'required|gt:0'
        ]);

        $update = $request->all();

        try {
            $tarif = TarifGigi::find($update['id_tarif']);
            $tarif->tarif_gigi = $update['tarif_gigi'];
            $tarif->save();
            return ResponseFormatter::success($tarif, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data tarif\ gagal");
        }
    }

    public function store(Request $request){
        $request->validate([
            'tarif_gigi' => 'required|gt:0'
        ]);

        try {
            $tarif = TarifGigi::create($request->all());
            return ResponseFormatter::success($tarif, "Create Data tarif Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Create Data tarif gagal");
        }
    }

    public function destroy($id_tarif)
    {
        try {
            $tarif = TarifGigi::find($id_tarif)->delete();
            return ResponseFormatter::success(null, "Delete Data Tarif Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Delete Data Tarif Gagal");
        }
    }
}
