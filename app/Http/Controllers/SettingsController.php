<?php

namespace App\Http\Controllers;

use App\Models\User_join;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SettingsController extends Controller
{

    /**
     * Récupérer des informations généralistes à propos d'un donné (games jouée, gagnée, perdues ...) 
     * 
     *
     * @param integer $userid        L'id de l'utilisateur en question
     * 
     * @return Collection            La réponse de l'ORM
     */
    private function get_general_stats(int $userid) {
        // On désactive le mode "ONLY_FULL_GROUP_BY" qui est activé par défaut avec Laravel
        DB::statement("SET SQL_MODE=''");
                
        // On fait un inner join entre la table user_play et la tabke games pour récupérer les informations
        // qui nous intéressent
        return User_join::select("winner", "symbol") 
            -> join('games', 'games.gameid', '=', 'user_joins.gameid')
            -> where("player", $userid) 
            -> where("winner", "!=", null)
            -> get();
    }


    /**
     * Récupérer l'historique des parties jouées (joueur1, joueur2, vainqueur ...)
     *
     * @param integer $userid        L'id de l'utilisateur en question
     * 
     * @return array                 La réponse de l'ORM 
     */
    private function get_history(int $userid) {
        return User_join::select(
            "users.email AS email_p1", 
            "users.name AS name_p1",
            "users2.email AS email_p2",
            "users2.name AS name_p2", 
            "user_joins.symbol AS join_p1",
            "user_joins2.symbol AS join_p2",
            "games.winner",
            "games.created_at",
        ) 
        -> join('user_joins as user_joins2', 
            function ($join) {
                $join -> on('user_joins.gameid', '=', DB::raw("`user_joins2`.`gameid`"))
                      -> where('user_joins.player', '<>', DB::raw("`user_joins2`.`player`"));
            })
        -> join('users', 'user_joins.player', '=', 'users.id')
        -> join('users as users2', 'user_joins2.player', '=', 'users2.id')
        -> join('games', 'user_joins.gameid', '=', 'games.gameid')
        -> where("games.winner", "<>", null) 
        -> where("games.winner", "<>", "")
        -> where("users.email", "<>", DB::raw("`users2`.`email`"))
        -> where(function ($query) use ($userid) {
            $query -> where('users.id', $userid)
                   -> orWhere('users2.id', $userid);
        })
        -> groupBy("games.gameid")
        -> orderBy("games.created_at", "DESC")
        -> get()
        -> toArray();
    } 


    /**
     * Afficher le profil d'un utilisateur donné (un utilisateur peu afficher le profil de n'importe quel utilisateur)
     *
     * @param User $user        L'utilisateur par Model Binding
     * 
     * @return view             La vue affichant le profil
     */
    public function show(User $user) {

        // Get the ORM responde to the request
        $statistics = $this -> get_general_stats($user -> id);

        // Parse the ORM response to get usable stats
        $played_games = 0;
        $drawn_games = 0;
        $won_games = 0;
        $not_ended_games = 0;

        foreach($statistics as $game) {
            if($game -> winner === $game -> symbol) {
                $won_games++;
            }
            if($game -> winner === "draw") {
                $drawn_games++;
            }
            if($game -> winner === null) {
                $not_ended_games++;
            }
            $played_games++;
        }

        $lost_games = $played_games - $won_games - $drawn_games - $not_ended_games;
        
        // Get an array that represents the history of played games of the user
        $history = $this -> get_history($user -> id); 

        // Return the profile page with all the needed parameters
        return view("app.settings.profile", [
            "won_games" => $won_games,
            "lost_games" => $lost_games,
            "drawn_games" => $drawn_games,
            "email" => $user -> email,
            "name" => $user -> name,
            "history" => $history,

            // diffForHumans -> 15 seconds ago, 2 months ago for example
            "created_at" => Carbon::parse($user -> created_at) -> diffForHumans(),
        ]);
    }

    
    /**
     * Show the settings page
     *
     * @return view
     */
    public function show_settings() {
        return view("app.settings.settings");
    }
}
