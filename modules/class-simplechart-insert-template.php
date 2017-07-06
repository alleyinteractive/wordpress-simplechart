<?php

/**
 * Backbone templates for various views for your new service
 */
class Simplechart_Insert_Template {

	/**
	 * Outputs the Backbone template for an item within search results.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function item() {
		?>
		<div id="simplechart-item-{{ data.id }}" class="simplechart-item-area simplechart-item" data-id="{{ data.id }}">
			<div class="simplechart-item-container clearfix">
				<div class="simplechart-item-main">
					<div class="simplechart-item-content">
						<h3>{{ data.content }}</h3>
					</div>

					<p class="simplechart-item-meta">
						<?php esc_html_e( 'Status:', 'simplechart' ); ?> {{data.status}}
					</p>
					<p class="simplechart-item-meta">
						{{ data.date }}
					</p>
				</div>

				<a href="#" id="simplechart-check-{{ data.id }}" class="check" title="<?php esc_attr_e( 'Deselect', 'simplechart' ); ?>">
					<div class="media-modal-icon"></div>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Outputs the Backbone template for a select item's thumbnail in the footer toolbar.
	 *
	 * @param string $id The template ID.
	 */
	public function thumbnail( $id ) {
		?>
		<div class="simplechart-item-thumb">
			<h4>{{ data.content }}</h4>
		</div>
		<?php
	}

	/**
	 * Outputs the Backbone template for a tab's search fields.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function search( $id, $tab ) {
		?>
		<form action="#" class="simplechart-toolbar-container clearfix tab-all">
			<input
			type="text"
			name="q"
			value="{{ data.params.q }}"
			class="simplechart-input-text simplechart-input-search"
			size="40"
			placeholder="<?php esc_attr_e( 'Search for anything!', 'simplechart' ); ?>"
			>
			<input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'simplechart' ); ?>">

			<div class="spinner"></div>
		</form>
	</script>
	<?php
	}

	/**
	 * Outputs the markup needed before a template.
	 *
	 * @param string $id  The template ID.
	 * @return null
	 */
	final public function before_template( $id ) {
		?>
		<script type="text/html" id="tmpl-<?php echo esc_attr( $id ); ?>">
			<?php
	}
}
