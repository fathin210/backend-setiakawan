<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TarifGigiController;
use App\Http\Controllers\TeknisiController;
use Illuminate\Support\Facades\Route;

Route::get("/pasien/cetak/{id_pasien}", [PasienController::class, "cetakKartu"]);
Route::get("/kwitansi/cetak/{id_antrian}", [AntrianController::class, "cetakKwitansi"]);
Route::post("/admin/login", [AdminController::class, "login"]);
Route::post("/admin/register", [AdminController::class, "register"]);
Route::get('/notif', [BookingController::class, "send"]);
Route::get("/info-antrian/day", [AntrianController::class, "getPendaftaranOneDay"]);

Route::group(['prefix' => 'admin', 'middleware' => ['assign.guard:admin', 'jwt.auth']], function () {

    Route::get("/admin", [AdminController::class, "index"]);
    // ini endpoint auth admin
    Route::post("/logout", [AdminController::class, "logout"]);
    Route::get("/profile", [AdminController::class, "adminProfile"]);

    // Ini endpoint Teknisi
    Route::get("/teknisi", [TeknisiController::class, "index"]);
    Route::post("/teknisi", [TeknisiController::class, "store"]);
    Route::put("/teknisi", [TeknisiController::class, "update"]);
    Route::delete("/teknisi/{id_teknisi}", [TeknisiController::class, "destroy"]);

    // ini endpoint Pasien
    Route::get("/pasien/cari", [PasienController::class, "search"]);
    Route::get("/pasien/{id_pasien}", [PasienController::class, "showDetail"]);
    Route::delete("/pasien/{id_pasien}", [PasienController::class, "destroy"]);
    Route::put("/pasien/{id_pasien}", [PasienController::class, "update"]);
    Route::post("/pasien", [PasienController::class, "store"]);
    Route::get("/pasien", [PasienController::class, "index"]);

    // ini endpoint Pendaftaran
    Route::get("/info-antrian/week", [AntrianController::class, "getPendaftaranOneWeek"]);
    Route::post("/antrian", [AntrianController::class, "store"]);
    Route::put("/antrian", [AntrianController::class, "update"]);
    Route::put("/antrian/status", [AntrianController::class, "updateStatus"]);
    Route::post("/antrian/tanggal", [AntrianController::class, "index"]);

    // ini endpoint status
    Route::get("/status", [StatusController::class, "index"]);
    Route::put("/status", [StatusController::class, "update"]);
    Route::post("/status", [StatusController::class, "store"]);
    Route::delete("/status", [StatusController::class, "destroy"]);

    // ini endpoint tarif
    Route::get("/tarif", [TarifGigiController::class, "index"]);
    Route::post("/tarif", [TarifGigiController::class, "store"]);
    Route::put("/tarif", [TarifGigiController::class, "update"]);
    Route::delete("/tarif/{id_tarif}", [TarifGigiController::class, "destroy"]);

    // ini endpoint pelayanan
    Route::get("/pelayanan", [PelayananController::class, "index"]);
    Route::post("/pelayanan", [PelayananController::class, "store"]);
    Route::put("/pelayanan", [PelayananController::class, "update"]);
    Route::delete("/pelayanan", [PelayananController::class, "store"]);

    // Ini Endpoint Booking
    Route::post("/booking", [BookingController::class, 'index']);
    Route::delete("/booking/{id_booking}", [BookingController::class, 'destroy']);
    Route::post("/booking/laporan", [BookingController::class, 'showBookingBatal']);
});

Route::post("/pasien/login", [PasienController::class, "login"]);
Route::put("/pasien/password", [PasienController::class, "updatePassword"]);

Route::group(['prefix' => 'pasien', 'middleware' => ['assign.guard:pasien', 'jwt.auth']], function () {
    Route::post("/logout", [PasienController::class, "logout"]);
    Route::get("/profile", [PasienController::class, "pasienProfile"]);
    Route::get("/detail/{id_pasien}", [PasienController::class, "showDetail"]);
    Route::put("/profile/{id_pasien}", [PasienController::class, "update"]);

    Route::post("/booking", [BookingController::class, 'store']);
    Route::put("/booking/{id_booking}", [BookingController::class, 'update']);
    Route::post("/booking/batal", [BookingController::class, 'batal']);
    Route::get("/booking/pasien/{id_pasien}", [BookingController::class, 'showById']);
});
