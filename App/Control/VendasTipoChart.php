<?php
use Fyber\Control\Page;
use Fyber\Widgets\Dialog\Message;
use Fyber\Database\Transaction;
use Fyber\Widgets\Container\Panel;

/**
 * Vendas por tipo
 */
class VendasTipoChart extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

	$loader = new \Twig\Loader\FilesystemLoader('App/Resources');
	$twig = new \Twig\Environment($loader);

        try
        {
            // inicia transação com o banco 'Fyber'
            Transaction::open('Fyber');
            $vendas = View_Tipo::getVendasTipo();
            Transaction::close(); // finaliza a transação
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
        
        // vetor de parâmetros para o template
        $replaces = array();
        $replaces['title'] = 'Vendas Status';
        $replaces['labels'] = json_encode(array_keys($vendas));
        $replaces['data']  = json_encode(array_values($vendas));
        
        $content = $twig->render('vendas_tipo.html', $replaces);
        
        // cria um painél para conter o formulário
        $panel = new Panel('Situação Financeira');
        $panel->add($content);
        
        parent::add($panel);
    }
}
