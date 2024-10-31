<?php
/*----------------------------------------------------------------------------------------------------------------------
Plugin Name: Qwanturank
Description: Widget affichant le classement Qwanturank
Version: 1.02
Author: Qwanturank.news
Author URI: https://qwanturank.news
Plugin URI: https://qwanturank.news

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

----------------------------------------------------------------------------------------------------------------------*/



add_action( 'widgets_init', 'qwanturank_load_widget' );
function qwanturank_load_widget() {
    register_widget( 'qwanturank_widget' );
}


function get_classement(){
	
	$pxn_classement_qwanturank = get_transient( "pxn_classement_qwanturank" );
	
	if (!$pxn_classement_qwanturank) {
	
		$response = wp_remote_get( "https://qwanturank.news/wp-json/classement/qwanturank?timestamp=".date("Ymd-Hi") );
		$response_json = json_decode($response["body"]);
		$update = $response_json->update;
		$date = substr($update,6,2)."/".substr($update,4,2)."/".substr($update,0,4)." à ".substr($update,9,5);
		$classement = $response_json->classement;
		
		$pxn_classement_qwanturank = array(
			"date" => $date,
			"classement" => $classement
		);

		set_transient( 'pxn_classement_qwanturank',$pxn_classement_qwanturank_value , 60*60);
	
	}
			
	return $pxn_classement_qwanturank;
}
 
 
 
 
 
class qwanturank_widget extends WP_Widget {
 
	function __construct() {
		parent::__construct(
			'qwanturank_widget', 
			'Classement Qwanturank', 
			array( 
				'description' => 'Suivi du classement Qwanturank',
			) 
		);
	}
 
	public function widget( $args, $instance ) {
		
		$array = get_classement();
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		$nb = (int) $instance['nb'];
		if ($instance[ 'links' ] == "on" ) { $links = 1; }
		if ($instance[ 'nofollow' ] == "on" ) { $st_follow = " rel='nofollow'"; }
		if ($instance[ 'remove_credit' ] == "on" ) { $remove_credit = 1; }

		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
	
		$classement_array = json_decode($array["classement"]);
		
		echo "<style>.pxn_qwanturank_classement{margin:10px;}.pxn_qwanturank_maj{margin:10px;font-size:0.8em;}</style>";
		
		echo "<div class='pxn_qwanturank_classement'>";
		
		for ($i = 0;$i<$nb;$i++){
			echo "<b>#".($i+1)."</b> - ";
			if ($links) { echo "<a target='_blank' ".$st_follow." href='//".$classement_array[$i]."'>"; }
			echo $classement_array[$i];
			if ($links) { echo "</a>"; }
			echo "<br>";
		}
		echo "</div>";
		
		if (!$remove_credit) {
			echo "<div class='pxn_qwanturank_maj'><a target='_blank' href='https://qwanturank.news/classement/'>Classement Qwanturank</a> du ".$array["date"]."<div>";
		}
		
		echo $args['after_widget'];
		
	}

	public function form( $instance ) {
		
		
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Classement Qwanturank';
		$nb = ! empty( $instance['nb'] ) ? $instance['nb'] : 5;
		$links = ! empty( $instance['links'] ) ? $instance['links'] : 1;
		$nofollow = ! empty( $instance['nofollow'] ) ? $instance['nofollow'] : 1;
		$remove_credit = ! empty( $instance['remove_credit'] ) ? $instance['remove_credit'] : 1;

		?>

	<p>

	<label for="<?php echo $this->get_field_id( 'title' ); ?>">Titre</label> 
	<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />

	<label for="<?php echo $this->get_field_id( 'nb' ); ?>">Nombre de sites</label> 
	<input type="number" min="3" max="50" class="widefat" id="<?php echo $this->get_field_id( 'nb' ); ?>"  name="<?php echo $this->get_field_name( 'nb' ); ?>" value="<?php echo esc_attr( $nb ) ; ?>" />

	<p>
	<input type="checkbox" class="widefat" id="<?php echo $this->get_field_id( 'links' ); ?>"  name="<?php echo $this->get_field_name( 'links' ); ?>" <?php checked( $instance[ 'links' ], 'on' ); ?> />
	<label for="<?php echo $this->get_field_id( 'links' ); ?>">Liens vers les sites</label> 
	</p>
	
	<p>
	<input type="checkbox" class="widefat" id="<?php echo $this->get_field_id( 'nofollow' ); ?>"  name="<?php echo $this->get_field_name( 'nofollow' ); ?>" <?php checked( $instance[ 'nofollow' ], 'on' ); ?> />
	<label for="<?php echo $this->get_field_id( 'nofollow' ); ?>">Liens NoFollow</label> 
	</p>

	<p>
	<input type="checkbox" class="widefat" id="<?php echo $this->get_field_id( 'remove_credit' ); ?>"  name="<?php echo $this->get_field_name( 'remove_credit' ); ?>" <?php checked( $instance[ 'remove_credit' ], 'on' ); ?> />
	<label for="<?php echo $this->get_field_id( 'remove_credit' ); ?>">Supprimer le lien vers le classement complet</label> 
	</p>
	


	</p>
		<?php 
	}
     
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['nb'] = ( ! empty( $new_instance['nb'] ) ) ? strip_tags( $new_instance['nb'] ) : '';
		$instance['links'] = ( ! empty( $new_instance['links'] ) ) ? strip_tags( $new_instance['links'] ) : '';
		$instance['nofollow'] = ( ! empty( $new_instance['nofollow'] ) ) ? strip_tags( $new_instance['nofollow'] ) : '';
		$instance['remove_credit'] = ( ! empty( $new_instance['remove_credit'] ) ) ? strip_tags( $new_instance['remove_credit'] ) : '';

		return $instance;
	}
	
}