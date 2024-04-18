<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }

    public static function getStudentStatuses(Request $request)
    {
        $nim = $request->query('nim');
        $student = Student::where('nim', $nim)->first();
        $data_peminjaman = Peminjaman::select('peminjaman.id', 'peminjaman.tgl_pinjam', 'peminjaman.tgl_kembali', 'bukus.judul_buku', 'bukus.kode_buku', 'peminjaman.status',)
            ->join('buku_peminjaman', 'peminjaman.kode_pinjam', '=', 'buku_peminjaman.kode_pinjam')
            ->join('bukus', 'buku_peminjaman.kode_buku', '=', 'bukus.kode_buku')
            ->where('peminjaman.nim', $nim)
            ->limit(2)
            ->get();

        $student->data_peminjaman = $data_peminjaman;
        Log::info($student);
        Log::info($data_peminjaman);
        if (!$student) {
            return view('portal-peminjaman', ['error' => 'NIM tidak ditemukan']);
        }
        return view('home-page', ['student' => $student], ['data_peminjaman' => $data_peminjaman]);
        // return response()->json($student);
    }
}
