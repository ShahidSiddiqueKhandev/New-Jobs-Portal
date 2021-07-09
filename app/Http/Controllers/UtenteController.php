<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use App\Models\Utente;
use App\Models\Citta;
use App\Models\Lavoro;


class UtenteController extends Controller {

   public function login(Request $req): Factory | View | Application {
      $req->validate([
         'msg' => ['min:3', 'max:75'],
      ], [
         'msg.min' => 'Invalid MSG.',
         'msg.max' => 'Invalid MSG.'
      ]);
      return view('login.index',[
         'msg' => $req->msg
      ]);
   }

   public function logout(Request $req): RedirectResponse {
      $req
         ->session()
         ->forget('utente');
      Cookie::queue(Cookie::forget('password'));
      $req
         ->session()
         ->regenerate();
      Log::warning('Finished User-Session.');
      return redirect('/login')
         ->withErrors('Ti sei disconnesso, devi effettuare di nuovo il Login.');
   }

   public function registrazione(Request $req): Factory | View | Application {
      $req->validate([
         'msg' => ['min:3', 'max:75'],
      ], [
         'msg.min' => 'Invalid MSG.',
         'msg.max' => 'Invalid MSG.'
      ]);
      return view('registrazione.index',[
         'citta' => Citta::all(),
         'lavori' => Lavoro::all(),
         'msg' => $req->msg
      ]);
   }

   public function insert(Request $req): RedirectResponse {
      if(checkRef($req, 'registrazione')) {
         $req->validate([
            'email' => ['email', 'required', 'unique:Utente','min:2', 'max:35'],
            'password' => ['required', 'min:8', 'max:8'],
            'nome' => ['required', 'min:3', 'max:45'],
            'cognome' => ['required', 'min:3', 'max:45'],
            'citta' => ['required', 'numeric', 'min:1', 'max:13'],
            'lavoro' => ['required', 'numeric', 'min:1', 'max:15'],
            'dataInizioLavoro' => ['nullable', 'date', 'date_format:Y-m-d']
         ], [
            'email.required' => 'Email is Required.',
            'email.min' => 'Email almeno 2 caratteri.',
            'email.max' => 'Email massimo 35 caratteri.',
            'email.unique' => 'Utente già Registrato, è possible effettuare il Login.',
            'password.required' => 'Password is Required.',
            'password.min' => 'Password con 8 caratteri.',
            'password.max' => 'Password con 8 caratteri.',
            'nome.required' => 'Nome is Required.',
            'nome.min' => 'Nome almeno 3 caratteri.',
            'nome.max' => 'Nome massimo 45 caratteri.',
            'cognome.required' => 'Cognome is Required.',
            'cognome.min' => 'Cognome almeno 3 caratteri.',
            'cognome.max' => 'Cognome massimo 45 caratteri.',
            'citta.required' => 'Città is Required.',
            'citta.numeric' => 'Città inserita non valida.',
            'citta.min' => 'Città inserita non valida.',
            'citta.max' => 'Città inserita non valida.',
            'lavoro.required' => 'Lavoro is Required.',
            'lavoro.numeric' => 'Lavoro inserito non valido.',
            'lavoro.min' => 'Lavoro inserito non valido.',
            'lavoro.max' => 'Lavoro inserito non valido.',
            'dataInizioLavoro.date' => 'Data inizio lavoro is a date.',
            'dataInizioLavoro.date_format' => 'Incorrect date format for Data inizio lavoro.',
            'dataInizioLavoro.before_or_equal' => 'Data inizio lavoro non valida.'
         ]);
         insertUtente($req);
         Log::debug('New User Interted.');
         return redirect('/login')
            ->with('msg', 'reg');
      } else
         redirect('/registrazione');
   }

   public function logResult(Request $req): Factory | View | RedirectResponse | Application {
      $req->validate([
         'email' => ['email', 'required', 'min:2', 'max:35'],
         'password' => ['required', 'min:8', 'max:8'],
      ], [
         'email.email' => 'Inserisci Email valida',
         'email.required' => 'Email is Required.',
         'email.min' => 'Email almeno 2 caratteri.',
         'email.max' => 'Email massimo 35 caratteri.',
         'password.required' => 'Password is Required.',
         'password.min'  => 'Password con 8 caratteri.',
         'password.max'  => 'Password con 8 caratteri.',
      ]);
      $email = trim($req->email);
      $password = $req->input('password');
      if(isLogged($email, $password)) {
         if(!$req->navbar) {
            $utente = Utente::all([
               'id',
               'email',
               'nome',
               'cognome',
            ])
               ->where('email', $email)
               ->first();
            $utente->password = $password;
            $req
               ->session()
               ->put('utente', $utente);
            //----
            $req
               ->session()
               ->regenerate();
            //----
            Log::info("New User-Session started ($email)");
         }
         $utente_id = $req
            ->session()
            ->get('utente')->id;
         return view('feed.index', [
            'posts' => getAllPosts($utente_id),
            'profile_id' => null
         ]);
      } else
         return back()
            ->withErrors(['Utente non registrato, è possible farlo.']);
   }

   public function passwordDimenticata(Request $req): bool {
      $req->validate([
         'email' => ['email', 'required', 'min:2', 'max:35'],
         'password' => ['required', 'min:8', 'max:8'],
      ], [
         'email.email' => 'Inserisci Email valida',
         'email.required' => 'Email is Required.',
         'email.min' => 'Email almeno 2 caratteri.',
         'email.max' => 'Email massimo 35 caratteri.',
         'password.required'  => 'Password is Required.',
         'password.min'  => 'Password con 8 caratteri.',
      ]);
      $res = false;
      if(checkRef($req, 'login')) {
         $email = trim($req->email);
         $password = $req->password;
         $utente = Utente::where('email', $email);
         if($utente->count()) {
            $utente->update(['password' => Hash::make($password)]);
            Cookie::queue('password', $password, (60 * 24));
            $res = sendmail($email, $password);
         }
      }
      return $res;
   }
}
