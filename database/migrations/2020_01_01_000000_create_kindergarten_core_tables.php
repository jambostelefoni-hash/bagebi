<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class CreateKindergartenCoreTables extends Migration
{
    public function up()
    {
        if(!Schema::hasTable('active_statuses'))Schema::create('active_statuses',function(Blueprint $t){$t->id();$t->string('name',45);$t->timestamps();});
        if(!Schema::hasTable('group_age_ranges'))Schema::create('group_age_ranges',function(Blueprint $t){$t->id();$t->string('range',45);});
        if(!Schema::hasTable('regions'))Schema::create('regions',function(Blueprint $t){$t->id();$t->string('name',100);$t->timestamps();});
        if(!Schema::hasTable('municipalities'))Schema::create('municipalities',function(Blueprint $t){$t->id();$t->unsignedBigInteger('region_id')->nullable()->index();$t->string('name',100);$t->timestamps();});
        if(!Schema::hasTable('priorities'))Schema::create('priorities',function(Blueprint $t){$t->id();$t->string('name',255);$t->timestamps();});
        if(!Schema::hasTable('kindergartens'))Schema::create('kindergartens',function(Blueprint $t){$t->id();$t->unsignedBigInteger('municipality_id')->nullable()->index();$t->string('name',100);$t->timestamps();});
        if(!Schema::hasTable('kindergarten_group_age_range'))Schema::create('kindergarten_group_age_range',function(Blueprint $t){$t->unsignedBigInteger('kindergarten_id');$t->unsignedBigInteger('group_age_range');$t->unsignedInteger('space_length')->default(0);$t->unsignedInteger('space_filled')->default(0);$t->unsignedInteger('space_free')->default(0);$t->primary(['kindergarten_id','group_age_range']);});
        if(!Schema::hasTable('kindergarteners'))Schema::create('kindergarteners',function(Blueprint $t){$t->id();$t->unsignedBigInteger('municipality_id')->nullable()->index();$t->unsignedBigInteger('kindergarten_id')->nullable()->index();$t->unsignedBigInteger('group_id')->nullable()->index();$t->unsignedBigInteger('active_status_id')->default(1)->index();$t->boolean('graduate')->default(false);$t->string('kids_personal_number',11)->unique();$t->string('kids_first_name',100);$t->string('kids_last_name',100);$t->string('mother_personal_number',11)->nullable();$t->string('mother_first_name',100)->nullable();$t->string('mother_last_name',100)->nullable();$t->string('father_personal_number',11)->nullable();$t->string('father_first_name',100)->nullable();$t->string('father_last_name',100)->nullable();$t->string('mobile_number',20);$t->string('email')->nullable();$t->timestamps();});
        if(!Schema::hasTable('kindergartner_priorities'))Schema::create('kindergartner_priorities',function(Blueprint $t){$t->id();$t->unsignedBigInteger('kindergartner_id')->unique();$t->unsignedBigInteger('priority_id');$t->boolean('has_permission')->default(false);$t->timestamps();});
        if(!Schema::hasTable('settings'))Schema::create('settings',function(Blueprint $t){$t->id();$t->string('slug',45)->unique();$t->json('object')->nullable();$t->timestamps();});
        if(!Schema::hasTable('jobs'))Schema::create('jobs',function(Blueprint $t){$t->bigIncrements('id');$t->string('queue')->index();$t->longText('payload');$t->unsignedTinyInteger('attempts');$t->unsignedInteger('reserved_at')->nullable();$t->unsignedInteger('available_at');$t->unsignedInteger('created_at');});
        DB::table('active_statuses')->insertOrIgnore([['id'=>1,'name'=>'მომლოდინე'],['id'=>2,'name'=>'აქტიური'],['id'=>3,'name'=>'დამთავრებული'],['id'=>4,'name'=>'გასული']]);
        DB::table('group_age_ranges')->insertOrIgnore([['id'=>1,'range'=>'2-3'],['id'=>2,'range'=>'3-4'],['id'=>3,'range'=>'4-5'],['id'=>4,'range'=>'5-6']]);
    }
    public function down(){foreach(['jobs','kindergartner_priorities','kindergarteners','kindergarten_group_age_range','kindergartens','priorities','municipalities','regions','group_age_ranges','active_statuses','settings'] as $table)Schema::dropIfExists($table);}
}
