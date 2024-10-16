<?php

use Fyber\Control\Page;
use Fyber\Widgets\Dialog\Message;
use Fyber\Database\Transaction;
use Fyber\Widgets\Container\Panel;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Relatório de Fyber
 */
class ClienteReport extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
        $twig = new \Twig\Environment($loader);

        // vetor de parâmetros para o template
        $replaces = array();

        try {
            // inicia transação com o banco 'Fyber'
            Transaction::open('Fyber');
            $replaces['Fyber'] = View_Cliente::all();
            Transaction::close(); // finaliza a transação
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }

        $content = $twig->render('ClienteReport.html', $replaces);

        //Chamada do PDF
        $options = new Options;
        $options->set('dpi', 128);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'tmp/Cliente_RadioFyber.pdf';

        file_put_contents($filename, $dompdf->output());
        echo "<script> window.open('{$filename}')</script>";

        // cria um painél para conter o formulário
        $panel = new Panel('Relatório de Clientes');
        $panel->add($content);

        parent::add($panel);


    }

}

