<?php

namespace App\Models;

use App\Core\Model;

class Habitacion extends Model
{
    public function all()
    {
        $sql = "SELECT
                    h.id,
                    h.numero,
                    p.nombre AS piso,
                    t.nombre AS tipo,
                    t.capacidad,
                    t.precio,
                    h.estado
                FROM habitaciones h
                INNER JOIN pisos p
                    ON h.piso_id = p.id
                INNER JOIN tipos_habitacion t
                    ON h.tipo_id = t.id
                ORDER BY h.numero ASC";

        return $this->db->query($sql)->fetchAll();
    }
}