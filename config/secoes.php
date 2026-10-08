<?php
// =====================================================================
// config/secoes.php — Secções do site geríveis no painel (CMS).
//
// Cada secção tem:
//   nome, descricao     → como aparece no painel
//   ocultavel           → pode ser escondida do site
//   gerir               → páginas próprias do painel com mais conteúdo da secção
//   campos              → textos/imagens da secção (chave => definição)
//   grupos              → listas de itens repetíveis (destaques, passos, planos...)
//
// Tipos de campo:
//   texto    → linha simples (títulos, rótulos, mensagens)
//   curto    → editor de texto com negrito/itálico/link (parágrafo curto)
//   rico     → editor de texto completo (vários parágrafos, listas, subtítulos)
//   lista    → editor de lista com marcadores (cada marcador = um item)
//   imagem   → upload de imagem
//   icone    → nome de um ícone Material Design Icons (ex.: mdi-whatsapp)
//   checkbox → sim/não
//
// "padrao" é o conteúdo inicial (o texto do site original). É gravado na
// base de dados na instalação e usado sempre que um campo não existe na BD.
// =====================================================================

return [

    'confianca' => [
        'nome'      => 'Faixa de confiança',
        'descricao' => 'Os 4 destaques logo abaixo do slider.',
        'ocultavel' => true,
        'campos'    => [],
        'grupos'    => [
            'itens' => [
                'nome'   => 'Destaques',
                'campos' => [
                    'icone'  => ['tipo' => 'icone', 'rotulo' => 'Ícone'],
                    'titulo' => ['tipo' => 'texto', 'rotulo' => 'Título', 'obrigatorio' => true, 'max' => 80],
                    'texto'  => ['tipo' => 'curto', 'rotulo' => 'Texto'],
                ],
                'padrao' => [
                    ['icone' => 'mdi-storefront-outline', 'titulo' => 'Atendimento presencial', 'texto' => 'Escritório em São Paulo, Luanda'],
                    ['icone' => 'mdi-whatsapp', 'titulo' => 'Resposta rápida', 'texto' => 'Fale connosco directamente no WhatsApp'],
                    ['icone' => 'mdi-file-document-check-outline', 'titulo' => 'Preço claro à partida', 'texto' => 'Sabe quanto paga antes de começarmos'],
                    ['icone' => 'mdi-shield-check-outline', 'titulo' => 'Empresa registada', 'texto' => 'Axiora — Comércio Geral e Prestação de Serviços LDA'],
                ],
            ],
        ],
    ],

    'servicos' => [
        'nome'      => 'Serviços',
        'descricao' => 'Cabeçalho da secção de serviços. Os cartões gerem-se em Conteúdo › Serviços.',
        'ocultavel' => true,
        'gerir'     => ['Gerir cartões de serviços' => '/admin/conteudo/servicos'],
        'campos'    => [
            'eyebrow' => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Os nossos serviços'],
            'titulo'  => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'Serviços essenciais, tratados por quem conhece Luanda.'],
            'texto'   => ['tipo' => 'curto', 'rotulo' => 'Texto de apoio', 'padrao' => 'Escolha o serviço, envie-nos uma mensagem e tratamos do resto. Sem idas e voltas desnecessárias — acompanhamos cada passo consigo.'],
        ],
    ],

    'como-funciona' => [
        'nome'      => 'Como funciona',
        'descricao' => 'Os passos do atendimento, em fundo escuro.',
        'ocultavel' => true,
        'campos'    => [
            'eyebrow'           => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Como funciona'],
            'titulo'            => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'Do primeiro contacto ao assunto resolvido, em 4 passos.'],
            'botao_texto'       => ['tipo' => 'texto', 'rotulo' => 'Texto do botão', 'padrao' => 'Começar agora'],
            'mensagem_whatsapp' => ['tipo' => 'texto', 'rotulo' => 'Mensagem do WhatsApp do botão', 'ajuda' => 'Vazio = mensagem geral das Definições.', 'padrao' => ''],
            'nota'              => ['tipo' => 'texto', 'rotulo' => 'Nota ao lado do botão', 'ajuda' => 'Vazio = horário curto das Definições.', 'padrao' => ''],
        ],
        'grupos'    => [
            'passos' => [
                'nome'   => 'Passos',
                'campos' => [
                    'icone'  => ['tipo' => 'icone', 'rotulo' => 'Ícone'],
                    'titulo' => ['tipo' => 'texto', 'rotulo' => 'Título', 'obrigatorio' => true, 'max' => 80],
                    'texto'  => ['tipo' => 'curto', 'rotulo' => 'Texto'],
                ],
                'padrao' => [
                    ['icone' => 'mdi-message-text-outline', 'titulo' => 'Fale connosco', 'texto' => 'Envie uma mensagem no WhatsApp, ligue ou visite o nosso escritório em São Paulo.'],
                    ['icone' => 'mdi-clipboard-text-search-outline', 'titulo' => 'Analisamos o seu caso', 'texto' => 'Explicamos o que é preciso, os prazos e o custo — tudo claro antes de avançar.'],
                    ['icone' => 'mdi-cog-outline', 'titulo' => 'Tratamos de tudo', 'texto' => 'A nossa equipa executa o serviço e mantém-no informado em cada etapa.'],
                    ['icone' => 'mdi-check-decagram-outline', 'titulo' => 'Assunto resolvido', 'texto' => 'Entregamos o resultado e continuamos disponíveis para o que precisar a seguir.'],
                ],
            ],
        ],
    ],

    'sobre' => [
        'nome'      => 'Sobre nós',
        'descricao' => 'Apresentação da empresa, equipa, missão, visão e valores.',
        'ocultavel' => true,
        'campos'    => [
            'eyebrow'       => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Sobre a Axiora'],
            'titulo'        => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'Uma empresa angolana feita para facilitar a sua vida.'],
            'texto'         => ['tipo' => 'rico', 'rotulo' => 'Texto', 'padrao' => '<p>A <strong>Axiora — Comércio Geral e Prestação de Serviços LDA</strong> nasceu em Luanda com uma missão clara: oferecer soluções práticas e confiáveis que facilitam a vida e os negócios dos nossos clientes. Num único lugar, encontra assessoria de viagens, gestão de viaturas, serviços técnicos e apoio logístico.</p><p>O nosso diferencial está no atendimento próximo, na orientação clara e no foco em soluções rápidas. Para nós, <strong>qualidade e transparência</strong> são o mínimo que cada cliente merece.</p>'],
            'imagem'        => ['tipo' => 'imagem', 'rotulo' => 'Imagem principal', 'ajuda' => 'Vertical ou quadrada, ex.: 900×1000.', 'padrao' => 'assets/images/banner.webp'],
            'imagem_alt'    => ['tipo' => 'texto', 'rotulo' => 'Descrição da imagem', 'padrao' => 'Equipa Axiora em Luanda'],
            'equipa_1'      => ['tipo' => 'imagem', 'rotulo' => 'Foto da equipa 1', 'ajuda' => 'Quadrada, ex.: 200×200.', 'padrao' => 'assets/images/membros/1.jpeg'],
            'equipa_2'      => ['tipo' => 'imagem', 'rotulo' => 'Foto da equipa 2', 'ajuda' => 'Quadrada, ex.: 200×200.', 'padrao' => 'assets/images/membros/2.jpeg'],
            'cartao_titulo' => ['tipo' => 'texto', 'rotulo' => 'Cartão da equipa — título', 'padrao' => 'Equipa local'],
            'cartao_texto'  => ['tipo' => 'texto', 'rotulo' => 'Cartão da equipa — texto', 'padrao' => 'Atendimento feito por pessoas, não por robôs.'],
            'missao'        => ['tipo' => 'rico', 'rotulo' => 'Missão', 'padrao' => '<p>Prestar serviços com qualidade, transparência e eficiência, oferecendo soluções práticas em assessoria de viagens, gestão de viaturas, serviços técnicos e apoio logístico.</p>'],
            'visao'         => ['tipo' => 'rico', 'rotulo' => 'Visão', 'padrao' => '<p>Ser uma empresa de referência em Angola na prestação de serviços e comercialização, reconhecida pela confiança, qualidade, profissionalismo e pelos resultados que gera para os clientes.</p>'],
            'valores'       => ['tipo' => 'lista', 'rotulo' => 'Valores', 'ajuda' => 'Um valor por marcador.', 'padrao' => '<ul><li>Integridade</li><li>Responsabilidade</li><li>Profissionalismo</li><li>Transparência</li><li>Compromisso com o cliente</li><li>Qualidade nos serviços</li></ul>'],
        ],
    ],

    'porque' => [
        'nome'      => 'Porquê escolher-nos',
        'descricao' => 'Motivos para escolher a Axiora e o público que servimos.',
        'ocultavel' => true,
        'campos'    => [
            'eyebrow'        => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Porquê escolher-nos'],
            'titulo'         => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'O que faz os nossos clientes voltarem.'],
            'imagem'         => ['tipo' => 'imagem', 'rotulo' => 'Imagem', 'ajuda' => 'Horizontal, ex.: 1200×900.', 'padrao' => 'assets/images/equipa-reuniao.webp'],
            'imagem_alt'     => ['tipo' => 'texto', 'rotulo' => 'Descrição da imagem', 'padrao' => 'Reunião de trabalho da equipa'],
            'publico_titulo' => ['tipo' => 'texto', 'rotulo' => 'Título do cartão "público"', 'padrao' => 'Quem servimos'],
        ],
        'grupos'    => [
            'motivos' => [
                'nome'   => 'Motivos',
                'campos' => [
                    'icone'  => ['tipo' => 'icone', 'rotulo' => 'Ícone'],
                    'titulo' => ['tipo' => 'texto', 'rotulo' => 'Título', 'obrigatorio' => true, 'max' => 100],
                    'texto'  => ['tipo' => 'curto', 'rotulo' => 'Texto'],
                ],
                'padrao' => [
                    ['icone' => 'mdi-view-grid-outline', 'titulo' => 'Vários serviços, um só contacto', 'texto' => 'Da assessoria de viagens à manutenção técnica — resolve tudo sem andar de um lado para o outro.'],
                    ['icone' => 'mdi-account-heart-outline', 'titulo' => 'Atendimento próximo e personalizado', 'texto' => 'Orientamos com clareza, passo a passo, até o processo estar concluído.'],
                    ['icone' => 'mdi-timer-sand', 'titulo' => 'Rapidez sem burocracia', 'texto' => 'Valorizamos o seu tempo. Soluções ágeis e sem etapas desnecessárias.'],
                    ['icone' => 'mdi-eye-outline', 'titulo' => 'Transparência total', 'texto' => 'Informação clara sobre preços, prazos e processos. Sem surpresas nem letras pequenas.'],
                ],
            ],
            'publico' => [
                'nome'   => 'Público que servimos',
                'campos' => [
                    'icone'  => ['tipo' => 'icone', 'rotulo' => 'Ícone'],
                    'titulo' => ['tipo' => 'texto', 'rotulo' => 'Texto', 'obrigatorio' => true, 'max' => 80],
                ],
                'padrao' => [
                    ['icone' => 'mdi-account-group-outline', 'titulo' => 'Particulares & famílias'],
                    ['icone' => 'mdi-briefcase-outline', 'titulo' => 'Profissionais independentes'],
                    ['icone' => 'mdi-office-building-outline', 'titulo' => 'Pequenas & médias empresas'],
                    ['icone' => 'mdi-car-outline', 'titulo' => 'Proprietários de viaturas'],
                    ['icone' => 'mdi-earth', 'titulo' => 'Operações internacionais'],
                ],
            ],
        ],
    ],

    'testemunhos' => [
        'nome'      => 'Testemunhos',
        'descricao' => 'Cabeçalho da secção de testemunhos. As opiniões gerem-se em Conteúdo › Testemunhos.',
        'ocultavel' => true,
        'gerir'     => ['Gerir testemunhos' => '/admin/conteudo/testemunhos'],
        'campos'    => [
            'eyebrow' => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Testemunhos'],
            'titulo'  => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'O que dizem os nossos clientes.'],
        ],
    ],

    'noticias' => [
        'nome'      => 'Blog & Notícias',
        'descricao' => 'Os 3 artigos mais recentes na homepage. Os artigos gerem-se em Blog › Artigos.',
        'ocultavel' => true,
        'gerir'     => ['Gerir artigos' => '/admin/blog'],
        'campos'    => [
            'eyebrow'     => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Blog & Notícias'],
            'titulo'      => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'Novidades, comunicados e dicas práticas.'],
            'texto'       => ['tipo' => 'curto', 'rotulo' => 'Texto de apoio', 'padrao' => 'Acompanhe o que há de novo na Axiora e aprenda a tratar dos seus assuntos com menos burocracia.'],
            'botao_texto' => ['tipo' => 'texto', 'rotulo' => 'Texto do botão', 'padrao' => 'Ver todas as publicações'],
        ],
    ],

    'websites' => [
        'nome'      => 'Websites (serviço digital)',
        'descricao' => 'Promoção de criação de websites, com os planos e preços.',
        'ocultavel' => true,
        'campos'    => [
            'eyebrow'       => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Serviço digital'],
            'titulo'        => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'O seu negócio também merece um website profissional.'],
            'texto'         => ['tipo' => 'curto', 'rotulo' => 'Texto', 'padrao' => 'Criamos websites modernos para empresas que querem ser encontradas no Google e transmitir confiança online.'],
            'inclui_titulo' => ['tipo' => 'texto', 'rotulo' => 'Título da lista', 'padrao' => 'Incluído em todos os planos'],
            'inclui'        => ['tipo' => 'lista', 'rotulo' => 'Incluído em todos os planos', 'ajuda' => 'Um item por marcador.', 'padrao' => '<ul><li>Design personalizado</li><li>Adaptado a telemóveis</li><li>SEO para Google incluído</li><li>Integração com WhatsApp</li><li>Suporte após o lançamento</li></ul>'],
        ],
        'grupos'    => [
            'planos' => [
                'nome'   => 'Planos',
                'campos' => [
                    'titulo'            => ['tipo' => 'texto', 'rotulo' => 'Nome do plano', 'obrigatorio' => true, 'max' => 60],
                    'preco'             => ['tipo' => 'texto', 'rotulo' => 'Preço', 'max' => 30, 'ajuda' => 'Ex.: 169.000'],
                    'moeda'             => ['tipo' => 'texto', 'rotulo' => 'Moeda', 'max' => 10],
                    'nota'              => ['tipo' => 'texto', 'rotulo' => 'Nota por baixo do preço', 'max' => 100],
                    'lista'             => ['tipo' => 'lista', 'rotulo' => 'O que inclui', 'ajuda' => 'Um item por marcador.'],
                    'destaque'          => ['tipo' => 'checkbox', 'rotulo' => 'Plano em destaque (fundo escuro)'],
                    'selo'              => ['tipo' => 'texto', 'rotulo' => 'Selo', 'max' => 30, 'ajuda' => 'Ex.: Mais completo. Só aparece no plano em destaque.'],
                    'botao_texto'       => ['tipo' => 'texto', 'rotulo' => 'Texto do botão', 'max' => 40],
                    'mensagem_whatsapp' => ['tipo' => 'texto', 'rotulo' => 'Mensagem do WhatsApp', 'max' => 255],
                ],
                'padrao' => [
                    ['titulo' => 'Website', 'preco' => '169.000', 'moeda' => 'Kz', 'nota' => 'Website completo e funcional', 'lista' => '<ul><li>Site institucional completo</li><li>Entrega em 2 semanas</li></ul>', 'destaque' => 0, 'selo' => '', 'botao_texto' => 'Pedir orçamento', 'mensagem_whatsapp' => 'Olá! Quero saber mais sobre o plano Website (169.000 Kz).'],
                    ['titulo' => 'Website + Blog', 'preco' => '259.000', 'moeda' => 'Kz', 'nota' => 'Com blog e gestão de conteúdos', 'lista' => '<ul><li>Tudo do plano Website</li><li>Blog para publicar artigos</li><li>Gestão de conteúdos: edite textos e imagens sem programar</li></ul>', 'destaque' => 1, 'selo' => 'Mais completo', 'botao_texto' => 'Pedir orçamento', 'mensagem_whatsapp' => 'Olá! Quero saber mais sobre o plano Website + Blog (259.000 Kz).'],
                ],
            ],
        ],
    ],

    'contacto' => [
        'nome'      => 'Contacto',
        'descricao' => 'Cabeçalho e botões da secção de contacto. Telefone, email, morada, horário e mapa estão nas Definições.',
        'ocultavel' => true,
        'gerir'     => ['Editar contactos e mapa' => '/admin/definicoes'],
        'campos'    => [
            'eyebrow'           => ['tipo' => 'texto', 'rotulo' => 'Etiqueta', 'padrao' => 'Contacto'],
            'titulo'            => ['tipo' => 'texto', 'rotulo' => 'Título', 'padrao' => 'Venha falar connosco ou envie uma mensagem.'],
            'mensagem_whatsapp' => ['tipo' => 'texto', 'rotulo' => 'Mensagem do botão WhatsApp', 'padrao' => 'Olá! Gostaria de mais informações sobre os vossos serviços.'],
        ],
    ],

    'rodape' => [
        'nome'      => 'Rodapé',
        'descricao' => 'Chamada para acção e apresentação no fundo de todas as páginas.',
        'ocultavel' => false,
        'campos'    => [
            'cta_titulo' => ['tipo' => 'texto', 'rotulo' => 'Chamada — título', 'padrao' => 'Pronto para resolver o seu assunto?'],
            'cta_texto'  => ['tipo' => 'curto', 'rotulo' => 'Chamada — texto', 'padrao' => 'Envie-nos uma mensagem — respondemos no horário de atendimento.'],
            'cta_botao'  => ['tipo' => 'texto', 'rotulo' => 'Chamada — texto do botão', 'padrao' => 'Falar no WhatsApp'],
            'descricao'  => ['tipo' => 'curto', 'rotulo' => 'Texto por baixo do logótipo', 'padrao' => 'Soluções práticas e confiáveis em Luanda: viagens, viaturas, serviços técnicos, câmbio e logística.'],
        ],
    ],
];
