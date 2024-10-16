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

$data = new stdClass();
$data->id = 4; // Defina o ID do usuário existente para edição
$data->nome = 'teste2';
$data->login = 'teste@gmail.com';
$data->senha = 'nova_senha_teste';

try {
    Transaction::open($this->connection);

    if (isset($data->id) && $data->id) {
        // Edição de usuário existente
        $usuario = $this->activeRecord::find($data->id);
        if ($usuario) {
            // Atualiza os dados do usuário, exceto a senha se não foi fornecida
            $usuarioData = $usuario->toArray();
            if (empty($data->senha)) {
                $data->senha = $usuarioData['senha'];
            }
            error_log("Dados do usuário antes de atualizar: " . print_r($usuario, true));
            $usuario->fromArray((array) $data);
            $usuario->store();
            error_log("Dados do usuário após atualizar: " . print_r($usuario, true));
        }
    } else {
        // Criação de novo usuário
        $usuario = new $this->activeRecord;
        $usuario->fromArray((array) $data);
        error_log("Dados do novo usuário antes de salvar: " . print_r($usuario, true));
        $usuario->store();
        error_log("Dados do novo usuário após salvar: " . print_r($usuario, true));
    }

    Transaction::close();

    error_log("Dados salvos com sucesso.");
} catch (Exception $e) {
    error_log("Erro ao salvar os dados: " . $e->getMessage());
    Transaction::rollback();
    throw $e;
}
