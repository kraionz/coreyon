<?php namespace Creatyon\Core\Common\database\seeds;

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new User;
        $admin->username = 'Lerolero';
        $admin->email = 'admin@admin.com';
        $admin->password = '123456';
        $admin->save();

        $user = new User;
        $user->username = 'Andres';
        $user->email = 'kraionz@hotmail.com';
        $user->password = '123456';
        $user->save();
    }
}
