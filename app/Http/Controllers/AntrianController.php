<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Antrian;
use App\Models\TarifGigi;
use Exception;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;
use Codedge\Fpdf\Fpdf\Fpdf;

class AntrianController extends Controller
{
    protected $fpdf;
 
    public function __construct()
    {
        $this->fpdf = new Fpdf;
    }
    public function store(Request $request){
        $request->validate([
            'id_pasien' => 'required',
            'id_admin' => 'required',
            'tanggal_pelaksanaan' => 'required|date',
        ]);

        $today = date('Ymd');
        $formatDate = substr($today, 2,6);

        $input = $request->all();
        $lastNomor = Antrian::select('nomor_pendaftaran')->orderByDesc('nomor_pendaftaran')->first();

        try {
            if($lastNomor === null){
                $pendaftaran = Antrian::create($input + ['nomor_pendaftaran' => "A".$formatDate."001" ]);
                return ResponseFormatter::success($pendaftaran, "Create New Data Antrian Sukses");
            }    
            $checkLatest = substr($lastNomor->nomor_pendaftaran, 1, 6);

            if($checkLatest == $formatDate){
                $lastNomor->nomor_pendaftaran = (int)substr($lastNomor->nomor_pendaftaran, 1) + 1; 
                $pendaftaran = Antrian::create($input + ['nomor_pendaftaran' => "A".$lastNomor->nomor_pendaftaran ]);
            }else {
                $pendaftaran = Antrian::create($input + ['nomor_pendaftaran' => "A".$formatDate."001" ]);
            }
            
            return ResponseFormatter::success($pendaftaran, "Create New Data Antrian Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Create New Data Antrian Gagal');
        }
    }

    public function index(Request $request)
    {
        $tanggal_pelaksanaan = $request->validate([
            "tanggal_pelaksanaan" => "required|date"
        ]);
        
        try {
            $antrian = Antrian::with('admin')->with('teknisi')->with('pasien')->with('status')->with('pelayanan')->with('tarif')->where("tanggal_pelaksanaan","=",$tanggal_pelaksanaan)->orderBy("id_status")->get(); 
            return ResponseFormatter::success($antrian);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            "id_status" => "required",
            "id_antrian" => "required"
        ]);

        $update = $request->all();

        try {
            $antrian = Antrian::find($update['id_antrian']);
            $antrian->id_status = $update['id_status'];
            if($update['id_status'] == '4'){
                $antrian->id_teknisi = null;
                $antrian->id_pelayanan = null;
                $antrian->id_tarif = null;
                $antrian->jumlah_gigi = null;
                $antrian->total_biaya = null;
            }
            $antrian->save();
            return ResponseFormatter::success($antrian, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "error");
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_antrian' => 'required',
            'id_teknisi' => 'required',
            'id_pelayanan' => 'required',
            'jumlah_gigi' => 'required|numeric'
        ]);

        $update = $request->all();

        try {
            $antrian = Antrian::find($update['id_antrian']);
            $antrian->id_teknisi = $update['id_teknisi'];
            $antrian->id_pelayanan = $update['id_pelayanan'];
            $antrian->jumlah_gigi = $update['jumlah_gigi'];
            if($antrian->id_pelayanan == 1 ){
                $tarif = TarifGigi::select("tarif_gigi")->where('id_tarif','=',$update['id_tarif'])->first();
                $antrian->id_tarif = $update['id_tarif'];
                $antrian->total_biaya = ($tarif->tarif_gigi * (int)$update['jumlah_gigi']) + (10000 * (int)$update['jumlah_gigi']);
            }else if($antrian->id_pelayanan == 2 ){
                $antrian->total_biaya = 10000 * (int)$update['jumlah_gigi'];
            }
            $antrian->id_status = 3;
            $antrian->save();
            return ResponseFormatter::success($antrian, "Update Data Sukses");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "error");
        }
    }
    
    public function getPendaftaranOneWeek()
    {
        try {
           
           $tanggal = Antrian::where('tanggal_pelaksanaan', '>=', Carbon::now()->startOfMonth())
           ->where('tanggal_pelaksanaan', '<', Carbon::now()->endOfMonth())->groupBy("tanggal_pelaksanaan")
           ->select("tanggal_pelaksanaan")->selectSub("COUNT(*)","jumlah_pasien")->get();

           return ResponseFormatter::success(["tanggal" => $tanggal], "List Data Antrian");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "error");
        }
    }

    public function getPendaftaranOneDay()
    {        

        try {
            $total = Antrian::where('tanggal_pelaksanaan', '=', Carbon::now()->toDateString())->groupBy("tanggal_pelaksanaan")
            ->select("tanggal_pelaksanaan")->selectSub("COUNT(*)","jumlah_pasien")->get();

            $menunggu = Antrian::where('tanggal_pelaksanaan', '=', Carbon::now()->toDateString())->where('id_status', '=', 1)
            ->get()->groupBy("tanggal_pelaksanaan","id_status");

            $ditangani = Antrian::where('tanggal_pelaksanaan', '=', Carbon::now()->toDateString())->where('id_status', '=', 2)->groupBy("tanggal_pelaksanaan","id_status")
            ->select("tanggal_pelaksanaan","id_status")->selectSub("COUNT(*)","jumlah_pasien")->get();

            $selesai = Antrian::where('tanggal_pelaksanaan', '=', Carbon::now()->toDateString())->where('id_status', '=', 3)->groupBy("tanggal_pelaksanaan","id_status")
            ->select("tanggal_pelaksanaan","id_status")->selectSub("COUNT(*)","jumlah_pasien")->get();

            $batal = Antrian::where('tanggal_pelaksanaan', '=', Carbon::now()->toDateString())->where('id_status', '=', 4)->groupBy("tanggal_pelaksanaan","id_status")
            ->select("tanggal_pelaksanaan","id_status")->selectSub("COUNT(*)","jumlah_pasien")->get();
            return ResponseFormatter::success(["total" => $total,"menunggu" => $menunggu,"ditangani" => $ditangani,"selesai" => $selesai, "batal" => $batal], "List Data Antrian");
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), "error");
        }
    }

    public function cetakKwitansi($id_antrian)
    {
        try {
            $antrian = Antrian::with('pasien')->with('teknisi')->with('pelayanan')->find($id_antrian);
    
            $this->fpdf->SetAutoPageBreak(false);
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->AddPage();

            $this->fpdf->SetFillColor(255,255,255); 
            $this->fpdf->Sety(15); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'',0,0,'C',1); 
            $this->fpdf->Sety(20); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'BALAI PEMASANGAN GIGI',0,0,'C',1);
            $this->fpdf->Sety(25); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'SETIA KAWAN',0,0,'C',1);
            $this->fpdf->Sety(30); 
            $this->fpdf->SetX(10); 
            $this->fpdf->Cell(125,5,'---------------------------------------------------------------------------------------',0,0,'L',1); 
            $this->fpdf->Sety(35); 
            $this->fpdf->SetFont('Arial','B',12);
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'KWITANSI',0,0,'C',1);
            $this->fpdf->SetFont('Arial','I',12);
            $this->fpdf->Sety(40); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'#SK'.''.$antrian->nomor_pendaftaran,0,0,'C',1);
            $this->fpdf->SetFont('Arial','',10);
            $this->fpdf->Sety(47); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'  '.'Dicetak oleh :'.' '.$antrian->admin->nama.', ',0,0,'L',1);
            $this->fpdf->Sety(55); 
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'  '.'Nomor Pasien',0,0,'L',1);
            $this->fpdf->Sety(55); 
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,': '.$antrian->pasien->nomor_pasien,0,0,'L',1);
            $this->fpdf->Sety(60); 
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'  '.'Sudah terima dari',0,0,'L',1);
            $this->fpdf->Sety(60); 
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,': '.$antrian->pasien->nama,0,0,'L',1);
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->Sety(75); 
            $this->fpdf->SetX(10);
            $this->fpdf->Cell(125,5,'  '.'Untuk Pembayaran',0,0,'L',1);
            $this->fpdf->Sety(75); 
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,': '.' '.'1. '.'Pemasangan',0,0,'L',1);
            $this->fpdf->Sety(75); 
            $this->fpdf->SetX(91);
            if($antrian->id_pelayanan == 1){
                $this->fpdf->Cell(35,5,$antrian->total_biaya.''.',-',0,0,'R',1);
            }else{
                $this->fpdf->Cell(35,5,'0'.''.',-',0,0,'R',1);
            }
            $this->fpdf->Sety(75); 
            $this->fpdf->SetX(90);
            $this->fpdf->Cell(6,5,'Rp.',0,0,'L',1);
            $this->fpdf->Sety(80); 
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,'  '.' '.'2. '.'Perbaikan',0,0,'L',1);
            $this->fpdf->Sety(80); 
            $this->fpdf->SetX(91);
            if($antrian->id_pelayanan == 2){
                $this->fpdf->Cell(35,5,$antrian->total_biaya.''.',-',0,0,'R',1);
            }else{
                $this->fpdf->Cell(35,5,'0'.''.',-',0,0,'R',1);
            }
            $this->fpdf->Sety(80); 
            $this->fpdf->SetX(90);
            $this->fpdf->Cell(6,5,'Rp.',0,0,'L',1);
            $this->fpdf->Sety(85); 
            $this->fpdf->SetX(91);
            $this->fpdf->Cell(35,2,'-------------------------',0,0,'R',1);
            $this->fpdf->SetFont('Arial','B',12);
            $this->fpdf->Sety(88); 
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,'  '.' '.'Jumlah',0,0,'L',1);
            $this->fpdf->Sety(88); 
            $this->fpdf->SetX(91);
            $this->fpdf->Cell(35,5,$antrian->total_biaya.''.',-',0,0,'R',1);
            $this->fpdf->Sety(88); 
            $this->fpdf->SetX(90);
            $this->fpdf->Cell(6,5,'Rp.',0,0,'L',1);
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->Sety(100); 
            $this->fpdf->SetX(20);
            $this->fpdf->Cell(125,5,'Teknisi :',0,0,'L',1);
            $this->fpdf->Sety(115); 
            $this->fpdf->SetX(20);
            $this->fpdf->Cell(35,5,$antrian->teknisi->nama,0,0,'L',1);
            $this->fpdf->SetFont('Arial','',12);
            $this->fpdf->Sety(100); 
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,'                         '.'Sidoarjo,'.' '.Carbon::now()->toDateString(),0,0,'L',1);
            $this->fpdf->Sety(115); 
            $this->fpdf->SetX(51);
            $this->fpdf->Cell(35,5,'                         '.$antrian->admin->nama,0,0,'L',1);
            $this->fpdf->Output();
            exit;
    } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
    }
    }
}
