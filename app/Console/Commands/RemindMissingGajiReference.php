<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendNotification;
use Illuminate\Support\Facades\DB;

class RemindMissingGajiReference extends Command
{
    protected $signature = 'morin:remind-missing-gaji-reference';
    protected $description = 'Send reminder to responsible user when current month gaji or uang makan or tukin reference is missing';

    public function handle()
    {
        $today = now();

        if ($today->day <= 1) {
            return 0;
        }

        $hasGajiReference = $this->hasReferenceWithKeyword('gaji');
        $hasUangMakanReference = $this->hasReferenceWithKeyword('uang makan');
        $hasTukinReference = $this->hasReferenceWithKeyword('tukin') || $this->hasReferenceWithKeyword('tunjangan kinerja');

        $shouldRemindForGaji = !$hasGajiReference && $today->day > 1;
        $shouldRemindForUangMakan = !$hasUangMakanReference && $today->day > 10;
        $shouldRemindForTukin = !$hasTukinReference && $today->day > 10;

        if (!$shouldRemindForGaji && !$shouldRemindForUangMakan && !$shouldRemindForTukin) {
            return 0;
        }

        $responsiblePhone = $this->getResponsiblePhoneNumber();
        if (empty($responsiblePhone)) {
            Log::warning('No responsible phone number found for missing reference reminder.');
            return 0;
        }

        $messages = $this->buildMessages($today, $shouldRemindForGaji, $shouldRemindForUangMakan, $shouldRemindForTukin);

        foreach ($messages as $index => $message) {
            $details = [
                'message' => $message,
                'no_hp' => $responsiblePhone,
                'id' => null,
            ];

            //delay antar pesan reminder pembuatan notifikasi pembayaran gaji, uang makan, dan tukin agar tidak dikirim bersamaan
            $delay = (DB::table('jobs')->count() * 10) + ($index * 15); $queue = new SendNotification($details);

            dispatch($queue->delay($delay));
        }

        return 0;
    }

    private function hasReferenceWithKeyword($keyword)
    {
        return DB::table('referensi_pembayaran')
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->get()
            ->contains(function ($item) use ($keyword) {
                return stripos($item->nama_pembayaran ?? '', $keyword) !== false;
            });
    }

    private function buildMessages($today, $shouldRemindForGaji, $shouldRemindForUangMakan, $shouldRemindForTukin)
    {
        $messages = [];

        if ($shouldRemindForGaji) {
            $messages[] = "*Morin : Peringatan Pembuatan Notifikasi Pembayaran Gaji Belum Dibuat*.
Saat ini tanggal {$today->translatedFormat('d F Y')}.
Referensi pembayaran Gaji untuk bulan {$today->translatedFormat('F')} tahun {$today->year} belum tersedia.
Mohon penanggung jawab menindaklanjuti segera agar data pembayaran dapat diproses.

_Pesan ini dikirimkan oleh Aplikasi *Morin (Money Reminder)* sebagai Aplikasi Notifikasi Keuangan BPS Kota Padang Panjang pada {$today->format('d-m-y H:i:s')} WIB_";
        }

        if ($shouldRemindForUangMakan) {
            $messages[] = "*Morin : Peringatan Pembuatan Notifikasi Pembayaran Uang Makan Belum Dibuat*.
Saat ini tanggal {$today->translatedFormat('d F Y')}.
Referensi pembayaran Uang Makan untuk bulan {$today->translatedFormat('F')} tahun {$today->year} belum tersedia.
Mohon penanggung jawab menindaklanjuti segera agar data pembayaran dapat diproses.

_Pesan ini dikirimkan oleh Aplikasi *Morin (Money Reminder)* sebagai Aplikasi Notifikasi Keuangan BPS Kota Padang Panjang pada {$today->format('d-m-y H:i:s')} WIB_";
        }

        if ($shouldRemindForTukin) {
            $messages[] = "*Morin : Peringatan Pembuatan Notifikasi Pembayaran Tunjangan Kinerja/Tukin Belum Dibuat*.
Saat ini tanggal {$today->translatedFormat('d F Y')}.
Referensi pembayaran Tunjangan Kinerja/Tukin untuk bulan {$today->translatedFormat('F')} tahun {$today->year} belum tersedia.
Mohon penanggung jawab menindaklanjuti segera agar data pembayaran dapat diproses.

_Pesan ini dikirimkan oleh Aplikasi *Morin (Money Reminder)* sebagai Aplikasi Notifikasi Keuangan BPS Kota Padang Panjang pada {$today->format('d-m-y H:i:s')} WIB_";
        }

        return $messages;
    }

    private function getResponsiblePhoneNumber()
    {
        $user = DB::table('users')->find(4);
        if ($user) {
            foreach (['no_hp', 'phone', 'phone_number', 'hp'] as $field) {
                if (!empty($user->{$field})) {
                    return $user->{$field};
                }
            }
        }

        $pegawai = DB::table('pegawai')->where('id', 4)->first();
        if ($pegawai && !empty($pegawai->no_hp)) {
            return $pegawai->no_hp;
        }

        return null;
    }
}
