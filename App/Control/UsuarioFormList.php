<?php

require_once 'vendor/autoload.php';
require_once 'App/Model/Usuario.php';

use Fyber\Model\Usuario;
use Fyber\Control\Page;
use Fyber\Control\Action;
use Fyber\Widgets\Form\Password;
use Fyber\Widgets\Form\Form;
use Fyber\Widgets\Form\Entry;
use Fyber\Widgets\Form\Combo;
use Fyber\Widgets\Container\VBox;
use Fyber\Widgets\Datagrid\Datagrid;
use Fyber\Widgets\Datagrid\DatagridColumn;
use Fyber\Database\Transaction;
use Fyber\Database\Repository;
use Fyber\Database\Criteria;
use Fyber\Traits\DeleteTrait;
use Fyber\Traits\ReloadTrait;
use Fyber\Traits\SaveTrait;
use Fyber\Traits\EditTrait;

use Fyber\Widgets\Wrapper\DatagridWrapper;
use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Container\Panel;

use Fyber\Widgets\Dialog\Message;

/**
 * Cadastro de Usuários
 */
class UsuarioFormList extends Page
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
        $this->activeRecord = Usuario::class; // Use a classe Usuario com o namespace correto
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_usuarios'));
        $this->form->setTitle('Cadastro de Usuários');
        
        // cria os campos do formulário
        $id   = new Entry('id');
        $nome = new Entry('nome');
        $login = new Entry('login');
        $senha = new Password('senha');
        
        $id->setEditable(FALSE);
                
        $this->form->addField('Código', $id,    '10%');
        $this->form->addField('Nome',  $nome,  '50%');
        $this->form->addField('Login',  $login,  '50%');
        $this->form->addField('Senha',  $senha,  '50%');
                
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));
        
        // instancia a Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $id   = new DatagridColumn(     'id',    'Código',   'center','10%');
        $nome     = new DatagridColumn( 'nome',  'Nome','left',  '50%');
        $login     = new DatagridColumn( 'login',  'Login','left',  '50%');
        $senha     = new DatagridColumn( 'senha',  'Senha','left',  '50%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($login);
        $this->datagrid->addColumn($senha);
        
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
    $data = $this->form->getData();
    
    // Verificação e logging detalhado
    error_log("Dados recebidos: " . print_r($data, true));

    if (isset($data->senha) && !empty($data->senha)) {
        // Verifica se a senha já está hasheada
        if (!password_get_info($data->senha)['algo']) {
            error_log("Senha antes do hash: " . $data->senha);
            $hashedPassword = password_hash($data->senha, PASSWORD_DEFAULT);
            
            if ($hashedPassword !== false) {
                $data->senha = $hashedPassword;
                error_log("Senha após hash: " . $data->senha);
                $this->form->setData($data);
            } else {
                error_log("Falha ao gerar o hash da senha");
                throw new Exception("Falha ao gerar o hash da senha");
            }
        } else {
            error_log("A senha já está hasheada.");
        }
    } else {
        error_log("Senha não fornecida ou está vazia");
        throw new Exception("Senha não fornecida ou está vazia");
    }

    // Log final antes de salvar
    error_log("Dados antes de salvar: " . print_r($data, true));

    try {
        // Certifique-se de que o hash da senha está sendo salvo no banco de dados corretamente
        Transaction::open($this->connection); // abre a transação
        $usuario = new $this->activeRecord; // instancia o Active Record
        $usuario->fromArray((array) $data); // carrega os dados no Active Record
        $usuario->store(); // armazena o objeto no banco de dados
        Transaction::close(); // fecha a transação

        error_log("Dados salvos com sucesso.");
    } catch (Exception $e) {
        error_log("Erro ao salvar os dados: " . $e->getMessage());
        Transaction::rollback();
        throw $e;
    }

    // Chamamos apenas onReload para recarregar os dados após o salvamento
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
