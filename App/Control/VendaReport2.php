<?php
use Fyber\Control\Page;
use Fyber\Control\Action;
use Fyber\Widgets\Form\Form;
use Fyber\Widgets\Form\Entry;
use Fyber\Widgets\Form\Date;
use Fyber\Widgets\Dialog\Message;
use Fyber\Database\Transaction;
use Fyber\Database\Repository;
use Fyber\Database\Criteria;

use Fyber\Widgets\Wrapper\FormWrapper;
use Fyber\Widgets\Container\Panel;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Relatório de contas
 */
class VendaReport2 extends Page
{
    private $form;   // formulário de entrada

    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_relat_venda2'));
        $this->form->setTitle('Relatório de Pendências');
        
        // cria os campos do formulário
        $data_ini = new Date('data_ini');
        $data_fim = new Date('data_fim');
        
        $this->form->addField('Vencimento Inicial', $data_ini, '50%');
        $this->form->addField('Vencimento Final', $data_fim, '50%');
        $this->form->addAction('Gerar', new Action(array($this, 'onGera')));
        $this->form->addAction('PDF', new Action(array($this, 'onGeraPDF')));
        
        parent::add($this->form);
    }

    /**
     * Gera o relatório, baseado nos parâmetros do formulário
     */
    public function onGera()
    {
        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
        $twig = new \Twig\Environment($loader);
        
        // obtém os dados do formulário
        $dados = $this->form->getData();

        // joga os dados de volta ao formulário
        $this->form->setData($dados);
        
        // lê os campos do formulário, converte para o padrão americano
        $data_ini = $dados->data_ini;
        $data_fim = $dados->data_fim;
        
        // vetor de parâmetros para o template
        $replaces = array();
        $replaces['data_ini'] = $dados->data_ini;
        $replaces['data_fim'] = $dados->data_fim;
        
        try
        {
            // inicia transação com o banco 'fyber'
            Transaction::open('fyber');

            // instancia um repositório da classe Conta
            $repositorio = new Repository('View_Venda2');

            // cria um critério de seleção por intervalo de datas
            $criterio = new Criteria;
            $criterio->setProperty('order', 'Vencimento');
            
            if ($dados->data_ini)
                $criterio->add('Vencimento', '>=', $data_ini);
            if ($dados->data_fim)
                $criterio->add('Vencimento', '<=', $data_fim);
            
            // lê todas vendas que satisfazem ao critério
            $vendas = $repositorio->load($criterio);
            
            if ($vendas)
            {
                foreach ($vendas as $venda)
                {
                    $venda_array = $venda->toArray();
                   // $venda_array['nome_cliente'] = $venda->cliente->nome;
                    $replaces['vendas'][] = $venda_array;
                }
            }
            // finaliza a transação
            Transaction::close();
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
        $content = $twig->render('pendentes_report.html', $replaces);
        
       // $title = 'Vendas';
       // $title.= (!empty($dados->data_ini)) ? ' de '  . $dados->data_ini : '';
       // $title.= (!empty($dados->data_fim)) ? ' até ' . $dados->data_fim : '';
       $title = '';
        // cria um painél para conter o formulário
        $panel = new Panel($title);
        $panel->add($content);
        
        parent::add($panel);
        
        return $content;
    }
    
    /**
     * Gera o relatório em PDF, baseado nos parâmetros do formulário
     */
    public function onGeraPDF($param)
    {
        // gera o relatório em HTML primeiro
        $html = $this->onGera($param);
        
        $options = new Options();
        $options->set('dpi', '128');

        // DomPDF converte o HTML para PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Escreve o arquivo e abre em tela
        $filename = 'tmp/Relatório_Pendentes.pdf';
        if (is_writable('tmp'))
        {
            file_put_contents($filename, $dompdf->output());
            echo "<script>window.open('{$filename}');</script>";
        }
        else
        {
            new Message('error', 'Permissão negada em: ' . $filename);
        }
    }
}