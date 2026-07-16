<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Habitacion;

class HabitacionController extends Controller
{
    public function index()
    {
        $habitaciones = (new Habitacion())->all();

        $this->view('habitaciones.index', compact('habitaciones'));
    }
}