<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Simulatie van data, vervang dit door je eigen data uit de database
        $dates = ['2024-01-01', '2024-02-01', '2024-03-01', '2024-04-01'];
        $ratings = [1500, 1600, 1700, 1750]; // Rating data voor de grafiek
        $gameLabels = ['Game 1', 'Game 2', 'Game 3', 'Game 4']; // Games
        $averageRatings = [7.5, 8.2, 7.9, 8.5]; // Gemiddelde waarderingen per game

        return view('home', compact('dates', 'ratings', 'gameLabels', 'averageRatings'));
    }
}
