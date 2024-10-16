<?php
use Fyber\Control\Page;
use Fyber\Control\Action;
use Fyber\Widgets\Form\Form;
use Fyber\Widgets\Form\Entry;
use Fyber\Widgets\Form\Combo;
use Fyber\Widgets\Form\Date;
use Fyber\Widgets\Container\VBox;
use Fyber\Widgets\Datagrid\Datagrid;
use Fyber\Widgets\Datagrid\DatagridColumn;

use Fyber\Database\Transaction;

use Fyber\Traits\DeleteTrait;
use Fyber\Traits\ReloadTrait;
use Fyber\Traits\SaveTrait;
use Fyber\Traits\EditTrait;

use Fyber\Widgets\Wrapper\DatagridWrapper;
use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Container\Panel;

/**
 * Venda Fyber
 */
class VendaFormList extends Page
{
    private $form;
    private $datagrid;
    private $loaded;
    private $connection;
    private $activeRecord;
    
    use EditTrait;
    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }
    use SaveTrait {
        onSave as onSaveTrait;
    }
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        $this->connection   = 'Fyber';
        $this->activeRecord = 'venda';
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_venda'));
        $this->form->setTitle('Cadastro de Vendas');
        
        // cria os campos do formulário
        $id             = new Entry('id');
        $id_cliente     = new Combo('id_cliente');
        $id_plano       = new Combo('id_plano');
        $data_vencimento= new Date('data_vencimento');
        $data_pagamento = new Date('data_pagamento');
        $id_situacao    = new Combo('id_situacao');
        $boleto         = new Entry('boleto');

        $id->setEditable(FALSE);

//Cliente
        Transaction::open('fyber');
                $clientes = Cliente::all();
                $items = array();
                foreach ($clientes as $obj_cliente)
                {
                    $items[$obj_cliente->id] = $obj_cliente->nome;
                }
        Transaction::close();
                $id_cliente->addItems($items);

//Plano
        Transaction::open('fyber');
        $planos = Plano::all();
        $items = array();
        foreach ($planos as $obj_plano)
        {
            $items[$obj_plano->id] = $obj_plano->nome;
        }
Transaction::close();
        $id_plano->addItems($items);

//Situacao
        Transaction::open('fyber');
        $situacoes = Situacao::all();
        $items = array();
        foreach ($situacoes as $obj_situacao)
        {
            $items[$obj_situacao->id] = $obj_situacao->nome;
        }
        Transaction::close();
        $id_situacao->addItems($items);        
                
//Montagem do Formulário                
        $this->form->addField('Código', $id, '10%');
        $this->form->addField('Cliente', $id_cliente, '50%');
        $this->form->addField('Plano', $id_plano, '50%');
        $this->form->addField('Vencimento',$data_vencimento, '50%');
        $this->form->addField('Pagamento', $data_pagamento, '50%');
        $this->form->addField('Situação', $id_situacao, '50%');
        $this->form->addField('Boleto', $boleto, '50%');
                   
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));
                
// instancia a Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $id              = new DatagridColumn('id',             'Código',    'center', '10%');
        $id_cliente      = new DatagridColumn('nome_cliente',   'Cliente',   'left',   '50%');
        $id_plano        = new DatagridColumn('nome_plano',     'Plano',     'left',   '50%');
        $data_vencimento = new DatagridColumn('data_vencimento','Vencimento','left',   '50%');
        $data_pagamento  = new DatagridColumn('data_pagamento', 'Pagamento', 'left',   '50%');
        $id_situacao     = new DatagridColumn('nome_situacao',  'Situação',  'left',   '50%');
        $boleto          = new DatagridColumn('boleto','Boleto',             'left',   '50%');   

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($id_cliente);
        $this->datagrid->addColumn($id_plano);
        $this->datagrid->addColumn($data_vencimento);
        $this->datagrid->addColumn($data_pagamento);
        $this->datagrid->addColumn($id_situacao);
        $this->datagrid->addColumn($boleto);
            
        $this->datagrid->addAction( 'Editar',  new Action([$this, 'onEdit']),   'id', 'fa fa-edit');
        $this->datagrid->addAction( 'Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash');
        
        // monta a página através de uma tabela
        $box = new VBox;
        $box->style = 'display:block';
        $box->add($this->form);
        $box->add($this->datagrid);
        
        parent::add($box);
    }
    
    /**
     * Salva os dados
     */
    public function onSave()
    {
        $this->onSaveTrait();
        $this->onReload();
    }
    
    /**
     * Carrega os dados
     */
    public function onReload()
    {
        $this->onReloadTrait();   
        $this->loaded = true;
    }

    /**
     * exibe a página
     */
    public function show()
    {
        // se a listagem ainda não foi carregada
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
}
