<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


/**
 * @method static like(mixed $input, mixed $input1)
 */
class MiPiace extends Model
{

   protected $table = 'MiPiace';
   public $timestamps = false;

   public function scopeIsLiked (Builder $query, int $post, int $utente): bool {
      $res = DB::table('MiPiace AS mp')
         ->select(DB::raw('COUNT(u.id) AS liked'))
         ->join('Post AS p', 'mp.post', 'p.id')
         ->join('Utente AS u', 'mp.utente', 'u.id')
         ->where('p.id', $post)
         ->where('u.id', $utente)
         ->first();
      return (bool) $res->liked;
   }

   public function scopeLike(Builder $query, int $post, int $utente): void {
      if(!MiPiace::isLiked($post, $utente)) {
         $miPiace = new MiPiace();
         $miPiace->post = $post;
         $miPiace->utente = $utente;
         $miPiace->save();
      }
   }
}
