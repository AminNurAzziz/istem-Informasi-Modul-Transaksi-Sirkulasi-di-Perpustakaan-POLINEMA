<?php

namespace App\Http\Services;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Student;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;
use PgSql\Lob;

class PeminjamanService
{
    public function createPeminjaman(array $bukuPinjam, string $nim)
    {
        $formattedBukuPinjam = [];
        foreach ($bukuPinjam as $buku) {
            $formattedBukuPinjam[] = [
                'id_detail_pinjam' => 'KD-P' . $buku['kode_buku'] . Str::random(3),
                'kode_buku' => $buku['kode_buku'],
                'kode_pinjam' => 'P' . time(),
            ];
        }

        $peminjaman = Peminjaman::create([
            'kode_pinjam' => 'P' . time(),
            'nim' => $nim,
            'tgl_pinjam' => now(),
            'tgl_kembali' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'dipinjam',
        ]);

        // Associate books with the loan
        $peminjaman->buku()->attach($formattedBukuPinjam);

        return [$peminjaman, $formattedBukuPinjam];
    }

    public function generateQRCodes(array $formattedBukuPinjam)
    {
        $qrCodePaths = [];
        foreach ($formattedBukuPinjam as $buku) {
            // Generate QR code
            $qrCodePath = 'qr_code/' . $buku['id_detail_pinjam'] . '.png';
            $fullPath = public_path($qrCodePath);

            $qrCode = QrCode::format('png')->size(300)->generate($buku['id_detail_pinjam']);
            file_put_contents($fullPath, $qrCode);

            // Load the QR code image
            $image = Image::make($fullPath);

            // Resize the image to fit within a 350x350 box
            $image->resize(370, 370, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Create a white canvas with dimensions 370x370 (bigger than image for border effect)
            $whiteCanvasImage = Image::canvas(350, 350, '#ffffff');

            // Calculate x and y coordinates to center the image on the canvas
            $x = ($whiteCanvasImage->width() - $image->width()) / 2;
            $y = ($whiteCanvasImage->height() - $image->height()) / 2;

            // Insert the QR code image in the center of the white canvas
            $whiteCanvasImage->insert($image, 'top-left', $x, $y);

            // Save the final image with the border
            $whiteCanvasImage->save($fullPath);

            // Add the path to the array of QR code paths
            $qrCodePaths[] = $qrCodePath;
        }
        Log::info('QR Code Paths: ' . json_encode($qrCodePaths));
        return $qrCodePaths;
    }


    public function getPeminjamanByKode($kodePeminjaman)
    {
        return Peminjaman::where('kode_peminjaman', $kodePeminjaman)->firstOrFail();
    }
}
