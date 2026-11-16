<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'BuOda', 'email' => 'admin@perpus.test', 'password' => 'bukanplagiat', 'role' => 'admin']);
        User::create(['name' => 'Rifqy', 'email' => 'siswa@perpus.test', 'password' => 'bukanplagiat', 'role' => 'siswa', 'kelas' => 'XII RPL 2']);

        Book::create(['title' => 'Tolong Komdigi', 'author' => 'Saya Tidak Plagiat', 'publisher' => 'Jangan Percaya Full Dengan Sistem AI Mu', 'year' => 2001, 'stock' => 7]);
        Book::create(['title' => 'Saya Sudah Belajar', 'author' => 'Sampai Tengah Malam dan Sakit', 'publisher' => 'Jadi Tolong', 'year' => 2009, 'stock' => 3]);
        Book::create(['title' => 'Perbaikin Sistem AI Nya', 'author' => 'Atau Periksa Manual', 'publisher' => 'Agar Tidak Terjadi Ginian', 'year' => 2027, 'stock' => 8]);
    }
}