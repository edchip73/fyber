<?php

use Fyber\Database\Record;
use Fyber\Database\Transaction;

class Venda extends Record
{
    const TABLENAME = 'venda';

//Cliente
private $cliente;
public function get_cliente()
    {
        if (empty($this->cliente)) 
        {
            $this->cliente = new Cliente($this->id_cliente);
        }

        return $this->cliente;
    }

    public function get_nome_cliente()
    {
        if (empty($this->cliente)) 
        {
            $this->cliente = new Cliente($this->id_cliente);
        }

        return $this->cliente->nome;
    }   

    //Plano
private $plano;
public function get_plano()
    {
        if (empty($this->plano)) 
        {
            $this->plano = new Plano($this->id_plano);
        }

        return $this->plano;
    }

    public function get_nome_plano()
    {
        if (empty($this->plano)) 
        {
            $this->plano = new Plano($this->id_plano);
        }

        return $this->plano->nome;
    }   

//Situação
private $situacao;
public function get_situacao()
    {
        if (empty($this->situacao)) 
        {
            $this->situacao = new situacao($this->id_situacao);
        }

        return $this->situacao;
    }

    public function get_nome_situacao()
    {
        if (empty($this->situacao)) 
        {
            $this->situacao = new situacao($this->id_situacao);
        }

        return $this->situacao->nome;
    }   

   
    
}
