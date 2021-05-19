<?php

namespace App\Http\Controllers;

use App\Models\Lavoro;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Foundation\Application;
use App\Models\Citta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Utente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class UtenteController extends Controller {

   public function login(Request $req): Factory | View | Application {
      return view('login.index',[
         'msg' => $req->msg,
         'ref' => (checkRef($req, 'login') || checkRef($req, 'registrazione'))
      ]);
   }

   public function logout(Request $req): RedirectResponse {
      $req
         ->session()
         ->forget('utente');
      Log::error('Finished User-Session.');
      return redirect()
         ->route('login');
   }

   public function registrazione(Request $req): Factory | View | Application {
      return view('registrazione.index',[
         'citta' => Citta::all(),
         'lavori' => Lavoro::all(),
         'msg' => $req->msg,
         'ref' => checkRef($req, 'login')
      ]);
   }

   public function insert(Request $req): RedirectResponse {
      $email = $req->email;
      if(isLogged($email))
         return redirect()
            ->route('login', ['msg' => 'log']);
      else {
         insertUtente($req);
         Log::debug('New User Interted');
         return redirect()
            ->route('login', ['msg' => 'reg']);
      }
   }

   public function logResult(Request $req): Factory | View | RedirectResponse | Application {
      $email = $req->email;
      $password = $req->password;
      $logged = isLogged($email, $password);
      if($logged) {
         if(!$req->navbar) {
            $utente = Utente::all([
               'id',
               'email',
               'password',
               'nome',
               'cognome',
            ])
               ->where('email', $email)
               ->first();
            $utente->password = $password;
            $req
               ->session()
               ->put('utente', $utente);
            Log::info('New User-Session started');
         }
         $utente_id = $req->session()->get('utente')->id;
         return $this->feed($utente_id);
      } else
         return redirect()
            ->route('login', ['msg' => 'not-reg']);
   }

   public function feed(int $utente_id): Factory | View | Application {
      return view('feed.index', [
         'posts' => getAllPosts($utente_id),
         'profile' => false
      ]);
   }

   public function passwordDimenticata(Request $req): bool {
      $email = $req->email;
      $password = $req->password;
      $res = false;
      $utente = Utente::where('email', $email);
      if($utente->count()) {
         $utente->update(['password' => Hash::make($password)]);
         $res = sendmail($email, $password);
      }
      return $res;
   }
}
