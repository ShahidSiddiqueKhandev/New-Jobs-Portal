<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMipiaceTable extends Migration
{
   /**
    * Run the migrations.
    *
    * @return void
    */
   public function up()
   {
      Schema::table('MiPiace', function (Blueprint $table) {
         $table->foreign('post', 'PostMiPiaceFK')->references('id')->on('post')->onUpdate('CASCADE')->onDelete('CASCADE');
         $table->foreign('utente', 'UtenteMiPiaceFK')->references('id')->on('utente')->onUpdate('CASCADE')->onDelete('CASCADE');
         DB::statement("ALTER TABLE MiPiace COMMENT = 'Like degli Utenti ai Post';");
      });
   }

   /**
    * Reverse the migrations.
    *
    * @return void
    */
   public function down()
   {
      Schema::table('mipiace', function (Blueprint $table) {
         $table->dropForeign('PostMiPiaceFK');
         $table->dropForeign('UtenteMiPiaceFK');
      });
   }
}
