<?php
class Tribe__Events__Pro__MiniCalendarShortcode {
	/**
	 * The shortcode allows filtering by event categories and by post tags,
	 * in line with what the calendar widget itself supports.
	 *
	 * @var array
	 */
	protected $tax_relationships = array(
		'categories' => TribeEvents::TAXONOMY,
		'tags' => 'post_tag'
	);

	/**
	 * Default arguments expected by the calendar widget.
	 *
	 * @var array
	 */
	protected $default_args = array(
		'before_widget' => '',
		'before_title'  => '',
		'title'         => '',
		'after_title'   => '',
		'after_widget'  => '',

		'tag'  => '',
		'tags' => '',

		'category'   => '',
		'categories' => ''
	);

	protected $arguments = array();
	protected $filters = array();
	protected $terms = array();


	public function __construct() {
		add_shortcode( 'tribe_mini_calendar', array( $this, 'do_shortcode' ) );
	}

	public function do_shortcode( $attributes ) {
		$this->reset();
		$this->arguments = shortcode_atts( $this->default_args, $attributes );
		$this->taxonomy_filters();

		ob_start();
		the_widget( 'TribeEventsMiniCalendarWidget', $this->arguments, $this->arguments );
		return ob_get_clean();
	}

	protected function reset() {
		$this->arguments = array();
		$this->filters = array();
		$this->terms = array();
	}

	/**
	 * Sets up an array of taxonomy filters, if required by the shortcode
	 * arguments.
	 */
	protected function taxonomy_filters() {
		// Consolidate plural/singular forms into one
		$params  = array();
		$params['categories'] = $this->arguments['categories'] . ',' . $this->arguments['category'];
		$params['tags'] = $this->arguments['tags'] . ',' . $this->arguments['tag'];

		// Build our taxonomy filter
		foreach ( $this->tax_relationships as $param => $tax ) {
			// Check for taxonomy terms for each supported taxonomy
			$this->terms = explode( ',', $params[$param] );
			foreach ( $this->terms as $term ) {
				$this->add_term( $term, $tax );
			}
		}

		// Add the filters to the list of widget arguments
		if ( ! empty( $this->filters ) ) $this->arguments['raw_filters'] = $this->filters;
	}

	/**
	 * Potentially add a taxonomy term to our list of filters.
	 *
	 * @param $term
	 * @param $tax
	 */
	protected function add_term( $term, $tax ) {
		$term = trim( $term );
		if ( empty( $term ) ) return;

		// Accept term IDs...
		if ( is_numeric( $term ) && $term == absint( $term ) ) {
			$this->filters[$tax][] = $term;
		}
		// Also accept term slugs...
		else {
			$term_obj = get_term_by( 'slug', $term, $tax );
			if ( false === $term_obj ) return;
			$this->filters[$tax][] = $term_obj->term_id;
		}
	}
}