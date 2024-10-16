<?php

use Fyber\Database\Record;
use Fyber\Database\Transaction;
use Livro\Database\Repository;

class View_Pagamento extends Record
{
    const TABLENAME = 'view_pagamento';
      

    private $itens;
    private $cliente;
    
    /**
     * Atribui o cliente
     */
    public function set_cliente(View_Pagamento $c)
    {
        $this->cliente = $c;
        $this->id_cliente = $c->id;
    }
    
    /**
     * retorna o objeto cliente vinculado à venda
     */
    public function get_cliente()
    {
        if (empty($this->cliente))
        {
            $this->cliente = new View_Pagamento($this->id_cliente);
        }
        
        // Retorna o objeto instanciado
        return $this->cliente;
    }
     /**
     * Retorna vendas por mes
     */
    public static function getVendasMes()
    {
        $meses = array();
        $meses[1] = 'Janeiro';
        $meses[2] = 'Fevereiro';
        $meses[3] = 'Março';
        $meses[4] = 'Abril';
        $meses[5] = 'Maio';
        $meses[6] = 'Junho';
        $meses[7] = 'Julho';
        $meses[8] = 'Agosto';
        $meses[9] = 'Setembro';
        $meses[10] = 'Outubro';
        $meses[11] = 'Novembro';
        $meses[12] = 'Dezembro';
        
        $conn = Transaction::get();
        $result = $conn->query("select MONTH(pagamento) as mes, 
        sum(valor) as valor from view_pagamento group by mes order by 1");
        
        $dataset = [];
        foreach ($result as $row)
        {
            $mes = $meses[ (int) $row['mes'] ];
            $dataset[ $mes ] = $row['valor'];
        }
        
        return $dataset;
    }

     /**
     * Retorna Tipo
     */
   
}