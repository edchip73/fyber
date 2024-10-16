<?php
use Fyber\Control\Page;
use Fyber\Widgets\Dialog\Message;
use Fyber\Database\Transaction;
use Fyber\Widgets\Container\Panel;

/**
 * Pagamentos
 */
class Dashboard extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
        $twig = new \Twig\Environment($loader);

        try {
            // inicia transação com o banco 'Fyber'
            Transaction::open('Fyber');
            $vendas = View_Pagamento::getVendasMes();
            Transaction::close(); // finaliza a transação
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }

        // vetor de parâmetros para o template
        $replaces = array();
        $replaces['title'] = 'Pagamentos de Clientes';
        $replaces['labels'] = json_encode(array_keys($vendas));
        $replaces['data'] = json_encode(array_values($vendas));

        $content = $twig->render('pagamento_mes.html', $replaces);

        // cria um painél para conter o formulário
        $panel = new Panel('Performance de Vendas');
        $panel->add($content);

        parent::add($panel);


    }

}
