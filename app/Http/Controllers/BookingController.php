<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\BookingNotif;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->validate([
            "tanggal" => "required|date"
        ]);

        $booking = Booking::with('pasien')->where('tanggal',$tanggal)->get();
        return ResponseFormatter::success($booking, "List Data Booking");
    }

    public function showById($id_pasien)
    {
        try{
            $booking = Booking::with('pasien')->where('id_pasien',$id_pasien)->whereDate('tanggal','>=',Carbon::now())->orderBy("tanggal")->get();
            return ResponseFormatter::success($booking, "Data Booking");
        }catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Data Booking Gagal Ditampilkan");
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pasien' => 'required',
            'tanggal' => 'required|date',
            'jam' => 'required'
        ]);

        $today = date('Ymd');
        $formatDate = substr($today, 2, 6);

        $input = $request->all();
        $lastNomor = Booking::select('nomor_booking')->orderByDesc('id_booking')->first();

        $bookingPerTanggal = Booking::where("tanggal","=", $input['tanggal'])->selectSub("COUNT(*)","jumlah_booking")->get(); 
        try {
            if($input['tanggal'] == Carbon::now()->toDateString()){
            if ($lastNomor === null) {
                $booking = Booking::create(array_replace($input,['nomor_booking' => "B".$formatDate."001"]));
                return ResponseFormatter::success($booking, "Tambah Data Booking Sukses");
            }
            $checkLatest = substr($lastNomor->nomor_booking, 1, 6);
            if ($checkLatest == $formatDate) {
                $lastNomor->nomor_booking = (int)substr($lastNomor->nomor_booking, 1) + 1; 
                $booking = Booking::create(array_replace($input, ['nomor_booking' => "B".$lastNomor->nomor_booking]));
            } else {
                $booking = Booking::create(array_replace($input,['nomor_booking' => "B".$formatDate."001"]));
            }
            if($bookingPerTanggal[0]->jumlah_booking > 5){
                return ResponseFormatter::error(null, "Sudah memenuhi batas kuota hari ini");
        }
        } else{
                if ($lastNomor === null) {
                    $booking = Booking::create(array_replace($input,['nomor_booking' => "B".$formatDate."001"]));
                    return ResponseFormatter::success($booking, "Tambah Data Booking Sukses");
                }
                $checkLatest = substr($lastNomor->nomor_booking, 1, 6);
                if ($checkLatest == $formatDate) {
                    $lastNomor->nomor_booking = (int)substr($lastNomor->nomor_booking, 1) + 1; 
                    $booking = Booking::create(array_replace($input, ['nomor_booking' => "B".$lastNomor->nomor_booking]));
                } else {
                    $booking = Booking::create(array_replace($input,['nomor_booking' => "B".$formatDate."001"]));
                }
                if($bookingPerTanggal[0]->jumlah_booking > 5){
                    return ResponseFormatter::error(null, "Sudah memenuhi batas kuota hari ini");
                }
            }
        return ResponseFormatter::success($booking, "Tambah Data Booking Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Tambah Data Booking Gagal');
        }
    }

    public function update(Request $request, $id_booking)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam' => 'required'
        ]);

        $update = $request->all();
        try {
            $booking = Booking::find($id_booking);
            $booking->tanggal = $update['tanggal'];
            $booking->jam = $update['jam'];
            $booking->save();
            return ResponseFormatter::success($booking, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "error");
        }
    }

    public function destroy($id_booking)
    {
        try {
            $booking = Booking::destroy($id_booking);
            return ResponseFormatter::success(null, "Delete Data Booking Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Delete Data Booking gagal");
        }
    }

    public function batal(Request $request){
        $request->validate([
            'id_booking' => 'required',
            'alasan_batal' => 'required' 
        ]);

        $input = $request->all();

        try {
            $booking = Booking::find($input['id_booking']);
            $booking->alasan_batal = $input['alasan_batal'];
            $booking->update();
            $booking->delete();

            return ResponseFormatter::success($booking, "Booking Dibatalkan");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Terdapat Kesalahan");
        }
    }

    public function showBookingBatal(Request $request){
        $request->validate([
            'bulan'
        ]);
        $input = $request->all();
        $bulan = substr($input['bulan'], 5);
        $tahun = substr($input['bulan'], 0, 4);
        try{
            $lainLain = Booking::onlyTrashed()->whereMonth("tanggal","=",$bulan)->whereYear("tanggal","=",$tahun)->where("alasan_batal","Lain-lain")->selectSub("COUNT(*)","jumlah_pasien")->get();
            $tidakMemberitahu = Booking::onlyTrashed()->whereMonth("tanggal","=",$bulan)->whereYear("tanggal","=",$tahun)->where("alasan_batal","Tidak ingin memberitahu")->selectSub("COUNT(*)","jumlah_pasien")->get();
            $keperluan = Booking::onlyTrashed()->whereMonth("tanggal","=",$bulan)->whereYear("tanggal","=",$tahun)->where("alasan_batal","Keperluan Mendesak")->selectSub("COUNT(*)","jumlah_pasien")->get();
            return ResponseFormatter::success(["lain" => $lainLain, "tidakMemberitahu" => $tidakMemberitahu, "keperluan" => $keperluan], "List Data Booking");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "List Data Booking Gagal Diambil");
        }
    }
}
