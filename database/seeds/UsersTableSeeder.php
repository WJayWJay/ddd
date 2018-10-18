<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = DB::table('users');
        $fuser = $user->orderBy('id', 'desc')->first();
        $id = $fuser->id;
        $user->insert([
            'name' => 'hellokitty'.++$id,
            // 'name' => str_random(10),
            'email' => str_random(10).'@gmail.com',
            'password' => bcrypt('wujian'),
        ]);
    }
}
