<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Pasien;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDF;
use Tymon\JWTAuth\Facades\JWTAuth;
use Codedge\Fpdf\Fpdf\Fpdf;

class PasienController extends Controller
{
    protected $fpdf;
    public function __construct()
    {
        $this->fpdf = new Fpdf;
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth("pasien")->factory()->getTTL() * 60,
            'pasien' => auth("pasien")->user(),
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_pasien' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return ResponseFormatter::error($validator->errors(), "Login Pasien Gagal", 422);
        }

        $pasien = Pasien::where('nomor_pasien', $request->nomor_pasien)->first();
        if($pasien === null){
            return ResponseFormatter::error(['error' => 'Akun Tidak Ditemukan'], "Login Pasien Gagal", 401);
        }

        if($pasien['password'] === null){
            return ResponseFormatter::error(['error' => 'Password Belum Diatur'], "Login Pasien Gagal", 401);
        }

        if(!Hash::check($request->password, $pasien->password)){
            return ResponseFormatter::error(['error' => 'Periksa Kembali Data Anda'], "Login Pasien Gagal", 401);
        }

        if(!$token = JWTAuth::fromUser($pasien)){
            return ResponseFormatter::error(['error' => 'Unauthorized'], "Login Pasien Gagal", 401);
        }
        return $this->createNewToken($token);
    }

    public function logout()
    {
        auth('pasien')->logout();
        return response()->json(['message' => 'Pasien successfully signed out']);
    }

    public function pasienProfile()
    {
        return ResponseFormatter::success(auth("pasien")->user(), "List Data Admin");
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'no_telepon' => 'required|digits_between:10,13',
        ]);

        $today = date('Ymd');
        $formatDate = substr($today, 2, 6);

        $input = $request->all();
        $lastNomor = Pasien::select('nomor_pasien')->orderByDesc('id_pasien')->first();
        try {
            if ($lastNomor === null) {
                $pasien = Pasien::create($input + ['nomor_pasien' => "P".$formatDate . "001"]);
                return ResponseFormatter::success($pasien, "Create New Data Pasien Sukses");
            }
            $checkLatest = substr($lastNomor->nomor_pasien, 1, 6);
            if ($checkLatest == $formatDate) {
                $lastNomor->nomor_pasien = (int)substr($lastNomor->nomor_pasien, 1) + 1; 
                $pasien = Pasien::create($input + ['nomor_pasien' => "P".$lastNomor->nomor_pasien]);
            } else {
                $pasien = Pasien::create($input + ['nomor_pasien' => "P".$formatDate."001"]);
            }
            return ResponseFormatter::success($pasien, "Create New Data Pasien Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Create New Data Pasien Gagal');
        }
    }

    public function index()
    {
        try {
            $pasien = Pasien::paginate(100);
            return ResponseFormatter::success($pasien);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Kesalahan");
        }
    }

    public function search(Request $request)
    {
        $search = $request->query("search");
        try {
            $pasien = Pasien::where([['nama', 'LIKE', "$search%"]])->orWhere([['nomor_pasien', 'LIKE', "$search%"]])->simplePaginate(25);
            return ResponseFormatter::success($pasien);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function showDetail($id_pasien)
    {
        try {
            $pasien = Pasien::with(array('pendaftaran' => function($query){
                $query->with('teknisi')->with('pelayanan')->with('status')->with('tarif')->where('id_status',3);
            }))->where('id_pasien',$id_pasien)->first();
            
            return ResponseFormatter::success($pasien);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(Request $request, $id_pasien)
    {
        $request->validate([
            'nama' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'no_telepon' => 'required|digits_between:10,13',
        ]);

        $update = $request->all();

        try {
            $pasien = Pasien::find($id_pasien);
            $pasien->nama = $update['nama'];
            $pasien->jenis_kelamin = $update['jenis_kelamin'];
            $pasien->alamat = $update['alamat'];
            $pasien->no_telepon = $update['no_telepon'];
            $pasien->save();

            return ResponseFormatter::success($pasien, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data Pasien dengan id = $id_pasien Gagal");
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'nomor_pasien' => 'required',
            'nama' => 'required',
            'password' => 'required',
            'konfirmasi_password' => 'required',
        ]);
        
        if($request['password'] !== $request['konfirmasi_password']){
            return ResponseFormatter::error(null, "Field Password dan Konfirmasi Password Tidak Sama");
        }

        $update = $request->all();

        try {
            $pasien = Pasien::where("nomor_pasien",$update['nomor_pasien'])->first();
            if($pasien == null){
            return ResponseFormatter::error($e->getMessage(), "Pastikan Nomor Kartu dan Nama Sesuai Pada Kartu");
            }
            $pasien->nama = $update['nama'];
            $pasien->password = bcrypt($update['password']);
            $pasien->save();

            return ResponseFormatter::success($pasien, "Update Password Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data Password Gagal");
        }
    }

    public function destroy($id_pasien)
    {
        try {
            $pasien = Pasien::destroy($id_pasien);
            return ResponseFormatter::success(null, "Delete Data Pasien dengan id : ".$id_pasien." Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "Update Data Pasien dengan id = $id_pasien Gagal");
        }
    }

    public function cetakKartu($id_pasien)
    {
        try {
            $pasien = Pasien::find($id_pasien);
            $this->fpdf->SetFont('Arial','',10);
            $this->fpdf->AddPage();
            $this->fpdf->Image('images/putus.jpg',7,10,92,60);   
            $this->fpdf->SetFillColor(255,255,255); 
            $this->fpdf->SetFont('Arial','B',11);
            $this->fpdf->Sety(13); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,15,'','LRT',0,'C',1); 
            $this->fpdf->Sety(15);
            $this->fpdf->SetX(15);
            $this->fpdf->Cell(75,5,'KARTU KUNJUNGAN','',0,'C',1); 
            $this->fpdf->SetFont('Arial','',10);
            $this->fpdf->Sety(20); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,5,'BALAI PEMASANGAN GIGI','LR',0,'C',1);
            $this->fpdf->SetFont('Arial','B',11);
            $this->fpdf->Sety(25); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,6,'SETIA KAWAN','LR',0,'C',1);
            $this->fpdf->SetFont('Arial','',11);
            $this->fpdf->Sety(31); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,5,'Jln. Letjen Sutoyo 5 Waru - Sidoarjo','LR',0,'C',1);
            $this->fpdf->Sety(36); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,5,'Telp. 0812-3308-0705','LR',0,'C',1);
            $this->fpdf->Sety(41); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,14,'','LR',0,'C',1);    
            $this->fpdf->image('images/grs.jpg',10,43,85,0.1);
            $this->fpdf->image('images/gigiku.jpg',11,14,11,15);
            $this->fpdf->Sety(45); 
            $this->fpdf->SetFont('Arial','',11);
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,5,'  '.'NO PASIEN','LR',0,'L',1);
            $this->fpdf->SetFont('Arial','',11);
            $this->fpdf->Sety(45); 
            $this->fpdf->SetX(38);
            $this->fpdf->Cell(30,5,': '.$pasien['nomor_pasien'],0,0,'L',1);
            $this->fpdf->SetFont('Arial','',11);
            $this->fpdf->Sety(50); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,5,'  '.'NAMA','LR',0,'L',1);
            $this->fpdf->Sety(50); 
            $this->fpdf->SetX(38);
            $this->fpdf->Cell(27,5,': '.$pasien['nama'],0,0,'L',1);
            $this->fpdf->Sety(55); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,5,'  '.'ALAMAT','LR',0,'L',1);
            $this->fpdf->Sety(55); 
            $this->fpdf->SetX(38);
            $this->fpdf->Cell(27,5,': '.substr($pasien['alamat'],0,24),0,0,'L',1);
            $this->fpdf->SetFont('Arial','B',8);
            $this->fpdf->Sety(60); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,7,'Kartu Ini Harap Dibawa Setiap Kunjungan','LR',0,'C',1);
            $this->fpdf->Sety(67); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(85,0,'','LRB',0,'C',1);
            $this->fpdf->Output();
            exit;
    } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
    }
    }
}
