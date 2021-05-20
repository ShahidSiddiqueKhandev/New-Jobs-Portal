<?php

namespace App\Http\Controllers;

use App\Models\Citta;
use App\Models\Lavoro;
use App\Models\Utente;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ProfileController extends Controller {

   private ?Utente $utente;

   public function profile(Request $req): Factory | View | Application {
      $this->utente = $req
         ->session()
         ->get('utente');
      $utente_id = $this->utente->id;
      $profile = getProfile($utente_id);
      return view('profile.index', [
         'profile' => $profile,
         'richieste' => getRichieste($utente_id),
         'posts' => getAllPosts($utente_id, true),
         'own_profile' => true
      ]);
   }

   public function editProfile(Request $req): Factory | View | Application | RedirectResponse {
      if(checkRef($req, 'profile') || checkRef($req, 'show-profile')) {
         $this->utente = $req
            ->session()
            ->get('utente');
         $profile = getProfile($this->utente->id);
         return view('profile.utils.form', [
            'lavori' => Lavoro::all(),
            'citta' => Citta::all(),
            'profile' => $profile
         ]);
      } else
         return redirect()
            ->route('logout');
   }
   public function updateProfile(Request $req): RedirectResponse {
      Log::debug('A profile is Updated ');
      updateProfile($req);
      return redirect('/profile');
   }
   public function showProfile(Request $req): Factory | View | Application {
      $emailSearched = $req->search;
      consoleLog("Profile searched: $emailSearched");
      $this->utente = $req
         ->session()
         ->get('utente');
      $utenteSearched = Utente::where(
         'email',  $emailSearched
      )->first();
      if(isset($utenteSearched)) {
         $utente_id = $this->utente->id;
         $utenteSearched_id = $utenteSearched->id;
         $profile = getProfile($utenteSearched_id);
         return view('profile.index', [
            'profile' => $profile,
            'richieste' => getRichieste($utente_id),
            'posts' => getAllPosts($utenteSearched_id, true),
            'own_profile' => $profile->utente_id === $utente_id,
         ]);
      } else {
         Log::error("User $emailSearched NOT FOUND.");
         return view('utils.user-not-found');
      }
   }
}
