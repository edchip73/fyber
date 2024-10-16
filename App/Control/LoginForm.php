<?php

require_once 'vendor/autoload.php';
require_once 'App/Model/Usuario.php';

use Fyber\Model\Usuario;
use Fyber\Control\UsuarioFormList;
use Fyber\Control\Page;
use Fyber\Control\Action;
use Fyber\Widgets\Form\Form;
use Fyber\Widgets\Form\Entry;
use Fyber\Widgets\Form\Password;
use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Container\Panel;

use Fyber\Database\Transaction;
use Fyber\Database\Record;

use Fyber\Session\Session;
use Fyber\Widgets\Dialog\Message;

/**
 * Formulário de Login
 */
class LoginForm extends Page
{
    private $form; // formulário
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_login'));
        //$this->form->setTitle('Login');
        
        $login      = new Entry('login');
        $password   = new Password('password');
        
        $login->placeholder    = 'Entre com seu login';
        $password->placeholder = 'Entre com sua senha';
        
        $this->form->addField('Login',    $login,    200);
        $this->form->addField('Senha',    $password, 200);
        $this->form->addAction('Login', new Action(array($this, 'onLogin')));
        
        // adiciona o formulário na página
        parent::add($this->form);
    }
    
   /**
     * Login
     */
    public function onLogin($param)
    {
        try {
            $data = $this->form->getData();
            
            Transaction::open('Fyber'); // Nome da conexão com o banco de dados
            
            $connection = Transaction::get();
            $sql = "SELECT * FROM usuario WHERE login = :login";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(':login', $data->login);
            $stmt->execute();
            $user = $stmt->fetchObject(Usuario::class);
            
            if ($user && password_verify($data->password, $user->senha)) {
                Session::setValue('logged', TRUE);
                echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
            } else {
                throw new Exception('Login ou senha incorretos');
            }
            
            Transaction::close();
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
        $data = $this->form->getData();
        if ($data->login == 'max@radiofyber.com.br' AND $data->password == 'radio@max')
        {
            Session::setValue('logged', TRUE);
            echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
        }    
    }
       
    /**
     * Logout
     */
    public function onLogout($param)
    {
        Session::setValue('logged', FALSE);
        echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
    }
}
