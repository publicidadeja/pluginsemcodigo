<?php
// Verifica se tem permissão
if (!current_user_can('manage_options')) {
    wp_die(__('Você não tem permissão para acessar esta página.'));
}
?>

<div class="gma-wrap">
    <h1 class="gma-main-title">Calendário de Campanhas</h1>
    <div class="gma-calendar-container">
        <div id="gma-calendar"></div>
    </div>
</div>

<!-- Estilos CSS inline -->
<style>
.gma-calendar-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.gma-evento-marketing {
    background-color: #4a90e2 !important;
    border-color: #357abd !important;
    color: white !important;
}

.gma-evento-aprovacao {
    background-color: #2ecc71 !important;
    border-color: #27ae60 !important;
    color: white !important;
}

.fc-event {
    cursor: pointer;
}

.fc-toolbar-title {
    font-size: 1.5em !important;
    font-weight: bold;
}

.fc-header-toolbar {
    margin-bottom: 1.5em !important;
}

.fc-button {
    background-color: #2271b1 !important;
    border-color: #2271b1 !important;
}

.fc-button:hover {
    background-color: #135e96 !important;
    border-color: #135e96 !important;
}
</style>

<!-- Inclusão do FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<!-- JavaScript inline -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('gma-calendar');
    
    // Obtém os eventos via PHP
    var eventos = <?php echo json_encode(gma_obter_eventos_calendario()); ?>;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        events: eventos,
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                return false;
            }
        },
        eventDidMount: function(info) {
            // Adiciona tooltip
            info.el.title = info.event.title;
        }
    });
    
    calendar.render();
});
</script>
