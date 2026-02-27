<?php
// database/migrations/2014_10_12_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('id_card')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->rememberToken();
            $table->timestamps();
        });
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'email' => 'usuario1@gmail.com',
                'password' => Hash::make('12345678'),
                'email_verified_at' => '2022-01-02 17:04:58',
                'avatar' => 'avatar-6.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'admin',
                'email' => 'moralessfaby.dev@gmail.com',
                'password' => Hash::make('12345678'),
                'email_verified_at' => '2022-01-02 17:04:58',
                'avatar' => 'avatar-6.jpg',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};