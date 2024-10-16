<?php

use Fyber\Control\Page;
use Fyber\Control\Action;
use Fyber\Widgets\Form\Form;
use Fyber\Widgets\Form\Entry;
use Fyber\Widgets\Container\VBox;
use Fyber\Widgets\Datagrid\Datagrid;
use Fyber\Widgets\Datagrid\DatagridColumn;
use Fyber\Widgets\Dialog\Message;
use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Wrapper\DatagridWrapper;
use Fyber\Database\Transaction;
use Fyber\Database\Repository;
use Fyber\Database\Criteria;


/**
 * Listagem de Pessoas
 */
class ClienteList extends Page
{
    private $form;     // formulário de buscas
    private $datagrid; // listagem
    private $loaded;

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        // instancia um formulário de buscas
        $this->form = new FormWrapper(new Form('form_busca_cliente'));
        $this->form->setTitle('Geração de Contratos');

        $nome = new Entry('nome');
        $this->form->addField('Cliente', $nome, '60%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Emitir Contrato', new Action(array($this, 'onEmitirContrato')));

        // instancia objeto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $codigo = new DatagridColumn('id', 'Código', 'center', '10%');
        $nome = new DatagridColumn('nome', 'Cliente', 'left', '40%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);

        // monta a página através de uma caixa
        $box = new VBox;
        $box->style = 'display:block';
        $box->add($this->form);
        $box->add($this->datagrid);

        parent::add($box);
    }

    /**
     * Carrega a Datagrid com os objetos do banco de dados
     */
    public function onReload()
    {
        Transaction::open('fyber'); // inicia transação com o BD
        $repository = new Repository('Cliente');

        // cria um critério de seleção de dados
        $criteria = new Criteria;
        $criteria->setProperty('order', 'id');

        // obtém os dados do formulário de buscas
        $dados = $this->form->getData();

        // verifica se o usuário preencheu o formulário
        if ($dados->nome) {
            // filtra pelo nome do cliente
            $criteria->add('nome', 'like', "%{$dados->nome}%");
        }

        // carrega os clientes que satisfazem o critério
        $clientes = $repository->load($criteria);
        $this->datagrid->clear();
        if ($clientes) {
            foreach ($clientes as $cliente) {
                // adiciona o objeto na Datagrid
                $this->datagrid->addItem($cliente);
            }
        }

        // mantém o valor do campo nome no formulário
        $this->form->setData($dados);

        // finaliza a transação
        Transaction::close();
        $this->loaded = true;
    }
/*
*** Método onEmitirContrato
*/

public function onEmitirContrato()
{
    $dados = $this->form->getData();

    if (!$dados->nome) {
        new Message('error', 'Preencha o nome para emitir o contrato.');
        return;
    }

    try {
        Transaction::open('Fyber');
        $repository = new Repository('Cliente');

        $criteria = new Criteria;
        $criteria->add('nome', 'like', "%{$dados->nome}%");

        $clientes = $repository->load($criteria);
        Transaction::close();

        if (count($clientes) === 1) {
            $cliente = $clientes[0];

            $clienteData = [
                'nome' => $cliente->nome,
                'doc' => $cliente->doc,
                'tel' => $cliente->tel,
                'email' => $cliente->email,
                'end' => $cliente->end,
                'num' => $cliente->num,
                'bairro' => $cliente->bairro,
                'cidade' => $cliente->cidade,
                'uf' => $cliente->uf,
            ];

            $jsonData = json_encode($clienteData, JSON_UNESCAPED_UNICODE);

            // Log the JSON data to verify it
            error_log("JSON data to send to Python script: $jsonData");

            // Ensure the JSON data is properly formatted
            if (json_last_error() !== JSON_ERROR_NONE) {
                new Message('error', 'Erro ao gerar o contrato: JSON inválido.');
                return;
            }

            // Save JSON data to a temporary file
            $tempJsonFilePath = tempnam(sys_get_temp_dir(), 'contract_data_');
            file_put_contents($tempJsonFilePath, $jsonData);

            $command = "python App/Templates/assets/doc/generate_contract.py " . escapeshellarg($tempJsonFilePath);
            error_log("Executing command: $command");
            $output = [];
            $return_var = null;
            exec($command . ' 2>&1', $output, $return_var);

            // Log the output and return value
            error_log("Python script output: " . print_r($output, true));
            error_log("Python script return value: " . $return_var);

            // Remove the temporary file
            unlink($tempJsonFilePath);

            $tempDocPath = trim(implode("\n", $output));

            if ($return_var !== 0 || !file_exists($tempDocPath)) {
                new Message('error', 'Erro ao gerar o contrato.');
                return;
            }

            if (file_exists($tempDocPath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                header('Content-Disposition: attachment; filename="contrato.docx"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tempDocPath));
                ob_clean();
                flush();
                readfile($tempDocPath);
                unlink($tempDocPath);
                exit;
            } else {
                new Message('error', 'Erro ao gerar o contrato.');
            }
        } elseif (count($clientes) > 1) {
            new Message('error', 'Mais de um cliente encontrado. Por favor, refine sua busca.');
        } else {
            new Message('error', 'Nenhum cliente encontrado.');
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        new Message('error', 'Erro ao processar o contrato.');
        Transaction::rollback();
    }
}



    /**
     * Exibe a página
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
