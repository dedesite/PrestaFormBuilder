<?php
/**
 * Those two classes are heavily based on Laravel's code :
 * https://github.com/laravel/framework/blob/4.2/src/Illuminate/Html/FormBuilder.php
 * https://github.com/laravel/framework/blob/4.2/src/Illuminate/Html/HtmlBuilder.php
 */


class Html {
	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public static function attributes($attributes)
	{
		$html = array();
		// For numeric keys we will assume that the key and the value are the same
		// as this will convert HTML attributes such as "required" to a correct
		// form like required="required" instead of using incorrect numerics.
		foreach ((array) $attributes as $key => $value)
		{
			$element = Html::attributeElement($key, $value);
			if ( ! is_null($element)) $html[] = $element;
		}
		return count($html) > 0 ? ' '.implode(' ', $html) : '';
	}
	/**
	 * Build a single attribute element.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @return string
	 */
	protected static function attributeElement($key, $value)
	{
		if (is_numeric($key)) $key = $value;
		if ( ! is_null($value)) return $key.'="'.$value.'"';
	}
}

class Form {
	private static $formgroup = '<div class="form-group">';
	private static $inputdiv = '<div class="col-lg-9">';
	private static $label_class = 'control-label col-lg-3';

	public static function open($options) {
		if(!isset($options['method'])) $options['method'] = 'POST';
		if($options['method'] == 'POST') {
			$options['enctype'] = 'multipart/form-data';
		}
		$options['class'] = isset($options['class']) ? $options['class'].' defaultForm form-horizontal' : 'defaultForm form-horizontal';
		return '<form'.Html::attributes($options).'>';
	}

	public static function close() {
		return '</form>';
	}

	/**
	 * Take an element and wrap it with admin decoration
	 */
	protected static function aelement($name, $label, $element) {
		//Add required class to the label if element is required
		$opt = array();
		if(strstr($element, 'required="required"')){
			$opt['class'] = 'required';
		}
		return '<div class="form-group">'.
					static::alabel($name, $label, $opt).
					static::$inputdiv.
						$element.
					'</div>
				</div>';
	}

	/**
	 * Create a form input field.
	 *
	 * @param  string  $type
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	public static function input($type, $name, $value = null, $options = array()) {
		if (!isset($options['name'])) $options['name'] = $name;
		if (!isset($options['id'])) $options['id'] = $name;
		// Once we have the type, value, and ID we can merge them into the rest of the
		// attributes array so we can convert them into their HTML attribute format
		// when creating the HTML element. Then, we will return the entire input.
		$merge = compact('type', 'value');

		$options = array_merge($options, $merge);

		return '<input '.Html::attributes($options).'>';
	}

	/**
	 * Create a form label element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	public static function label($name, $value = null, $options = array())
	{
		$options = Html::attributes($options);
		return '<label for="'.$name.'"'.$options.'>'.$value.'</label>';
	}

	/**
	 * label element in admin panel
	 */
	public static function alabel($name, $value, $options = array()) {
		$options['class'] = isset($options['class']) ? $options['class'].' '.static::$label_class : static::$label_class;
		return static::label($name, $value, $options);
	}

	/**
	 * Create a text input field.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	public static function text($name, $value = null, $options = array()) {
		return static::input('text', $name, $value, $options);
	}

	/**
	 * Create an admin panel text input field
	 */
	public static function atext($name, $label, $value = null, $options = array()) {
		return static::aelement($name, $label, static::text($name, $value, $options));
	}

	/**
	 * Create a hidden input field.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	public static function hidden($name, $value = null, $options = array()) {
		return static::input('hidden', $name, $value, $options);
	}

	public static function submit($name, $label, $options = array()) {
		$opt = array('name' => $name, 'class' => 'button', 'type' => 'submit');
		$options = array_merge($opt, $options);
		return '<button'.Html::attributes($options).'>'.View::$module->l($label).'</button>';
	}

	public static function imgsubmit($name, $label, $img_class, $options = array()) {
		$opt = array('name' => $name, 'class' => 'button', 'type' => 'submit');
		$options = array_merge($opt, $options);
		return '<button'.Html::attributes($options).'>
					<i class="'.$img_class.'"></i>'.$label.
			'</button>';
	}

	/**
	 * Prestashop category tree widget
	 */
	public static function category($name, $label, $desc, $selected_categories) {
		$tree_categories_helper = new HelperTreeCategories('categories-treeview');
		$tree_categories_helper->setRootCategory((Shop::getContext() == Shop::CONTEXT_SHOP ? Category::getRootCategory()->id_category : 0))
				->setUseCheckBox(true)
				->setUseSearch(false)
				->setInputName($name)
				->setSelectedCategories($selected_categories);
		$el = $tree_categories_helper->render().'<p class="help-block">'.$desc.'</p>';
		return static::aelement($name, $label, $el);
	}

	/**
	 * Radio switch button
	 */
	public static function rswitch($name, $label, $value) {
		$el = '<span class="switch prestashop-switch fixed-width-lg">'.
				static::checkable('radio', $name, '1', $value == '1', array('id' => $name.'_on')).
				static::label($name.'_on', 'Oui').
				static::checkable('radio', $name, '0', $value == '0', array('id' => $name.'_off')).
				static::label($name.'_off', 'Non').
				'<a class="slide-button btn"></a>
			</span>';
		return static::aelement($name, $label, $el);
	}

	protected static function checkable($type, $name, $value, $checked, $options) {
		if ($checked) $options['checked'] = 'checked';

		return static::input($type, $name, $value, $options);
	}

	public static function checkbox($name, $value = 1, $checked = null, $options = array()) {
		return static::checkable('checkbox', $name, $value, $checked, $options);
	}

	/**
	 * checkbox for admin panel
	 */
	public static function acheckbox($name, $label, $value = 1, $checked = null, $options = array()) {
		return static::aelement($name, $label, static::checkbox($name, $value, $checked, $options));
	}



	/**
	 * Determine if the value is selected.
	 *
	 * @param  string  $value
	 * @param  string  $selected
	 * @return string
	 */
	protected static function getSelectedValue($value, $selected)
	{
		if (is_array($selected))
		{
			return in_array($value, $selected) ? 'selected' : null;
		}
		return ((string) $value == (string) $selected) ? 'selected' : null;
	}

	/**
	 * Create a select element option.
	 *
	 * @param  string  $display
	 * @param  string  $value
	 * @param  string  $selected
	 * @return string
	 */
	protected static function option($display, $value, $selected)
	{
		$selected = static::getSelectedValue($value, $selected);
		$options = array('value' => $value, 'selected' => $selected);
		return '<option'.Html::attributes($options).'>'.$display.'</option>';
	}

	/**
	 * Create a select box field.
	 *
	 * @param  string  $name
	 * @param  array   $list
	 * @param  string  $selected
	 * @param  array   $options
	 * @return string
	 */
	public static function select($name, $list = array(), $selected = null, $options = array()) {
		if (!isset($options['name'])) $options['name'] = $name;
		if (!isset($options['id'])) $options['id'] = $name;	

		// We will simply loop through the options and build an HTML value for each of
		// them until we have an array of HTML declarations. Then we will join them
		// all together into one single HTML element that can be put on the form.
		$html = array();
		foreach ($list as $value => $display)
		{
			$html[] = static::option($display, $value, $selected);
		}
		$list = implode('', $html);

		// Once we have all of this HTML, we can join this into a single element after
		// formatting the attributes into an HTML "attributes" string, then we will
		// build out a final select statement, which will contain all the values.
		$options = Html::attributes($options);

		return '<select'.$options.'>'.$list.'</select>';
	}

	/**
	 * Create a select for admin panel
	 */
	public static function aselect($name, $label, $list = array(), $selected = null, $options = array()) {
		$options['class'] = isset($options['class']) ? $options['class'] . ' fixed-width-xl' : 'fixed-width-xl';
		return static::aelement($name, $label, static::select($name, $list, $selected, $options));
	}
}