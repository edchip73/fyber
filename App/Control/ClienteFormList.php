<?php

use Fyber\Control\Page;
use Fyber\Control\Action;
use Fyber\Widgets\Form\Form;
use Fyber\Widgets\Form\Entry;
use Fyber\Widgets\Form\Combo;
use Fyber\Widgets\Container\VBox;
use Fyber\Widgets\Datagrid\Datagrid;
use Fyber\Widgets\Datagrid\DatagridColumn;

use Fyber\Database\Transaction;
use Fyber\Database\Repository;

use Fyber\Traits\DeleteTrait;
use Fyber\Traits\ReloadTrait;
use Fyber\Traits\SaveTrait;
use Fyber\Traits\EditTrait;

use Fyber\Widgets\Wrapper\DatagridWrapper;
use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Container\Panel;

/**
 * Cadastro Fyber
 */
class ClienteFormList extends Page
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
        $this->activeRecord = 'cliente';
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_cliente'));
        $this->form->setTitle('Cadastro de Clientes');
                
        // cria os campos do formulário
        $id             = new Entry('id');
        $nome           = new Entry('nome');
        $fantasia       = new Entry('fantasia');
        $doc            = new Entry('doc');
        $ie             = new Entry('ie');
        $tel            = new Entry('tel');
        $email          = new Entry('email');
        $end            = new Entry('end');
        $num            = new Entry('num');
        $bairro         = new Entry('bairro');
        $cidade         = new Entry('cidade');
        $uf             = new Entry('uf');
                               
        $id->setEditable(FALSE);
              
              
//Montagem do Formulário                
        $this->form->addField('Código',     $id, '10%');
        $this->form->addField('Cliente',    $nome, '50%');
        $this->form->addField('Fantasia',   $fantasia, '50%');
        $this->form->addField('CNPJ',       $doc, '50%');
        $this->form->addField('IE',         $ie, '50%');
        $this->form->addField('Telefone',   $tel, '50%');
        $this->form->addField('E-mail',     $email, '50%');
        $this->form->addField('Endereço',   $end, '50%');
        $this->form->addField('Número',     $num, '50%');
        $this->form->addField('Bairro',     $bairro, '50%');
        $this->form->addField('Cidade',     $cidade, '50%');
        $this->form->addField('UF',         $uf, '50%');
                           
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));
                
        // instancia a Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $id             = new DatagridColumn('id',      'Código',  'center', '10%');
        $nome           = new DatagridColumn('nome',    'Cliente', 'left',   '50%');
        $fantasia       = new DatagridColumn('fantasia','Fantasia','left',   '50%');
        $doc            = new DatagridColumn('doc',     'CNPJ',    'left',   '50%');
        $ie             = new DatagridColumn('ie',      'IE',      'left',   '50%');
        $tel            = new DatagridColumn('tel',     'Telefone','left',   '50%');
        $email          = new DatagridColumn('email',   'E-mail',  'left',   '50%');
        $end            = new DatagridColumn('end',     'Endereço','left',   '50%');
        $num            = new DatagridColumn('num',     'Número',  'left',   '50%');
        $bairro         = new DatagridColumn('bairro',  'Bairro',  'left',   '50%');
        $cidade         = new DatagridColumn('cidade',  'Cidade',  'left',   '50%');
        $uf             = new DatagridColumn('uf',      'UF',      'left',   '50%');
               
        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($fantasia);
        $this->datagrid->addColumn($doc);
        $this->datagrid->addColumn($ie);
        $this->datagrid->addColumn($tel);
        $this->datagrid->addColumn($email);
        $this->datagrid->addColumn($end);
        $this->datagrid->addColumn($num);
        $this->datagrid->addColumn($bairro);
        $this->datagrid->addColumn($cidade);
        $this->datagrid->addColumn($uf);
        
                 
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
