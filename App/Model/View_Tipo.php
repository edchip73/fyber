<?php

use Fyber\Database\Record;
use Fyber\Database\Transaction;

class View_Tipo extends Record
{
    const TABLENAME = 'view_tipo';
    
    public static function getVendasTipo()
    {
        $conn = Transaction::get();
        $result = $conn->query("select tipo, valor as 'total' from view_tipo");
        
        $dataset = [];
        foreach ($result as $row)
        {
            $dataset[ $row['tipo'] ] = $row['total'];
        }
        
        return $dataset;
    }
}