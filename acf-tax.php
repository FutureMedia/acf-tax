<?php

/*
 *	Advanced Custom Fields - Select boxes for Taxonomy
 *	
 *	Displays selected taxonomy as a multicolumnar list of select boxes, 
 *	replacing the functionality of the original WP sidebar metabox for taxonomies.
 *	The number of columns can be customized to your needs.
 *
 *	Documentation: 
 *	To use it just register_field($class_name, $file_path) in your functions file
 *
 *	@author Future Media Ltd / www.futuremedia.gr / https://github.com/FutureMedia
 *  @author Will Ashworth (updated and brought current to support ACF 3.5.X)
 *
 */
 
 
class Tax_field extends acf_Field
{

	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*	- This function is called when the field class is initalized on each page.
	*	- Here you can add filters / actions and setup any other functionality for your field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
		// do not delete!
    	parent::__construct($parent);
    	
    	// set name / title
    	$this->name = 'tax'; // variable name (no spaces / special characters / etc)
		$this->title = __("Tax",'acf'); // field label (Displayed in edit screens)
		
   	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*	- this function is called from core/field_meta_box.php to create extra options
	*	for your field
	*
	*	@params
	*	- $key (int) - the $_POST obejct key required to save the options to the field
	*	- $field (array) - the field object
	*
	*	@author Brian Zoetewey - Taxonomy Field add-on
	*
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field)
	{
		// defaults
		//$this->set_field_defaults( $field );

		$taxonomies = get_taxonomies( array(), 'objects' );
		ksort( $taxonomies );
		$tax_choices = array();
		foreach( $taxonomies as $tax ) $tax_choices[ $tax->name ] = $tax->label;
		$col_choices = array();
		for( $i = 1; $i <= 5; $i++ ) $col_choices[$i] = $i;
		
		?>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Taxonomy' , 'acf' ); ?></label>
					<p class="description"><?php _e( 'Select which taxonomy to display.', 'acf' ); ?></p>
				</td>
				<td>
					<?php 
						$this->parent->create_field( array(
							'type'    => 'select',
							'name'    => "fields[{$key}][taxonomy]",
							'value'   => $field[ 'taxonomy' ],
							'choices' => $tax_choices,
						) );
					?>
				</td>
			</tr>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e( 'Columns' , 'acf' ); ?></label>
					<p class="description"><?php _e( 'Choose how many column to display.', 'acf' ); ?></p>
				</td>
				<td>
					<?php 
						$this->parent->create_field( array(
							'type'    => 'select',
							'name'    => "fields[{$key}][taxcol]",
							'value'   => $field[ 'taxcol' ],
							'choices' => $col_choices,
						) );
					?>
				</td>
			</tr>
			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e("Hide WP taxonomy?",'acf'); ?></label>
					<p class="description"><?php _e( 'You can hide the standard taxonomy metabox from the side panel.', 'acf' ); ?></p>
				</td>
				<td>
					<?php 
					$this->parent->create_field(array(
						'type'	=>	'radio',
						'name'	=>	'fields['.$key.'][hidetax]',
						'value'	=>	$field['hidetax'],
						'choices'	=>	array(
							'1'	=>	__("Yes",'acf'),
							'0'	=>	__("No",'acf'),
						)
					));
					?>
				</td>
			</tr>

		<?php
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*	- this function is called on edit screens to produce the html for this field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		global $post;
		// defaults
		$field[ 'taxonomy' ]  = ( array_key_exists( 'taxonomy', $field ) && isset( $field[ 'taxonomy' ] ) ) ? $field[ 'taxonomy' ] : 'category';
		$field[ 'hidetax' ]   = isset( $field[ 'hidetax' ] ) ? $field[ 'hidetax' ] : '0';
		$field[ 'taxcol' ]    = isset( $field[ 'taxcol' ] ) ? $field[ 'taxcol' ] : '2';
		$terms = get_terms( $field[ 'taxonomy' ], array( 'hide_empty' => false ) );
		
		// no choices
		if(empty($terms)) {
			echo '<p>' . _e("No choices to choose from",'acf') . '</p>';
			return false;
		}
	
		// building the html code

		echo '<input type="hidden" name="'.$field['name'].'" value="'.$field['name'].'" />';
		
		// checkbox saves an array - IMPORTANT
		$field['name'] .= '[]';

		$t = count($terms);
		$n = $field[ 'taxcol' ];		// number of columns
								
		if ($t > $n) {					// number of empty cells to add to balance the columns
			$m = $n - ( $t % $n );		// modulus
		} 		 
		else {
			$m = $t - $n;
		}
		$c = 1; 						// counter for column cells
		$w = 90 / $n . '%'; 			// width of cells
		
		$emptycell = '<td style="width:'. $w .';border-bottom:1px solid #ededed;"><!-- empty --></td>';

		// echo '<pre>';
		// print_r ($field);
		// echo $m;
		// echo '</pre>';
		?>
		
		<!-- Taxonomy Table -->
		<table class="widefat <?php echo $field['class'];?>" data-layout="<?php echo $field['name']; ?>" style="/*padding-bottom:25px;*/background:#fcfcfc;" >
			<tfoot>
				<tr>
					<th style="width:5%;"><!-- empty --></th>
					<th colspan="<?php echo $n; ?>" class="<?php echo $field['name']; ?>" style="width:45%;text-align:right;"><span>Taxonomy: <?php echo $field['taxonomy']; ?></span></th>
					<th style="width:5%;"><!-- empty --></th>
				</tr>
			</tfoot>
			<tbody>
			<?php
			// Loop 
			foreach( $terms as $term ) {
				
				$selected = '';

				if(isset($field['value']) && !empty($field['value'])) {
					if($field['taxonomy'] == $term->taxonomy && in_array($term->term_id,$field['value'])){
						$selected = 'checked="checked"';
					}
				}

				if ($c == 1 ) : ?>
					<tr>
						<td style="width:5%;border-bottom:1px solid #ededed;"><!-- empty --></td>
				<?php endif; ?>

				<td style="width:<?php echo $w; ?>;border-bottom:1px solid #ededed;">
					<label>
						<input type="checkbox" class="<?php echo $field['class']; ?>" name="<?php echo $field['name']; ?>" value="<?php echo $term->term_id; ?>" <?php echo $selected; ?>  style="margin-right:5px;" /><?php echo $term->name; ?>
					</label>
				</td>
				
				<?php 
				$c++;
				
				if ($c > $n) {			
					echo '
						<td style="width:5%;border-bottom:1px solid #ededed;"><!-- empty --></td>
					</tr>';
					$c = 1;
				}
				
			}
			// End Loop
			
			// Writing empty cells to fill the <tr>
			if ( $m >= 1 && $m < $n ) {			
				for ($l = 1; $l <= $m; $l ++) {
					echo $emptycell;
				}				
				echo '<td style="width:5%;border-bottom:1px solid #ededed;"><!-- empty --></td>'; //
			}
			
			?>
				</tr>
			</tbody>
		</table>
		<!-- / Taxonomy Table -->
		<?php 
		
		if ( $field[ 'hidetax' ] == 1 ) { ?>
			<style id="tax" type="text/css">#tagsdiv-<?php echo $field[ 'taxonomy' ]; ?>, #<?php echo $field[ 'taxonomy' ]; ?>div {display:none;}</style>
		<?php 		
		}



	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*	- this function is called in the admin_head of the edit screen where your field
	*	is created. Use this function to create css and javascript to assist your 
	*	create_field() function.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{

		
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	update_value
	*	- this function is called when saving a post object that your field is assigned to.
	*	the function will pass through the 3 parameters for you to use.
	*
	*	@params
	*	- $post_id (int) - usefull if you need to save extra data or manipulate the current
	*	post object
	*	- $field (array) - usefull if you need to manipulate the $value based on a field option
	*	- $value (mixed) - the new value of your field.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	*
	*-------------------------------------------------------------------------------------*/

	function update_value($post_id, $field, $value)
	{
		if(is_array($value)) {
			foreach($value as $term) {
				$terms[] = intval( $term );
			}

			$value = wp_set_object_terms( $post_id, $terms, $field[ 'taxonomy' ], false );
			parent::update_value( $post_id, $field, $value );
		}
		else {
			$value = wp_set_object_terms( $post_id, NULL, $field[ 'taxonomy' ], false );
			parent::update_value( $post_id, $field, $value );
		}

	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value
	*	- called from the edit page to get the value of your field. This function is useful
	*	if your field needs to collect extra data for your create_field() function.
	*
	*	@params
	*	- $post_id (int) - the post ID which your value is attached to
	*	- $field (array) - the field object.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/

	function get_value($post_id, $field)
	{
		// get values
		$terms = get_terms($field['taxonomy']);
		$value = array();

		foreach($terms as $term) {
			$val = intval( $term->term_id );
			$value[] = $val;
		}

		// return value
		return $value;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*	- called from your template file when using the API functions (get_field, etc). 
	*	This function is useful if your field needs to format the returned value
	*
	*	@params
	*	- $post_id (int) - the post ID which your value is attached to
	*	- $field (array) - the field object.
	*
	*	@author Brian Zoetewey - Taxonomy Field add-on
	*
	*-------------------------------------------------------------------------------------*/

	function get_value_for_api($post_id, $field)
	{
		$terms = get_terms($field['taxonomy']);

		// return value
		return $terms;
	}
	
}

?>