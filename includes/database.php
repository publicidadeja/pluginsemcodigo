<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Função para criar as tabelas do plugin.
 */
function gma_criar_tabelas() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Definição das tabelas
    $tabela_campanhas = $wpdb->prefix . 'gma_campanhas';
    $tabela_materiais = $wpdb->prefix . 'gma_materiais';
    $tabela_categorias = $wpdb->prefix . 'gma_categorias';
    $tabela_estatisticas = $wpdb->prefix . 'gma_estatisticas';
    $tabela_downloads = $wpdb->prefix . 'gma_downloads';
    $tabela_tags = $wpdb->prefix . 'gma_tags';
    $tabela_material_tags = $wpdb->prefix . 'gma_material_tags';
    $tabela_pastas = $wpdb->prefix . 'gma_pastas';
    $tabela_versoes = $wpdb->prefix . 'gma_versoes';
    $tabela_licencas = $wpdb->prefix . 'gma_licencas';

    // SQL para tabela de categorias
    $sql_categorias = "CREATE TABLE $tabela_categorias (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // SQL para tabela de campanhas
    $sql_campanhas = "CREATE TABLE $tabela_campanhas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        cliente varchar(255) NOT NULL,
        categoria_id mediumint(9) DEFAULT NULL,
        data_criacao datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        tipo_campanha VARCHAR(255) NOT NULL DEFAULT 'marketing',
        status_campanha VARCHAR(255) NOT NULL DEFAULT 'rascunho',
        PRIMARY KEY (id),
        FOREIGN KEY (categoria_id) REFERENCES $tabela_categorias(id) ON DELETE SET NULL
    ) $charset_collate;";

    // SQL para tabela de materiais atualizada
    $sql_materiais = "CREATE TABLE $tabela_materiais (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        imagem_url varchar(255) NOT NULL,
        copy text NOT NULL,
        link_canva varchar(255),
        arquivo_id bigint(20) unsigned DEFAULT NULL,
        status_aprovacao VARCHAR(20) NOT NULL DEFAULT 'pendente',
        feedback TEXT,
        data_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        pasta_id mediumint(9) DEFAULT NULL,
        tipo_midia varchar(50) DEFAULT 'imagem',
        versao_atual int DEFAULT 1,
        PRIMARY KEY (id),
        FOREIGN KEY (campanha_id) REFERENCES $tabela_campanhas(id) ON DELETE CASCADE,
        FOREIGN KEY (pasta_id) REFERENCES $tabela_pastas(id) ON DELETE SET NULL
    ) $charset_collate;";

    // SQL para tabela de estatísticas
    $sql_estatisticas = "CREATE TABLE $tabela_estatisticas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        visualizacoes bigint(20) NOT NULL DEFAULT 0,
        cliques bigint(20) NOT NULL DEFAULT 0,
        conversoes bigint(20) NOT NULL DEFAULT 0,
        data_visualizacao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (campanha_id) REFERENCES $tabela_campanhas(id) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL para tabela de downloads
    $sql_downloads = "CREATE TABLE $tabela_downloads (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        campanha_id mediumint(9) NOT NULL,
        material_id mediumint(9) NOT NULL,
        data_download datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (campanha_id) REFERENCES $tabela_campanhas(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES $tabela_materiais(id) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL para tabela de tags
    $sql_tags = "CREATE TABLE $tabela_tags (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // SQL para tabela de relação material-tag
    $sql_material_tags = "CREATE TABLE $tabela_material_tags (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        material_id mediumint(9) NOT NULL,
        tag_id mediumint(9) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (material_id) REFERENCES $tabela_materiais(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES $tabela_tags(id) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL para tabela de pastas
    $sql_pastas = "CREATE TABLE $tabela_pastas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        pasta_pai_id mediumint(9) DEFAULT NULL,
        data_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (pasta_pai_id) REFERENCES $tabela_pastas(id) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL para tabela de versões
    $sql_versoes = "CREATE TABLE $tabela_versoes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        material_id mediumint(9) NOT NULL,
        versao int NOT NULL,
        imagem_url varchar(255) NOT NULL,
        copy text NOT NULL,
        data_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        modificado_por bigint(20) unsigned NOT NULL,
        descricao_mudancas text,
        PRIMARY KEY (id),
        FOREIGN KEY (material_id) REFERENCES $tabela_materiais(id) ON DELETE CASCADE,
        FOREIGN KEY (modificado_por) REFERENCES {$wpdb->users}(ID)
    ) $charset_collate;";

    // SQL para tabela de licenças
    $sql_licencas = "CREATE TABLE $tabela_licencas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        codigo_licenca varchar(32) NOT NULL,
        tipo_licenca varchar(10) NOT NULL DEFAULT 'teste',
        data_ativacao datetime DEFAULT NULL,
        data_expiracao datetime DEFAULT NULL,
        site_url varchar(255) DEFAULT NULL,
        status varchar(20) DEFAULT 'inativo',
        PRIMARY KEY (id),
        UNIQUE KEY codigo_licenca (codigo_licenca)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Criar todas as tabelas
    dbDelta($sql_categorias);
    dbDelta($sql_campanhas);
    dbDelta($sql_materiais);
    dbDelta($sql_estatisticas);
    dbDelta($sql_downloads);
    dbDelta($sql_tags);
    dbDelta($sql_material_tags);
    dbDelta($sql_pastas);
    dbDelta($sql_versoes);
    dbDelta($sql_licencas);
}

/**
 * Função para verificar e atualizar a versão do banco de dados.
 */
function gma_verificar_versao_banco() {
    $versao_atual = get_option('gma_db_version', '1.0');
    $nova_versao = '2.0';
    
    if (version_compare($versao_atual, $nova_versao, '<')) {
        gma_criar_tabelas();
        update_option('gma_db_version', $nova_versao);
    }
}

/**
 * Função para verificar se as tabelas necessárias existem.
 */
function gma_verificar_tabelas() {
    global $wpdb;
    $tabelas_necessarias = [
        'gma_campanhas', 'gma_materiais', 'gma_categorias', 
        'gma_estatisticas', 'gma_downloads', 'gma_tags',
        'gma_material_tags', 'gma_pastas', 'gma_versoes', 'gma_licencas'
    ];

    foreach ($tabelas_necessarias as $tabela) {
        $tabela_nome = $wpdb->prefix . $tabela;
        if ($wpdb->get_var("SHOW TABLES LIKE '$tabela_nome'") != $tabela_nome) {
            gma_criar_tabelas();
            break;
        }
    }
}

// Hooks
add_action('plugins_loaded', 'gma_verificar_versao_banco');
add_action('plugins_loaded', 'gma_verificar_tabelas');
