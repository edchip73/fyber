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

use Fyber\Traits\DeleteTrait;
use Fyber\Traits\ReloadTrait;
use Fyber\Traits\SaveTrait;
use Fyber\Traits\EditTrait;

use Fyber\Widgets\Wrapper\DatagridWrapper;
use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Container\Panel;

/**
 * Cadastro de Planos
 */
class PlanoFormList extends Page
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

        $this->connection = 'Fyber';
        $this->activeRecord = 'plano';

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_planos'));
        $this->form->setTitle('Cadastro de Planos');

        // cria os campos do formulário
        $id = new Entry('id');
        $nome = new Entry('nome');
        $valor = new Entry('valor');

        $id->setEditable(FALSE);

        $this->form->addField('Código', $id, '10%');
        $this->form->addField('Plano', $nome, '50%');
        $this->form->addField('Valor', $valor, '50%');

        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));

        // instancia a Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $id = new DatagridColumn('id', 'Código', 'center', '10%');
        $nome = new DatagridColumn('nome', 'Plano', 'left', '50%');
        $valor = new DatagridColumn('valor', 'Valor', 'left', '50%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($valor);

        $this->datagrid->addAction('Editar', new Action([$this, 'onEdit']), 'id', 'fa fa-edit');
        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash');

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
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }
}
