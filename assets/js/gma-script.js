(function($) {
    $(document).ready(function() {
        // Verificação de dependências
        if (typeof $ === 'undefined' || typeof Swiper === 'undefined' || 
            typeof gsap === 'undefined' || typeof Swal === 'undefined') {
            console.error('jQuery, Swiper, GSAP ou SweetAlert2 não estão carregados corretamente.');
            return;
        }

        if (typeof gmaAjax === 'undefined') {
            console.error('O objeto gmaAjax não está definido. Verifique se wp_localize_script está sendo chamado corretamente.');
            return;
        }

        // Inicialização do Swiper
        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 30,
            centeredSlides: true,
            loop: false,
            grabCursor: true,
            speed: 500,
            allowTouchMove: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 'auto',
                    spaceBetween: 30
                }
            }
        });

        // Variável para controlar processamento de cliques
        let isProcessing = false;

        // Função de debounce para evitar múltiplos cliques
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Função para lidar com cliques nos botões
        function handleButtonClick(event) {
            event.preventDefault();
            event.stopPropagation(); // Previne propagação do evento
            
            // Verifica se já está processando
            if (isProcessing) {
                return false; // Retorna false explicitamente
            }
            
            // Marca como processando
            isProcessing = true;
            
            const $button = $(this);
            $button.prop('disabled', true); // Desabilita o botão imediatamente
            
            const $material = $button.closest('.gma-material');
            const materialId = $material.data('material-id');
            const acao = $button.hasClass('gma-aprovar') ? 'aprovar' : 'reprovar';
            
            $.ajax({
                url: gmaAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gma_' + acao + '_material',
                    material_id: materialId,
                    nonce: gmaAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Material ' + (acao === 'aprovar' ? 'aprovado' : 'reprovado') + ' com sucesso!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $material.removeClass('status-aprovado status-reprovado status-pendente')
                                .addClass('status-' + acao);
                        $material.find('.gma-status')
                                .text('Status: ' + acao.charAt(0).toUpperCase() + acao.slice(1));
                        $button.siblings().prop('disabled', false);
                        
                        gsap.to($material, {
                            duration: 0.3,
                            scale: 1.05,
                            yoyo: true,
                            repeat: 1,
                            ease: "power2.inOut",
                            onComplete: function() {
                                swiper.slideNext();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Erro: ' + response.data.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Erro ao processar a solicitação. Por favor, tente novamente.'
                    });
                },
                complete: function() {
                    setTimeout(() => {
                        isProcessing = false;
                        $button.prop('disabled', false);
                    }, 500); // Delay para garantir que não haja duplo processamento
                }
            });
        }

        // Aplicar debounce na função de clique
        const debouncedHandler = debounce(handleButtonClick, 250);

        // Event listeners com tratamento específico para mobile
        $(document).on('click touchstart.once', '.gma-aprovar, .gma-reprovar', function(e) {
            if (e.type === 'touchstart') {
                e.preventDefault();
                $(this).off('click');
            }
            debouncedHandler.call(this, e);
        });

        // Resto do código permanece igual...
        $(document).on('click', '.gma-editar', function(event) {
            event.preventDefault();
            const $material = $(this).closest('.gma-material');
            const $edicao = $material.find('.gma-edicao');
            $edicao.slideToggle(300);
        });

        $(document).on('click', '.gma-cancelar-edicao', function(event) {
            event.preventDefault();
            const $material = $(this).closest('.gma-material');
            const $edicao = $material.find('.gma-edicao');
            $edicao.slideUp(300);
        });

        $(document).on('click', '.gma-salvar-edicao', function(event) {
            event.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const $button = $(this);
            const $material = $button.closest('.gma-material');
            const materialId = $material.data('material-id');
            const alteracaoArte = $material.find('.gma-alteracao-arte').val();
            const novaCopy = $material.find('.gma-copy-edit').val();
            
            $button.prop('disabled', true);
            
            $.ajax({
                url: gmaAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gma_editar_material',
                    material_id: materialId,
                    alteracao_arte: alteracaoArte,
                    nova_copy: novaCopy,
                    nonce: gmaAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Material editado com sucesso!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $material.find('.gma-edicao').slideUp(300);
                        $material.find('.gma-copy').text(novaCopy);
                        $material.removeClass('status-aprovado status-reprovado status-pendente')
                                .addClass('status-pendente');
                        $material.find('.gma-status').text('Status: Pendente');
                        $material.find('.gma-aprovar, .gma-reprovar').prop('disabled', false);
                        
                        gsap.from($material.find('.gma-copy'), {
                            duration: 0.5,
                            opacity: 0,
                            y: 10,
                            ease: "power2.out"
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Erro: ' + response.data.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Erro ao processar a solicitação. Por favor, tente novamente.'
                    });
                },
                complete: function() {
                    setTimeout(() => {
                        isProcessing = false;
                        $button.prop('disabled', false);
                    }, 500);
                }
            });
        });

        // Lightbox
        $(document).on('click', '.lightbox-trigger', function(e) {
            e.preventDefault();
            const imageUrl = $(this).attr('src');
            $('#lightboxImage').attr('src', imageUrl);
            $('#imageLightbox').fadeIn('fast');
        });

        $(document).on('click', '.close-lightbox, .lightbox', function() {
            $('#imageLightbox').fadeOut('fast');
        });

        // Eventos de redimensionamento e foco
        $(window).on('resize', function() {
            swiper.update();
        });

        $(document).on('focus', '.gma-alteracao-arte, .gma-copy-edit', function() {
            swiper.allowTouchMove = false;
        });

        $(document).on('blur', '.gma-alteracao-arte, .gma-copy-edit', function() {
            swiper.allowTouchMove = true;
        });
    });
})(jQuery);
