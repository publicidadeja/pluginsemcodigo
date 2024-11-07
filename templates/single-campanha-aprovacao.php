<?php
// Mantenha os enqueues existentes
wp_enqueue_script('jquery');
wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);
wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js', array(), null, true);
wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
wp_enqueue_script('gma-script', plugin_dir_url(__FILE__) . '../assets/js/gma-script.js', array('jquery', 'swiper', 'gsap', 'sweetalert2'), '1.0.0', true);

wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css');

get_header();

$campanha_id = get_query_var('campanha_id'); 
$campanha = gma_obter_campanha($campanha_id);
$materiais = gma_listar_materiais($campanha_id);

if ($campanha) :
?>

<style>
    .gma-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px;
        box-sizing: border-box;
    }

    .gma-title {
        font-size: clamp(1.5rem, 4vw, 2.5rem);
        text-align: center;
        margin: 20px 0;
        color: #333;
    }

    .swiper-container {
        width: 100%;
        padding: 20px 0;
        overflow: hidden;
        position: relative;
    }

    .swiper-slide {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .gma-material {
        width: 100%;
        max-width: 500px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 10px;
        position: relative; /* Para posicionar o status */
    }

    .gma-material-image-container {
        width: 100%;
        position: relative;
        padding-top: 56.25%; /* Aspect ratio 16:9 */
    }

    .gma-material-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px 12px 0 0;
    }

    .gma-material-content {
        padding: 20px;
    }

    .gma-copy {
        font-size: 16px;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    .gma-status {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.8);
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
        color: #333;
    }

    .gma-status.status-aprovado {
        background: #2ecc71;
        color: #fff;
    }

    .gma-status.status-reprovado {
        background: #e74c3c;
        color: #fff;
    }

    .gma-status.status-pendente {
        background: #f39c12;
        color: #fff;
    }

    .gma-acoes {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }

    .gma-acoes button {
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .gma-aprovar { background-color: #2ecc71; color: white; }
    .gma-reprovar { background-color: #e74c3c; color: white; }
    .gma-editar { background-color: #3498db; color: white; }

    .gma-edicao {
        display: none;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 15px;
    }

    .gma-edicao textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    /* Estilos específicos para mobile */
    @media (max-width: 768px) {
        .swiper-container {
            padding: 10px 0;
        }

        .gma-material {
            margin: 5px;
        }

        .swiper-slide {
            width: 100% !important; /* Força largura total no mobile */
        }

        .gma-material-content {
            padding: 15px;
        }

        .gma-copy {
            font-size: 14px;
        }

        .gma-acoes {
            grid-template-columns: 1fr; /* Botões empilhados no mobile */
        }

        .gma-acoes button {
            width: 100%;
            margin-bottom: 5px;
        }

        .gma-status {
            top: 15px;
            right: 15px;
        }
    }

    /* Ajustes do Lightbox */
    .lightbox {
        display: none;
        position: fixed;
        z-index: 1000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
    }

    .lightbox-content {
        max-width: 90%;
        max-height: 90vh;
        margin: auto;
        display: block;
        position: relative;
        top: 50%;
        transform: translateY(-50%);
    }

    .close-lightbox {
        position: absolute;
        right: 20px;
        top: 20px;
        color: #fff;
        font-size: 30px;
        cursor: pointer;
    }

    /* Estilos para as setas do Swiper */
    .swiper-button-next,
    .swiper-button-prev {
        top: 50%; /* Posiciona as setas no meio da altura */
        transform: translateY(-50%); /* Centraliza verticalmente */
        z-index: 10; /* Garante que as setas fiquem acima dos botões */
        background-color: rgba(0, 0, 0, 0.5); /* Define a cor de fundo das setas */
        color: white; /* Define a cor do texto das setas */
        padding: 10px; /* Define o espaçamento interno das setas */
        border-radius: 50%; /* Define o formato arredondado das setas */
        cursor: pointer; /* Define o cursor do mouse como ponteiro */
    }

    /* Posiciona as setas fora do conteúdo */
    .swiper-button-next {
        right: 20px; /* Ajusta a distância da borda direita */
    }

    .swiper-button-prev {
        left: 20px; /* Ajusta a distância da borda esquerda */
    }
</style>

<div class="gma-container">
    <h1 class="gma-title"><?php echo esc_html($campanha->nome); ?></h1>

    <?php if ($materiais) : ?>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ($materiais as $material) : ?>
                    <div class="swiper-slide">
                        <div class="gma-material" data-material-id="<?php echo esc_attr($material->id); ?>">
                            <div class="gma-material-image-container">
                                <img class="gma-material-image lightbox-trigger" 
                                     src="<?php echo esc_url($material->imagem_url); ?>" 
                                     alt="Material">
                            </div>
                            <div class="gma-material-content">
                                <p class="gma-copy"><?php echo wp_kses_post($material->copy ?? ''); ?></p>
                                <div class="gma-acoes">
                                    <button class="gma-aprovar" data-action="aprovar" <?php echo $material->status_aprovacao === 'aprovado' ? 'disabled' : ''; ?>>Aprovar</button>
                                    <button class="gma-reprovar" data-action="reprovar" <?php echo $material->status_aprovacao === 'reprovado' ? 'disabled' : ''; ?>>Reprovar</button>
                                    <button class="gma-editar" data-action="editar">Editar</button>
                                </div>
                                <div class="gma-edicao">
                                    <h3>Editar Material</h3>
                                    <textarea class="gma-alteracao-arte" rows="4" 
                                              placeholder="Descreva as alterações necessárias"></textarea>
                                    <textarea class="gma-copy-edit" rows="4" 
                                              placeholder="Editar copy"><?php echo esc_textarea($material->copy ?? ''); ?></textarea>
                                    <button class="gma-salvar-edicao" data-material-id="<?php echo esc_attr($material->id); ?>">Salvar</button>
                                    <button class="gma-cancelar-edicao">Cancelar</button>
                                </div>
                            </div>
                            <p class="gma-status status-<?php echo esc_attr($material->status_aprovacao ?? 'pendente'); ?>">
                                <?php echo esc_html(ucfirst($material->status_aprovacao ?? 'Pendente')); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    <?php endif; ?>
</div>

<div id="imageLightbox" class="lightbox">
    <span class="close-lightbox">×</span>
    <img class="lightbox-content" id="lightboxImage" src="" alt="Lightbox Image">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var swiper = new Swiper('.swiper-container', {
        slidesPerView: 1,
        spaceBetween: 30,
        centeredSlides: true,
        loop: false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            // quando a largura da tela for >= 768px
            768: {
                slidesPerView: 'auto',
                spaceBetween: 30
            }
        },
        on: {
            slideChange: function () {
                // Fecha o campo de edição ao mudar de slide
                $('.gma-edicao').hide();
            }
        },
        speed: 500, /* Define a velocidade de transição para 500ms */
        allowTouchMove: true, // Habilita o swipe do usuário
    });
});
</script>

<?php
endif;
get_footer();
?>
