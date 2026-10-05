<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Artigo;
use App\Models\ConfiguracaoSite;
use App\Models\HeroSlide;
use App\Models\Servico;
use App\Models\Testemunho;

class HomeController extends Controller
{
    public function index(): void
    {
        $config = ConfiguracaoSite::publicas();

        $dados = [
            'heroSlides'  => (new HeroSlide())->todosAtivos(),
            'servicos'    => (new Servico())->todosAtivos(),
            'testemunhos' => (new Testemunho())->todosAtivos(),
            'artigos'     => (new Artigo())->recentes(3),
            'paginaAtual' => 'inicio',
        ];

        $horarios = [
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '08:00', 'closes' => '18:00'],
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Saturday', 'opens' => '09:00', 'closes' => '13:00'],
        ];

        $jsonld = [
            '@context'  => 'https://schema.org',
            '@type'     => 'LocalBusiness',
            'name'      => NOME_EMPRESA,
            'url'       => URL_BASE . '/',
            'logo'      => URL_BASE . '/assets/images/logo.png',
            'image'     => URL_BASE . '/assets/images/hero/luanda.webp',
            'telephone' => ConfiguracaoSite::telefoneLink(),
            'email'     => $config['email'],
            'address'   => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $config['endereco'],
                'addressLocality' => 'Luanda',
                'addressCountry'  => 'AO',
            ],
            'openingHoursSpecification' => $horarios,
        ];

        $this->view('home/index', $dados, [
            'titulo'    => NOME_SITE,
            'descricao' => DESCRICAO_SITE,
            'canonical' => URL_BASE . '/',
            'jsonld'    => $jsonld,
            'preload'   => !empty($dados['heroSlides'][0]['imagem']) ? $dados['heroSlides'][0]['imagem'] : null,
        ]);
    }

    /**
     * Verificação de saúde para o Docker/Coolify: confirma que o PHP responde
     * e que a base de dados está acessível.
     */
    public function saude(): void
    {
        try {
            pdo()->query('SELECT 1');
            $this->json(['estado' => 'ok']);
        } catch (\Throwable $e) {
            $this->json(['estado' => 'erro'], 503);
        }
    }
}
