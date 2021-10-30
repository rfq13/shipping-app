<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPasslogTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE TRIGGER CREATE_password_log AFTER INSERT ON `users` FOR EACH ROW
                BEGIN
                   INSERT INTO `password_logs` (`user_id`,`password`, `created_at`, `updated_at`) 
                   VALUES (NEW.id,NEW.password,now(),null);
                END');

        DB::unprepared('CREATE TRIGGER UPDATE_password_log AFTER UPDATE ON `users` FOR EACH ROW
                BEGIN
                INSERT INTO `password_logs` (`user_id`,`password`, `created_at`, `updated_at`) 
                   VALUES (NEW.id,NEW.password,null,now());
                END');
    }
    public function down()
    {
        DB::unprepared('DROP TRIGGER `CREATE_password_log`');
        DB::unprepared('DROP TRIGGER `UPDATE_password_log`');
    }
}
