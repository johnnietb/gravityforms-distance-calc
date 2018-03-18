<?php

if (!class_exists('GFForms')) {
	exit();
}

class GF_Field_Distance_Calculator extends GF_Field {

	public $type = 'distance_calculator';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return __('Distance udregner');
	}

	/**
	 * Return the settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'default_value_setting',
			'css_class_setting'
		);
	}

	public function merge_tag_filter( $value, $merge_tag, $options, $field, $raw_field_value ) {

		if ( $merge_tag == 'all_fields' && $field->type == 'distance_calculator' ) {

			$show_in_all_fields = apply_filters( 'gform_signature_show_in_all_fields', true, $field, $options, $value );
			if ( ! $show_in_all_fields ) {
				return $raw_field_value;
			}

		}

		return $value;
	}

	/**
	 * Returns the field inner markup.
	 *
	 * @param array $form The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin        = $is_entry_detail || $is_form_editor;
		$is_disabled		 = '';

		$form_id  = absint( $form['id'] );
		$id       = $this->id;
		//$field_id = $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$field_id = 'input_' . $id;

		$title = $this->get_form_editor_field_title();
		$html = '';
		if ($is_admin) {
			$is_disabled = 'disabled';
			$html .= '<style>
									.distance-calculator, .distance_addresses { opacity: .4; }
									.dc_info {
										border: 1px solid #E4E4E4;
										padding: 20px;
										background-color: #F6F6F6;
									}
							  </style>

								<div class="dc_info"><b>Husk at indtaste rate (kroner pr. km.)</b><br><i>Avanceret -> Standardværdi</i></div>';
		}

		$add_icon    = ! empty( $this->addIconUrl ) ? $this->addIconUrl : GFCommon::get_base_url() . '/images/list-add.svg';
		$delete_icon = ! empty( $this->deleteIconUrl ) ? $this->deleteIconUrl : GFCommon::get_base_url() . '/images/list-remove.svg';

		$html .= '<table class="distance-calculator" id="distance_calculator_' . $field_id . '">
						 <tr class="dc_expense_row">
						 	 <td>
							   <input type="text" class="dc_expense" name="expense_' . $field_id . '[]" placeholder="Udgift"' . $is_disabled . '>
							 </td>
							 <td>
							   <input type="number" class="dc_amount" step="0.01" min="0.01" max="999999.99" step="any" name="amount_' . $field_id . '[]" placeholder="Beløb" maxlength="9"' . $is_disabled . '>
							 </td>
							 <td class="icons">
								 <img src="' . $add_icon . '" class="add_list_item" title="' . esc_attr__( "Add another row", "gravityforms" ) . '" alt="' . esc_attr__( "Add a new row", "gravityforms" ) . '" onclick="distCalcAddListItem(this)" onkeypress="distCalcAddListItem(this)" />
								 <img src="' . $delete_icon . '" class="delete_list_item" title="' . esc_attr__( "Remove this row", "gravityforms" ) . '" alt="' . esc_attr__( "Remove this row", "gravityforms" ) . '" onclick="distCalcDeleteListItem(this)" onkeypress="distCalcDeleteListItem(this)" />
 							 </td>
						</tr>
						<tr class="dc_total_row">
							<td>
								<button type="button" class="toggleDistanceCalculation" onclick="toggleDistanceCalculation(\'' . $field_id . '\')">Tilføj kørsel</button>
							</td>
							<td colspan="2" class="total">
								Total: <input type="text" readonly class="dc_total" name="total_' . $field_id . '" placeholder="0 kr" value="0" tabindex="-1"> kr.
							</td>
						</tr>
						</table>';

	  $html .= $this->map_form( $field_id, $this->defaultValue);

		return $html;

	}

	private function map_form( $field_id, $rate ) {
		return '<div class="distance_addresses" id="distance_addresses_' . $field_id . '">
								<input type="text" class="google_places_field" name="from_' . $field_id . '" id="from_' . $field_id . '" placeholder="Fra startsted" autocomplete="off" required>
								<input type="text" class="google_places_field" name="to_' . $field_id . '" id="to_' . $field_id . '" placeholder="Til destination" autocomplete="off" required>
								<input type="hidden" name="dc_km_rate_' . $field_id . '" id="dc_km_rate_' . $field_id . '" value="' . $rate .'"/>
								<button type="button" class="doDistanceCalculation" onclick="calculateDrivingDistance(\''.$field_id.'\');">Beregn og tilføj kørsel</a>
						  </div>';
	}

  /**
	 * Get all our values and store them withing one serialized array
	 *
	 * @inheritdoc
	 */
	public function get_value_save_entry( $value, $form, $input_name, $lead_id, $lead ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			if ( !isset($_POST['total_' . $input_name]) ) {
				return false;
			}

			$total = number_format($_POST['total_' . $input_name], 2);
			$expenses = $_POST['expense_' . $input_name];
			$amounts = $_POST['amount_' . $input_name];
			$fields = array();

			foreach( $expenses as $key => $expense ) {
					$fields[$expense] = isset($amounts[$key]) ? $amounts[$key] : 0;
			}

			$fields['Total'] = $total;

			return serialize( $fields );
		}
	}

	/**
	 * Display our values within the email, pdf and admin section
	 *
	 * @inheritdoc
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		if ( !isset($value) ) {
			return false;
		}

		$result = '';
		$line_break = '<br />';
		if ( $format != 'html' ) {
			$line_break = "\n";
		}

		$values = unserialize($value);
		foreach($values as $key => $value) {
			if ($key == 'Total') {
				$result .= sprintf('<b>%s: %s kr.</b> %s', $key, $value, $line_break);
				continue;
			}

			$result .= sprintf('%s: %s kr. %s', $key, $value, $line_break);
		}

		return $result;
	}


}

GF_Fields::register( new GF_Field_Distance_Calculator() );
