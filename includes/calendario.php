// includes/calendario.php
function gma_obter_eventos_calendario() {
    $campanhas = gma_listar_campanhas();
    $eventos = array();
    
    foreach ($campanhas as $campanha) {
        $eventos[] = array(
            'id' => $campanha->id,
            'title' => $campanha->nome,
            'start' => $campanha->data_criacao,
            'url' => admin_url('admin.php?page=gma-editar-campanha&campanha_id=' . $campanha->id),
            'className' => 'gma-evento-' . $campanha->tipo_campanha
        );
    }
    
    return $eventos;
}
