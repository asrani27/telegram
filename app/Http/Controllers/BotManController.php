<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Informasi;
use BotMan\BotMan\BotMan;
use App\Models\Percakapan;
use Illuminate\Http\Request;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;

class BotManController extends Controller
{
    public function handle()
    {
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);
        $config = [
            // Your driver-specific configuration
            "telegram" => [
                "token" => "5970255835:AAHHqIhF2W-Hyoboe2EaQ4URjmOFTXDRv-A"
            ]
        ];
        $botman = BotManFactory::create($config, new LaravelCache());

        $botman->hears('/start|start', function (BotMan $bot) {
            $user = $bot->getUser();
            $bot->reply('Hi ' . $user->getFirstName() . ', Selamat datang di Bot Sistem!, Ketik "Mulai" Untuk memulai percakapan ');
        });

        $botman->hears('/mulai|mulai', function (BotMan $bot) {
            $bot->ask('Apakah anda ingin melakukan perubahan data absensi? Y / T ', function (Answer $answer) {
                $jawab = $answer->getText();
                if ($jawab == "Y" || $jawab == "y") {
                    $this->ask('Ketik NIP tanpa spasi?', function (Answer $answer) {
                        $nip = $answer->getText();
                        $checkNIP = Pegawai::where('nip', $nip)->first();
                        if ($checkNIP == null) {
                            $this->say('Data Pegawai Tidak Ditemukan');
                        } else {
                            $this->say('NIP : ' . $checkNIP->nip . PHP_EOL . 'Nama : ' . $checkNIP->nama);

                            $this->ask('Ketik Tanggal Yang ingin diperbaiki Dengan Format dd-mm-yyyy, Contoh : 01-05-2023 ?', function (Answer $answer) use ($nip, $checkNIP) {
                                $tanggal = $answer->getText();
                                $hari = Carbon::parse($tanggal)->translatedFormat('l');
                                $checkPresensi = Presensi::where('tanggal', Carbon::parse($tanggal)->format('Y-m-d'))->where('nip', $nip)->first();

                                if ($checkPresensi == null) {
                                    $this->say('Presensi Pada Tanggal ' . $tanggal . ' Dengan NIP : ' . $nip . ' Tidak ditemukan');
                                } else {
                                    $this->say('Tanggal : ' . $tanggal . PHP_EOL . 'NIP : ' . $nip . PHP_EOL . 'Nama : ' . $checkNIP->nama . PHP_EOL . 'Jam Masuk : ' . Carbon::parse($checkPresensi->jam_masuk)->format('H:i') . PHP_EOL . 'Jam Pulang : ' . Carbon::parse($checkPresensi->jam_pulang)->format('H:i') . PHP_EOL . 'Telat : ' . ($checkPresensi->terlambat == null ? 0 . ' Menit' : $checkPresensi->terlambat . ' Menit') . PHP_EOL . 'Lebih Awal : ' . ($checkPresensi->lebih_awal == null ? 0  . ' Menit' : $checkPresensi->lebih_awal . ' Menit'));


                                    $this->ask('Ketik "MASUK" untuk perbaikan data Masuk dan "PULANG" untuk perbaikan data pulang?', function (Answer $answer) use ($nip, $checkNIP, $checkPresensi) {
                                        $jawab = $answer->getText();
                                        if ($jawab == "MASUK" || $jawab == "masuk") {
                                            //$this->say($checkPresensi);
                                            // $update = $checkPresensi;
                                            // $update->jam_masuk = $checkPresensi->tanggal . ' 07:' . rand(20, 30) . ':' . rand(1, 60);
                                            // $update->terlambat = 0;
                                            // $update->save();
                                            $this->say('Berhasil Di Update, Berikut Data Terbaru : ');
                                            // $this->say('Tanggal : ' . $checkPresensi->tanggal . PHP_EOL . 'NIP : ' . $nip . PHP_EOL . 'Nama : ' . $checkNIP->nama . PHP_EOL . 'Jam Masuk : ' . Carbon::parse($checkPresensi->jam_masuk)->format('H:i') . PHP_EOL . 'Jam Pulang : ' . Carbon::parse($checkPresensi->jam_pulang)->format('H:i') . PHP_EOL . 'Telat : ' . ($checkPresensi->terlambat == null ? 0 . ' Menit' : $checkPresensi->terlambat . ' Menit') . PHP_EOL . 'Lebih Awal : ' . ($checkPresensi->lebih_awal == null ? 0  . ' Menit' : $checkPresensi->lebih_awal . ' Menit'));
                                        } elseif ($jawab == "PULANG" || $jawab == "pulang") {

                                            //$this->say($checkPresensi);
                                            // $update = $checkPresensi;
                                            // $update->jam_masuk = $checkPresensi->tanggal . ' 17:' . rand(1, 11) . ':' . rand(1, 60);
                                            // $update->lebih_awal = 0;
                                            // $update->save();
                                            $this->say('Berhasil Di Update, Berikut Data Terbaru : ');
                                            // $this->say('Tanggal : ' . $checkPresensi->tanggal . PHP_EOL . 'NIP : ' . $nip . PHP_EOL . 'Nama : ' . $checkNIP->nama . PHP_EOL . 'Jam Masuk : ' . Carbon::parse($checkPresensi->jam_masuk)->format('H:i') . PHP_EOL . 'Jam Pulang : ' . Carbon::parse($checkPresensi->jam_pulang)->format('H:i') . PHP_EOL . 'Telat : ' . ($checkPresensi->terlambat == null ? 0 . ' Menit' : $checkPresensi->terlambat . ' Menit') . PHP_EOL . 'Lebih Awal : ' . ($checkPresensi->lebih_awal == null ? 0  . ' Menit' : $checkPresensi->lebih_awal . ' Menit'));
                                        } else {
                                            $this->say('Keyword yang di masukkan salah, silahkan ketik Mulai');
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    $this->say('Terima Kasih Telah Berkunjung');
                }
            });
        });

        $botman->listen();
    }

    /**
     * Place your BotMan logic here.
     */

    public function askName($botman)
    {
        $user = $botman->getUser();
        $info = Informasi::get();
        $botman->ask('Halo, Dengan siapa saya berbicara?', function (Answer $answer) use ($info, $user) {
            $name = $answer->getText();

            $percakapan_baru = new Percakapan;
            $percakapan_baru->chat_id = $user->getId();
            $percakapan_baru->response_bot = 'Halo, Dengan siapa saya berbicara?';
            $percakapan_baru->response_user = $name;
            $percakapan_baru->save();

            $this->say('Senang bertemu Dengan mu ' . $name);

            $percakapan_baru = new Percakapan;
            $percakapan_baru->chat_id = $user->getId();
            $percakapan_baru->response_bot = 'Senang bertemu Dengan mu ' . $name;
            $percakapan_baru->response_user = null;
            $percakapan_baru->save();

            $this->ask('apa email anda?', function (Answer $answer) use ($name, $info, $user) {
                $email = $answer->getText();

                $percakapan_baru = new Percakapan;
                $percakapan_baru->chat_id = $user->getId();
                $percakapan_baru->response_bot = 'apa email anda?';
                $percakapan_baru->response_user = $email;
                $percakapan_baru->save();

                $this->say('Terima kasih ' . $name);

                $percakapan_baru = new Percakapan;
                $percakapan_baru->chat_id = $user->getId();
                $percakapan_baru->response_bot = 'Terima kasih ' . $name;
                $percakapan_baru->response_user = null;
                $percakapan_baru->save();

                $percakapan_baru = new Percakapan;
                $percakapan_baru->chat_id = $user->getId();
                $percakapan_baru->response_bot = 'Silahkan ketik Nomor informasi di bawah Ini :';
                $percakapan_baru->response_user = null;
                $percakapan_baru->save();

                $this->say('Silahkan ketik Nomor informasi di bawah Ini :');
                foreach ($info as $item) {
                    $this->say($item->no . '. ' . $item->parameter);

                    $percakapan_baru = new Percakapan;
                    $percakapan_baru->chat_id = $user->getId();
                    $percakapan_baru->response_bot = $item->no . '. ' . $item->parameter;
                    $percakapan_baru->response_user = null;
                    $percakapan_baru->save();
                }
            });
        });
    }
}
