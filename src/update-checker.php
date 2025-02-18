<?php


function logsfori_check_for_update( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    // URL de votre endpoint de mise à jour
    $update_url = 'https://plugin-manager.atomikagency.fr/api/pull/d0a236e7-1ca7-4657-81c2-e9d2468d3184';

    // Récupère les données de mise à jour
    $response = wp_remote_get( $update_url );
    if ( is_wp_error( $response ) ) {
        return $transient;
    }

    $update_data = json_decode( wp_remote_retrieve_body( $response) );

    // Compare les versions
    $plugin_slug = plugin_basename( LOGSFORI_PLUGIN_DIR.'/logsfori.php' );
    $current_version = get_plugin_data( LOGSFORI_PLUGIN_DIR.'/logsfori.php' )['Version'];

    if ( version_compare( $current_version, $update_data->version, '<' ) ) {
        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => $plugin_slug,
            'new_version' => $update_data->version,
            'package'     => $update_data->package,
            'url'         => 'https://atomikagency.fr/', // Page de détails du plugin
        ];
    }

    return $transient;
}
add_filter( 'site_transient_update_plugins', 'logsfori_check_for_update' );
